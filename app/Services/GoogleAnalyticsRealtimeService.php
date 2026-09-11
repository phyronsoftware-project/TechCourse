<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GoogleAnalyticsRealtimeService
{
    protected const TOKEN_CACHE_KEY = 'ga4_service_account_access_token';
    protected const ACTIVE_USERS_CACHE_KEY = 'ga4_realtime_active_users';
    protected const UNAVAILABLE_CACHE_KEY = 'ga4_temporarily_unavailable';
    protected const REQUEST_TIMEOUT_SECONDS = 3;
    protected const CONNECT_TIMEOUT_SECONDS = 2;

    // Fetch the GA4 realtime active user count with lightweight caching.
    public function activeUsers(): ?int
    {
        // Skip GA4 while the remote service is temporarily unavailable.
        if (! $this->isConfigured() || $this->isTemporarilyUnavailable()) {
            return null;
        }

        return Cache::remember(self::ACTIVE_USERS_CACHE_KEY, now()->addMinutes(1), function () {
            $accessToken = $this->fetchAccessToken();

            if (! $accessToken) {
                return null;
            }

            $propertyId = $this->propertyId();

            try {
                $response = Http::withToken($accessToken)
                    ->acceptJson()
                    ->connectTimeout(self::CONNECT_TIMEOUT_SECONDS)
                    ->timeout(self::REQUEST_TIMEOUT_SECONDS)
                    ->post("https://analyticsdata.googleapis.com/v1beta/properties/{$propertyId}:runRealtimeReport", [
                        'metrics' => [
                            ['name' => 'activeUsers'],
                        ],
                    ]);

                if (! $response->successful()) {
                    $this->markTemporarilyUnavailable();

                    Log::warning('GA4 realtime report request failed.', [
                        'status' => $response->status(),
                        'body' => $response->json(),
                    ]);

                    return null;
                }

                $rows = $response->json('rows', []);
                $value = data_get($rows, '0.metricValues.0.value');

                return is_numeric($value) ? (int) $value : 0;
            } catch (Throwable $exception) {
                $this->markTemporarilyUnavailable();

                Log::warning('GA4 realtime report request threw an exception.', [
                    'message' => $exception->getMessage(),
                ]);

                return null;
            }
        });
    }

    // Read total GA4 page views for one website path.
    public function pageViewsByPath(string $path, int $days = 30): ?int
    {
        return $this->runReportMetricByPath(
            metricName: 'screenPageViews',
            path: $this->normalizePath($path),
            days: $days,
        );
    }

    // Read GA4 custom event totals for one website path.
    public function eventCountByPath(string $eventName, string $path, int $days = 30): ?int
    {
        return $this->runReportMetricByPath(
            metricName: 'eventCount',
            path: $this->normalizePath($path),
            days: $days,
            eventName: $eventName,
        );
    }

    public function isConfigured(): bool
    {
        return filled($this->propertyId()) && filled($this->serviceAccountPath());
    }

    protected function runReportMetricByPath(string $metricName, string $path, int $days = 30, ?string $eventName = null): ?int
    {
        // Keep lesson pages responsive when GA4 is timing out or rate limited.
        if (! $this->isConfigured() || $this->isTemporarilyUnavailable()) {
            return null;
        }

        $cacheKey = 'ga4_report_' . md5($metricName . '|' . $path . '|' . $days . '|' . ($eventName ?? ''));

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($metricName, $path, $days, $eventName) {
            $accessToken = $this->fetchAccessToken();

            if (! $accessToken) {
                return null;
            }

            $propertyId = $this->propertyId();
            $filterExpressions = [
                [
                    'filter' => [
                        'fieldName' => 'pagePath',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => $path,
                        ],
                    ],
                ],
            ];

            if ($eventName !== null) {
                $filterExpressions[] = [
                    'filter' => [
                        'fieldName' => 'eventName',
                        'stringFilter' => [
                            'matchType' => 'EXACT',
                            'value' => $eventName,
                        ],
                    ],
                ];
            }

            try {
                $response = Http::withToken($accessToken)
                    ->acceptJson()
                    ->connectTimeout(self::CONNECT_TIMEOUT_SECONDS)
                    ->timeout(self::REQUEST_TIMEOUT_SECONDS)
                    ->post("https://analyticsdata.googleapis.com/v1beta/properties/{$propertyId}:runReport", [
                        'dateRanges' => [
                            [
                                'startDate' => $days . 'daysAgo',
                                'endDate' => 'today',
                            ],
                        ],
                        'dimensions' => [
                            ['name' => 'pagePath'],
                        ],
                        'metrics' => [
                            ['name' => $metricName],
                        ],
                        'dimensionFilter' => [
                            'andGroup' => [
                                'expressions' => $filterExpressions,
                            ],
                        ],
                    ]);

                if (! $response->successful()) {
                    $this->markTemporarilyUnavailable();

                    Log::warning('GA4 standard report request failed.', [
                        'status' => $response->status(),
                        'body' => $response->json(),
                        'metric' => $metricName,
                        'path' => $path,
                        'event_name' => $eventName,
                    ]);

                    return null;
                }

                $rows = $response->json('rows', []);
                $value = data_get($rows, '0.metricValues.0.value');

                return is_numeric($value) ? (int) $value : 0;
            } catch (Throwable $exception) {
                $this->markTemporarilyUnavailable();

                Log::warning('GA4 standard report request threw an exception.', [
                    'message' => $exception->getMessage(),
                    'metric' => $metricName,
                    'path' => $path,
                    'event_name' => $eventName,
                ]);

                return null;
            }
        });
    }

    protected function fetchAccessToken(): ?string
    {
        return Cache::remember(self::TOKEN_CACHE_KEY, now()->addMinutes(50), function () {
            $credentials = $this->credentials();

            if (! is_array($credentials)) {
                return null;
            }

            $jwt = $this->buildJwtAssertion($credentials);

            if (! $jwt) {
                return null;
            }

            try {
                $response = Http::asForm()
                    ->acceptJson()
                    ->connectTimeout(self::CONNECT_TIMEOUT_SECONDS)
                    ->timeout(self::REQUEST_TIMEOUT_SECONDS)
                    ->post((string) ($credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token'), [
                        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                        'assertion' => $jwt,
                    ]);

                if (! $response->successful()) {
                    $this->markTemporarilyUnavailable();

                    Log::warning('GA4 OAuth token request failed.', [
                        'status' => $response->status(),
                        'body' => $response->json(),
                    ]);

                    return null;
                }

                return $response->json('access_token');
            } catch (Throwable $exception) {
                $this->markTemporarilyUnavailable();

                Log::warning('GA4 OAuth token request threw an exception.', [
                    'message' => $exception->getMessage(),
                ]);

                return null;
            }
        });
    }

    // Pause repeated GA4 requests briefly after a remote failure.
    protected function markTemporarilyUnavailable(): void
    {
        Cache::put(self::UNAVAILABLE_CACHE_KEY, true, now()->addMinutes(5));
    }

    // Check whether a recent GA4 request already failed.
    protected function isTemporarilyUnavailable(): bool
    {
        return Cache::has(self::UNAVAILABLE_CACHE_KEY);
    }

    protected function buildJwtAssertion(array $credentials): ?string
    {
        $privateKey = (string) ($credentials['private_key'] ?? '');
        $clientEmail = (string) ($credentials['client_email'] ?? '');
        $tokenUri = (string) ($credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token');

        if ($privateKey === '' || $clientEmail === '') {
            return null;
        }

        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], JSON_UNESCAPED_SLASHES));

        $issuedAt = time();
        $payload = $this->base64UrlEncode(json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/analytics.readonly',
            'aud' => $tokenUri,
            'iat' => $issuedAt,
            'exp' => $issuedAt + 3600,
        ], JSON_UNESCAPED_SLASHES));

        $signingInput = $header . '.' . $payload;
        $signature = '';

        if (! openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            return null;
        }

        return $signingInput . '.' . $this->base64UrlEncode($signature);
    }

    protected function credentials(): ?array
    {
        $path = $this->serviceAccountPath();

        if (! $path || ! is_file($path)) {
            return null;
        }

        $contents = @file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        $decoded = json_decode($contents, true);

        return is_array($decoded) ? $decoded : null;
    }

    protected function serviceAccountPath(): ?string
    {
        $configuredPath = (string) (config('services.ga4.service_account_json_path') ?: env('GA4_SERVICE_ACCOUNT_JSON_PATH', ''));

        if ($configuredPath !== '') {
            return str_starts_with($configuredPath, '/')
                ? $configuredPath
                : base_path($configuredPath);
        }

        $defaultPath = storage_path('app/google/ga4-service-account.json');

        return is_file($defaultPath) ? $defaultPath : null;
    }

    protected function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    protected function propertyId(): string
    {
        return (string) (config('services.ga4.property_id') ?: env('GA4_PROPERTY_ID', ''));
    }

    protected function normalizePath(string $path): string
    {
        $trimmedPath = trim($path);

        if ($trimmedPath === '') {
            return '/';
        }

        $parsedPath = parse_url($trimmedPath, PHP_URL_PATH);
        $normalizedPath = is_string($parsedPath) && $parsedPath !== '' ? $parsedPath : $trimmedPath;

        return str_starts_with($normalizedPath, '/') ? $normalizedPath : '/' . $normalizedPath;
    }
}
