<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleCloudTextToSpeechService
{
    public function summary(): array
    {
        $credentials = $this->credentials();

        return [
            'is_ready' => $credentials !== null,
            'client_email_masked' => $credentials ? $this->mask((string) ($credentials['client_email'] ?? ''), 4, 18) : '-',
            'project_id' => $credentials['project_id'] ?? '-',
            'token_uri' => $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token',
        ];
    }

    public function listVoices(?string $languageCode = null): array
    {
        $response = Http::timeout(20)
            ->withToken($this->accessToken())
            ->acceptJson()
            ->get('https://texttospeech.googleapis.com/v1/voices', array_filter([
                'languageCode' => $languageCode ?: null,
            ]));

        if (! $response->successful()) {
            throw new RuntimeException('Unable to load Google Cloud voices right now.');
        }

        return collect($response->json('voices', []))
            ->map(function (array $voice) {
                return [
                    'name' => (string) ($voice['name'] ?? ''),
                    'languageCodes' => array_values($voice['languageCodes'] ?? []),
                    'ssmlGender' => (string) ($voice['ssmlGender'] ?? 'SSML_VOICE_GENDER_UNSPECIFIED'),
                    'naturalSampleRateHertz' => (int) ($voice['naturalSampleRateHertz'] ?? 0),
                ];
            })
            ->filter(fn (array $voice) => $voice['name'] !== '')
            ->values()
            ->all();
    }

    public function synthesize(string $text, string $languageCode, ?string $voiceName = null): string
    {
        $payload = [
            'input' => [
                'text' => $text,
            ],
            'voice' => array_filter([
                'languageCode' => $languageCode,
                'name' => $voiceName ?: null,
            ]),
            'audioConfig' => [
                'audioEncoding' => 'MP3',
            ],
        ];

        $response = Http::timeout(30)
            ->withToken($this->accessToken())
            ->acceptJson()
            ->post('https://texttospeech.googleapis.com/v1/text:synthesize', $payload);

        if (! $response->successful()) {
            $message = (string) data_get($response->json(), 'error.message', 'Unable to generate Google Cloud audio.');
            throw new RuntimeException($message);
        }

        $audioContent = (string) $response->json('audioContent', '');

        if ($audioContent === '') {
            throw new RuntimeException('Google Cloud returned an empty audio response.');
        }

        return base64_decode($audioContent, true) ?: throw new RuntimeException('Unable to decode Google Cloud audio response.');
    }

    protected function accessToken(): string
    {
        $credentials = $this->credentials();

        if ($credentials === null) {
            throw new RuntimeException('Google Cloud TTS credentials are not configured.');
        }

        $now = time();
        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], JSON_UNESCAPED_SLASHES));

        $claims = $this->base64UrlEncode(json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/cloud-platform',
            'aud' => $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now,
        ], JSON_UNESCAPED_SLASHES));

        $unsignedToken = $header . '.' . $claims;
        $privateKey = openssl_pkey_get_private($credentials['private_key']);

        if (! $privateKey) {
            throw new RuntimeException('Invalid Google Cloud service account private key.');
        }

        $signature = '';
        openssl_sign($unsignedToken, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        $jwt = $unsignedToken . '.' . $this->base64UrlEncode($signature);

        $response = Http::asForm()
            ->timeout(20)
            ->post($credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Unable to authenticate with Google Cloud TTS.');
        }

        $accessToken = (string) $response->json('access_token', '');

        if ($accessToken === '') {
            throw new RuntimeException('Google Cloud TTS access token is missing.');
        }

        return $accessToken;
    }

    protected function credentials(): ?array
    {
        $rawJson = trim((string) config('services.google_cloud_tts.service_account_json'));
        $jsonPath = trim((string) config('services.google_cloud_tts.service_account_json_path'));

        if ($rawJson !== '') {
            $decoded = json_decode($rawJson, true);

            return is_array($decoded) ? $decoded : null;
        }

        if ($jsonPath !== '' && is_file($jsonPath)) {
            $decoded = json_decode((string) file_get_contents($jsonPath), true);

            return is_array($decoded) ? $decoded : null;
        }

        return null;
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function mask(string $value, int $prefix = 3, int $suffix = 3): string
    {
        if ($value === '') {
            return '-';
        }

        if (strlen($value) <= ($prefix + $suffix)) {
            return str_repeat('*', strlen($value));
        }

        return substr($value, 0, $prefix) . str_repeat('*', max(strlen($value) - ($prefix + $suffix), 4)) . substr($value, -$suffix);
    }
}
