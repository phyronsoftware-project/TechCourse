<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AbaPayWayService
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

        if (! $summary['is_ready'] || blank($summary['purchase_url'])) {
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

        $returnUrl = (string) ($payload['return_url'] ?? $summary['return_url'] ?? '');
        $cancelUrl = (string) ($payload['cancel_url'] ?? $summary['cancel_url'] ?? '');
        $continueSuccessUrl = (string) ($payload['continue_success_url'] ?? $summary['return_url'] ?? '');

        $request = [
            'req_time' => $reqTime,
            'merchant_id' => (string) $summary['merchant_id'],
            'tran_id' => (string) $payload['tran_id'],
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'amount' => (float) ($payload['amount'] ?? 0),
            'type' => 'purchase',
            'payment_option' => 'abapay_khqr',
            'items' => $items,
            'currency' => (string) ($payload['currency'] ?? $summary['currency'] ?? 'USD'),
            'return_url' => $returnUrl,
            'cancel_url' => $cancelUrl,
            'continue_success_url' => $continueSuccessUrl,
            'return_deeplink' => '',
            'custom_fields' => null,
            'return_params' => null,
        ];

        $request['hash'] = $this->generatePurchaseHash($request, (string) $summary['api_key']);

        $response = Http::asForm()
            ->acceptJson()
            ->post((string) $summary['purchase_url'], $request);

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

    // ABA purchase hash follows the documented field sequence for QR/deeplink generation on sandbox checkout.
    protected function generatePurchaseHash(array $request, string $apiKey): string
    {
        $string = implode('', [
            (string) ($request['req_time'] ?? ''),
            (string) ($request['merchant_id'] ?? ''),
            (string) ($request['tran_id'] ?? ''),
            number_format((float) ($request['amount'] ?? 0), 2, '.', ''),
            (string) ($request['items'] ?? ''),
            '',
            '',
            (string) ($request['firstname'] ?? ''),
            (string) ($request['lastname'] ?? ''),
            (string) ($request['email'] ?? ''),
            (string) ($request['phone'] ?? ''),
            (string) ($request['type'] ?? ''),
            (string) ($request['payment_option'] ?? ''),
            (string) ($request['return_url'] ?? ''),
            (string) ($request['cancel_url'] ?? ''),
            (string) ($request['continue_success_url'] ?? ''),
            (string) ($request['return_deeplink'] ?? ''),
            (string) ($request['currency'] ?? ''),
            (string) ($request['custom_fields'] ?? ''),
            (string) ($request['return_params'] ?? ''),
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

    protected function limitField(string $value, int $limit): string
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            return '';
        }

        return mb_substr($trimmed, 0, $limit);
    }
}
