<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use KHQR\BakongKHQR;
use KHQR\Helpers\KHQRData;
use KHQR\Helpers\Utils;
use KHQR\Models\IndividualInfo;
use KHQR\Models\SourceInfo;
use Illuminate\Support\Carbon;
use RuntimeException;

class BakongKhqrService
{
    public function summary(): array
    {
        $mode = strtolower((string) config('bakong.mode', 'generated'));
        $accountId = (string) config('bakong.khqr_account_id');
        $staticImageUrl = (string) config('bakong.static_image_url', '');
        $staticQrString = (string) config('bakong.static_qr_string', '');
        $localStaticImageUrl = $this->detectLocalStaticImageUrl();

        return [
            'mode' => in_array($mode, ['generated', 'static'], true) ? $mode : 'static',
            'account_id' => $accountId,
            'merchant_name' => (string) config('bakong.merchant_name', 'TechCourse'),
            'merchant_city' => (string) config('bakong.merchant_city', 'Phnom Penh'),
            'mobile_number' => (string) config('bakong.mobile_number', ''),
            'static_image_url' => $staticImageUrl !== '' ? $staticImageUrl : $localStaticImageUrl,
            'static_qr_string' => $staticQrString,
            'app_name' => (string) config('bakong.app_name', 'TechCourse'),
            'app_icon_url' => (string) config('bakong.app_icon_url', ''),
            'callback_url' => (string) config('bakong.callback_url', ''),
            'token' => (string) config('bakong.khqr_token', ''),
            'is_ready' => filled($accountId) || filled($staticImageUrl) || filled($localStaticImageUrl) || filled($staticQrString),
        ];
    }

    // Generate a live Bakong KHQR payload, image, and optional deep link for checkout.
    public function generateCheckoutKhqr(array $payload): array
    {
        $summary = $this->summary();

        if (($summary['mode'] ?? 'generated') === 'static') {
            $staticKhqr = $this->configuredStaticKhqr($summary);

            if ($staticKhqr !== null) {
                return $staticKhqr;
            }
        }

        if (! filled($summary['account_id'] ?? null)) {
            throw new RuntimeException('Bakong KHQR account ID is not configured yet.');
        }

        $currency = strtoupper((string) ($payload['currency'] ?? 'KHR'));
        // Normalize KHR amounts to whole riel because Bakong KHQR rejects decimal KHR values.
        $amount = $this->normalizeAmount((float) ($payload['amount'] ?? 0), $currency);
        $orderNo = (string) ($payload['order_no'] ?? '');
        $billNumber = (string) ($payload['bill_number'] ?? $payload['order_no'] ?? '');

        $individualInfo = new IndividualInfo(
            bakongAccountID: $summary['account_id'],
            merchantName: $summary['merchant_name'],
            merchantCity: $summary['merchant_city'],
            acquiringBank: null,
            accountInformation: null,
            currency: $currency === 'USD' ? KHQRData::CURRENCY_USD : KHQRData::CURRENCY_KHR,
            amount: $amount,
            billNumber: $billNumber !== '' ? $billNumber : null,
            storeLabel: $summary['app_name'] !== '' ? $summary['app_name'] : null,
            terminalLabel: null,
            mobileNumber: $summary['mobile_number'] !== '' ? $summary['mobile_number'] : null,
        );

        $response = BakongKHQR::generateIndividual($individualInfo);
        $responseData = is_array($response->data) ? $response->data : [];
        $qrString = (string) ($responseData['qr'] ?? '');

        if ($qrString === '') {
            throw new RuntimeException('Bakong KHQR generation did not return a QR string.');
        }

        // Rewrite the SDK timestamp tag into official creation+expiration subtags so dynamic KHQR stays valid in banking apps.
        $qrString = $this->applyDynamicTimestampPayload($qrString, now(), $this->dynamicExpiryAt());

        $imageResult = (new Builder())->build(
            data: $qrString,
            size: 430,
            margin: 12,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
        );

        $deepLink = null;
        $token = (string) $summary['token'];

        if ($token !== '') {
            try {
                $sourceInfo = new SourceInfo(
                    $summary['app_icon_url'] !== '' ? $summary['app_icon_url'] : null,
                    $summary['app_name'] !== '' ? $summary['app_name'] : null,
                    $summary['callback_url'] !== '' ? $summary['callback_url'] : null,
                );

                $deepLinkResponse = BakongKHQR::generateDeepLink($qrString, $sourceInfo);
                $deepLink = $deepLinkResponse->data->shortLink ?? null;
            } catch (\Throwable) {
                $deepLink = null;
            }
        }

        return [
            'qr_string' => $qrString,
            'md5' => (string) ($responseData['md5'] ?? md5($qrString)),
            'image_data_uri' => $imageResult->getDataUri(),
            'deep_link' => $deepLink,
            'amount' => $amount,
            'currency' => $currency,
        ];
    }

