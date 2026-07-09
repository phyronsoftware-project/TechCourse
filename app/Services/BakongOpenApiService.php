<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class BakongOpenApiService
{
    // Expose safe config details so checkout can know whether Bakong verification is ready.
    public function summary(): array
    {
        $token = (string) config('bakong.open_api_token');

        return [
            'base_url' => rtrim((string) config('bakong.open_api_base_url', 'https://api-bakong.nbc.gov.kh'), '/'),
            'has_token' => filled($token),
            'token_masked' => $this->mask($token),
        ];
    }

    // Verify a transaction reference with the official Bakong Open API.
    public function checkTransaction(string $referenceType, string $referenceValue, array $options = []): array
    {
        $summary = $this->summary();

        if (! $summary['has_token']) {
            throw new RuntimeException('Bakong Open API token is not configured yet.');
        }

        $referenceType = strtolower(trim($referenceType));
        $referenceValue = trim($referenceValue);

        $endpointMap = [
            'hash' => 'check_transaction_by_hash',
            'short_hash' => 'check_transaction_by_short_hash',
            'md5' => 'check_transaction_by_md5',
            'instruction_ref' => 'check_transaction_by_instruction_ref',
            'external_ref' => 'check_transaction_by_external_ref',
        ];

        if (! isset($endpointMap[$referenceType])) {
            throw new RuntimeException('Unsupported Bakong reference type.');
        }

        $payload = match ($referenceType) {
            'hash' => ['hash' => $referenceValue],
            'short_hash' => [
                'hash' => $referenceValue,
                'amount' => (float) ($options['amount'] ?? 0),
                'currency' => (string) ($options['currency'] ?? 'USD'),
            ],
            'md5' => ['md5' => $referenceValue],
            'instruction_ref' => ['instructionRef' => $referenceValue],
            'external_ref' => ['externalRef' => $referenceValue],
        };

        if ($referenceType === 'short_hash' && (float) ($payload['amount'] ?? 0) <= 0) {
            throw new RuntimeException('Bakong short hash verification requires a valid amount.');
        }

        $response = Http::acceptJson()
            ->timeout(15)
            ->withToken((string) config('bakong.open_api_token'))
            ->post($summary['base_url'] . '/local/v1/' . $endpointMap[$referenceType], $payload);

        if (! $response->successful()) {
            $message = (string) data_get($response->json(), 'responseMessage', 'Unable to verify transaction with Bakong Open API.');
            throw new RuntimeException('Bakong Open API request failed with HTTP ' . $response->status() . ' - ' . $message);
        }

        $data = $response->json();

        if ((int) data_get($data, 'responseCode', 1) !== 0) {
            throw new RuntimeException((string) data_get($data, 'responseMessage', 'Bakong verification failed.'));
        }

        return is_array($data) ? $data : [];
    }

    // Normalize Bakong tracking states into local payment statuses.
    public function normalizeTrackingStatus(array $response): string
    {
        $trackingStatus = strtoupper((string) data_get($response, 'data.trackingStatus', ''));

        if (in_array($trackingStatus, ['ACKNOWLEDGED_BY_FI', 'SUCCESS'], true)) {
            return 'success';
        }

        if (in_array($trackingStatus, ['RECEIVE_AT_ACH', 'RECEIVE_AT_RECEIVER_BANK'], true)) {
            return 'pending';
        }

        if ($trackingStatus === 'REFUNDED_BY_RECEIVER') {
            return 'refunded';
        }

        if ($trackingStatus === 'FAILED_AT_ACH') {
            return 'failed';
        }

        return $trackingStatus === '' ? 'success' : 'unknown';
    }

    protected function mask(string $value): string
    {
        if ($value === '') {
            return '-';
        }

        if (strlen($value) <= 8) {
            return str_repeat('*', strlen($value));
        }

        return substr($value, 0, 4) . str_repeat('*', max(strlen($value) - 8, 4)) . substr($value, -4);
    }
}
