<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AbaPaywayService
{
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

        if (! $summary['is_ready'] || blank($summary['generate_qr_url'])) {
            throw new RuntimeException('ABA PayWay sandbox config is not ready.');
        }

        $reqTime = now()->format('YmdHis');
        $callbackUrl = base64_encode((string) $summary['callback_url']);
        $items = base64_encode(json_encode([[
            'name' => (string) ($payload['item_name'] ?? 'TechCourse Payment'),
            'quantity' => 1,
            'price' => (float) ($payload['amount'] ?? 0),
        ]], JSON_UNESCAPED_SLASHES));

        $request = [
            'req_time' => $reqTime,
            'merchant_id' => (string) $summary['merchant_id'],
            'tran_id' => (string) $payload['tran_id'],
            'first_name' => (string) ($payload['first_name'] ?? 'ABA'),
            'last_name' => (string) ($payload['last_name'] ?? 'Bank'),
            'email' => (string) ($payload['email'] ?? ''),
            'phone' => (string) ($payload['phone'] ?? ''),
            'amount' => (float) ($payload['amount'] ?? 0),
            'purchase_type' => 'purchase',
            'payment_option' => 'abapay_khqr',
            'items' => $items,
            'callback_url' => $callbackUrl,
            'currency' => (string) ($payload['currency'] ?? $summary['currency'] ?? 'USD'),
            'return_deeplink' => null,
            'custom_fields' => null,
            'return_params' => null,
            'payout' => null,
            'lifetime' => (int) ($payload['lifetime'] ?? 6),
            'qr_image_template' => (string) ($payload['qr_image_template'] ?? 'template3_color'),
        ];

        $request['hash'] = $this->generateQrHash($request, (string) $summary['api_key']);

        $response = Http::acceptJson()
            ->contentType('application/json')
            ->post((string) $summary['generate_qr_url'], $request);

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

        if ($statusCode !== '0' && $statusCode !== '00') {
            throw new RuntimeException((string) data_get($data, 'status.message', 'Unable to generate ABA KHQR.'));
        }

        return $data;
    }

    protected function generateQrHash(array $request, string $apiKey): string
    {
        $string = implode('', [
            (string) ($request['req_time'] ?? ''),
            (string) ($request['merchant_id'] ?? ''),
            (string) ($request['tran_id'] ?? ''),
            number_format((float) ($request['amount'] ?? 0), 2, '.', ''),
            (string) ($request['items'] ?? ''),
            (string) ($request['first_name'] ?? ''),
            (string) ($request['last_name'] ?? ''),
            (string) ($request['email'] ?? ''),
            (string) ($request['phone'] ?? ''),
            (string) ($request['purchase_type'] ?? ''),
            (string) ($request['payment_option'] ?? ''),
            (string) ($request['callback_url'] ?? ''),
            (string) ($request['currency'] ?? ''),
            (string) ($request['return_deeplink'] ?? ''),
            (string) ($request['custom_fields'] ?? ''),
            (string) ($request['return_params'] ?? ''),
            (string) ($request['payout'] ?? ''),
            (string) ($request['lifetime'] ?? ''),
            (string) ($request['qr_image_template'] ?? ''),
        ]);

        return base64_encode(hash_hmac('sha512', $string, $apiKey, true));
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