    protected function configuredStaticKhqr(array $summary): ?array
    {
        $staticImageUrl = trim((string) ($summary['static_image_url'] ?? ''));
        $staticQrString = trim((string) ($summary['static_qr_string'] ?? ''));

        if ($staticImageUrl !== '') {
            return [
                'qr_string' => $staticQrString !== '' ? $staticQrString : null,
                'md5' => $staticQrString !== '' ? md5($staticQrString) : null,
                'image_data_uri' => $staticImageUrl,
                'deep_link' => null,
            ];
        }

        if ($staticQrString === '') {
            return null;
        }

        $imageResult = (new Builder())->build(
            data: $staticQrString,
            size: 430,
            margin: 12,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
        );

        return [
            'qr_string' => $staticQrString,
            'md5' => md5($staticQrString),
            'image_data_uri' => $imageResult->getDataUri(),
            'deep_link' => null,
        ];
    }

    protected function detectLocalStaticImageUrl(): ?string
    {
        foreach ([
            'ABA_Images/KHQR_Static.png',
            'uploads/khqr/acleda-khqr.png',
            'uploads/khqr/bakong-khqr.png',
        ] as $relativePath) {
            if (is_file(public_path($relativePath))) {
                return asset($relativePath);
            }
        }

        return null;
    }

    protected function normalizeAmount(float $amount, string $currency): float|int
    {
        if ($currency === 'KHR') {
            return max(1, (int) round($amount));
        }

        return round($amount, 2);
    }

    protected function dynamicExpiryAt(): Carbon
    {
        $minutes = max(1, (int) config('bakong.dynamic_expire_minutes', 10));

        return now()->addMinutes($minutes);
    }

    protected function applyDynamicTimestampPayload(string $qrString, Carbon $createdAt, Carbon $expiresAt): string
    {
        $createdAtMs = (string) $createdAt->utc()->valueOf();
        $expiresAtMs = (string) $expiresAt->utc()->valueOf();
        $timestampValue =
            '00' . str_pad((string) strlen($createdAtMs), 2, '0', STR_PAD_LEFT) . $createdAtMs .
            '01' . str_pad((string) strlen($expiresAtMs), 2, '0', STR_PAD_LEFT) . $expiresAtMs;
        $timestampField = '99' . str_pad((string) strlen($timestampValue), 2, '0', STR_PAD_LEFT) . $timestampValue;

        $qrWithoutCrc = preg_replace('/63\d{2}[A-Fa-f0-9]{4}$/', '', $qrString);
        if (! is_string($qrWithoutCrc) || $qrWithoutCrc === '') {
            throw new RuntimeException('Bakong KHQR CRC payload is invalid.');
        }

        $qrWithoutTimestamp = preg_replace('/99\d{2}(?:00\d{2}\d{13}(?:01\d{2}\d{13})?|01\d{2}\d{13})/', '', $qrWithoutCrc, 1);
        if (! is_string($qrWithoutTimestamp)) {
            throw new RuntimeException('Bakong KHQR timestamp payload is invalid.');
        }

        $payload = $qrWithoutTimestamp . $timestampField . '6304';

        return $payload . Utils::crc16($payload);
    }
}
