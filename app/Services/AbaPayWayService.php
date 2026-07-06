<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AbaPayWayService
{
    // Keep one place for the success codes returned by ABA PayWay APIs.
    protected array $successCodes = ['0', '00'];

    public function summary(): array
    {
        $merchantId = (string) config('services.aba_payway.merchant_id');
        $apiKey = (string) config('services.aba_payway.api_key');
        $publicKey = (string) config('services.aba_payway.rsa_public_key');
        $privateKey = (string) config('services.aba_payway.rsa_private_key');

        return [
            'merchant_id' => $merchantId,
            'merchant_id_masked' => $this->mask($merchantId, 2, 2),
            'api_key' => $apiKey,
            'api_key_masked' => $this->mask($apiKey, 4, 4),
            'currency' => (string) config('services.aba_payway.currency', 'USD'),
            'payment_option' => (string) config('services.aba_payway.payment_option', 'abapay_deeplink'),
            'purchase_url' => (string) config('services.aba_payway.purchase_url'),
            'generate_qr_url' => (string) config('services.aba_payway.generate_qr_url'),
            'check_transaction_url' => (string) config('services.aba_payway.check_transaction_url'),
            'return_url' => (string) config('services.aba_payway.return_url'),
            'cancel_url' => (string) config('services.aba_payway.cancel_url'),
            'callback_url' => (string) config('services.aba_payway.callback_url'),
            'has_public_key' => filled($publicKey),
            'has_private_key' => filled($privateKey),
            'is_ready' => filled($merchantId) && filled($apiKey) && filled($publicKey) && filled($privateKey),
        ];
    }

    public function generateKhqr(array $payload): array
    {
        $summary = $this->summary();

        if (! $summary['is_ready'] || (blank($summary['generate_qr_url']) && blank($summary['purchase_url']))) {
            throw new RuntimeException('ABA PayWay sandbox config is not ready.');
        }

        $reqTime = now()->format('YmdHis');
        $items = base64_encode(json_encode([[
            'name' => (string) ($payload['item_name'] ?? 'TechCourse Payment'),
            'quantity' => 1,
            'price' => (float) ($payload['amount'] ?? 0),
        ]], JSON_UNESCAPED_SLASHES));

        // ABA PayWay limits some identity fields, so trim them before sending the QR request.
        $firstName = $this->limitField((string) ($payload['first_name'] ?? 'ABA'), 20);
        $lastName = $this->limitField((string) ($payload['last_name'] ?? 'Bank'), 20);
        $email = $this->limitField((string) ($payload['email'] ?? ''), 50);
        $phone = $this->limitField((string) ($payload['phone'] ?? ''), 20);

        $callbackUrl = (string) ($payload['callback_url'] ?? $summary['callback_url'] ?? '');
        $callbackUrlBase64 = $callbackUrl !== '' ? base64_encode($callbackUrl) : null;

        $request = [
            'req_time' => $reqTime,
            'merchant_id' => (string) $summary['merchant_id'],
            'tran_id' => (string) $payload['tran_id'],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'amount' => (float) ($payload['amount'] ?? 0),
            'purchase_type' => 'purchase',
            'payment_option' => 'abapay_khqr',
            'items' => $items,
            'currency' => (string) ($payload['currency'] ?? $summary['currency'] ?? 'USD'),
            'callback_url' => $callbackUrlBase64,
            'lifetime' => (int) ($payload['lifetime'] ?? 6),
            'qr_image_template' => (string) ($payload['qr_image_template'] ?? 'template3_color'),
        ];

        // Keep the ABA QR request payload aligned with the working SDK by omitting null fields entirely.
        $request = array_filter($request, static fn ($value) => $value !== null);

        $request['hash'] = $this->generateQrHash(
            $request,
            (string) $summary['api_key']
        );

        $response = Http::acceptJson()
            ->asJson()
            ->acceptJson()
            ->post((string) ($summary['generate_qr_url'] ?: $summary['purchase_url']), $request);

        if (! $response->successful()) {
            $errorMessage = (string) data_get($response->json(), 'status.message', '');
            $errorDetails = data_get($response->json(), 'status.errors');

            if (is_array($errorDetails) && $errorDetails !== []) {
                $flattenedErrors = [];

                foreach ($errorDetails as $field => $messages) {
                    $text = is_array($messages) ? implode(', ', $messages) : (string) $messages;
                    $flattenedErrors[] = $field . ': ' . $text;
                }

                $errorMessage = trim($errorMessage . ' ' . implode(' | ', $flattenedErrors));
            }

            throw new RuntimeException(
                'ABA PayWay QR request failed with HTTP ' . $response->status() . ($errorMessage !== '' ? ' - ' . $errorMessage : '.')
            );
        }

        $data = $response->json();
        $statusCode = (string) data_get($data, 'status.code', '');

        if (! in_array($statusCode, $this->successCodes, true)) {
            throw new RuntimeException((string) data_get($data, 'status.message', 'Unable to generate ABA KHQR.'));
        }

        return $data;
    }

    public function checkTransaction(string $tranId): array
    {
        $summary = $this->summary();

        if (! $summary['is_ready'] || blank($summary['check_transaction_url'])) {
            throw new RuntimeException('ABA PayWay check transaction config is not ready.');
        }

        $request = [
            'req_time' => now()->format('YmdHis'),
            'merchant_id' => (string) $summary['merchant_id'],
            'tran_id' => $tranId,
        ];

        $request['hash'] = $this->generateCheckTransactionHash($request, (string) $summary['api_key']);

        $response = Http::acceptJson()
            ->asJson()
            ->post((string) $summary['check_transaction_url'], $request);

        if (! $response->successful()) {
            throw new RuntimeException('ABA PayWay check transaction failed with HTTP ' . $response->status() . '.');
        }

        return $response->json();
    }

    // Verify the callback signature sent by ABA PayWay before trusting the payload.
    public function verifyCallbackSignature(array $payload, ?string $signature): bool
    {
        if (blank($signature)) {
            return false;
        }

        ksort($payload);

        $beforeHash = '';

        foreach ($payload as $value) {
            $beforeHash .= is_array($value)
                ? json_encode($value, JSON_UNESCAPED_SLASHES)
                : (string) $value;
        }

        $expected = base64_encode(
            hash_hmac('sha512', $beforeHash, (string) config('services.aba_payway.api_key'), true)
        );

        return hash_equals($expected, (string) $signature);
    }

    public function isSuccessStatus(?string $statusCode): bool
    {
        return in_array((string) $statusCode, $this->successCodes, true);
    }

    // ABA QR API hash follows the documented field sequence for generate-qr requests.
    protected function generateQrHash(array $request, string $hashKey): string
    {
        $values = [
            (string) ($request['req_time'] ?? ''),
            (string) ($request['merchant_id'] ?? ''),
            (string) ($request['tran_id'] ?? ''),
            (string) ($request['amount'] ?? ''),
        ];

        if (array_key_exists('items', $request)) {
            $values[] = (string) $request['items'];
        }

        $values[] = (string) ($request['first_name'] ?? '');
        $values[] = (string) ($request['last_name'] ?? '');
        $values[] = (string) ($request['email'] ?? '');
        $values[] = (string) ($request['phone'] ?? '');
        $values[] = (string) ($request['purchase_type'] ?? '');
        $values[] = (string) ($request['payment_option'] ?? '');

        if (array_key_exists('callback_url', $request)) {
            $values[] = (string) $request['callback_url'];
        }

        if (array_key_exists('return_deeplink', $request)) {
            $values[] = (string) $request['return_deeplink'];
        }

        $values[] = (string) ($request['currency'] ?? '');

        if (array_key_exists('custom_fields', $request)) {
            $values[] = (string) $request['custom_fields'];
        }

        if (array_key_exists('return_params', $request)) {
            $values[] = (string) $request['return_params'];
        }

        if (array_key_exists('payout', $request)) {
            $values[] = (string) $request['payout'];
        }

        $values[] = (string) ($request['lifetime'] ?? '');
        $values[] = (string) ($request['qr_image_template'] ?? '');

        $string = implode('', $values);

        return base64_encode(hash_hmac('sha512', $string, $hashKey, true));
    }

    // ABA check-transaction hash uses the compact field order from the official API example.
    protected function generateCheckTransactionHash(array $request, string $hashKey): string
    {
        $string = implode('', [
            (string) ($request['req_time'] ?? ''),
            (string) ($request['merchant_id'] ?? ''),
            (string) ($request['tran_id'] ?? ''),
        ]);

        return base64_encode(hash_hmac('sha512', $string, $hashKey, true));
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

    protected function limitField(string $value, int $limit): string
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            return '';
        }

        return mb_substr($trimmed, 0, $limit);
    }
}
