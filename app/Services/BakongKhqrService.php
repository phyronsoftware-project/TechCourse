<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use KHQR\BakongKHQR;
use KHQR\Helpers\KHQRData;
use KHQR\Models\IndividualInfo;
use KHQR\Models\SourceInfo;
use RuntimeException;

class BakongKhqrService
{
    public function summary(): array
    {
        $mode = strtolower((string) config('services.bakong_khqr.mode', 'generated'));
        $accountId = (string) config('services.bakong_khqr.account_id');
        $staticImageUrl = (string) config('services.bakong_khqr.static_image_url', '');
        $staticQrString = (string) config('services.bakong_khqr.static_qr_string', '');
        $localStaticImageUrl = $this->detectLocalStaticImageUrl();

        return [
            'mode' => in_array($mode, ['generated', 'static'], true) ? $mode : 'generated',
            'account_id' => $accountId,
            'merchant_name' => (string) config('services.bakong_khqr.merchant_name', 'TechCourse'),
            'merchant_city' => (string) config('services.bakong_khqr.merchant_city', 'Phnom Penh'),
            'mobile_number' => (string) config('services.bakong_khqr.mobile_number', ''),
            'static_image_url' => $staticImageUrl !== '' ? $staticImageUrl : $localStaticImageUrl,
            'static_qr_string' => $staticQrString,
            'app_name' => (string) config('services.bakong_khqr.app_name', 'TechCourse'),
            'app_icon_url' => (string) config('services.bakong_khqr.app_icon_url', ''),
            'callback_url' => (string) config('services.bakong_khqr.callback_url', ''),
            'token' => (string) config('services.bakong_khqr.token', ''),
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
        $amount = (float) ($payload['amount'] ?? 0);
        $orderNo = (string) ($payload['order_no'] ?? '');
        $courseTitle = (string) ($payload['course_title'] ?? 'TechCourse');

        $individualInfo = new IndividualInfo(
            bakongAccountID: $summary['account_id'],
            merchantName: $summary['merchant_name'],
            merchantCity: $summary['merchant_city'],
            acquiringBank: null,
            accountInformation: null,
            currency: $currency === 'USD' ? KHQRData::CURRENCY_USD : KHQRData::CURRENCY_KHR,
            amount: $amount,
            billNumber: $orderNo !== '' ? $orderNo : null,
            storeLabel: null,
            terminalLabel: null,
            mobileNumber: null,
        );

        $response = BakongKHQR::generateIndividual($individualInfo);
        $responseData = is_array($response->data) ? $response->data : [];
        $qrString = (string) ($responseData['qr'] ?? '');

        if ($qrString === '') {
            throw new RuntimeException('Bakong KHQR generation did not return a QR string.');
        }

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
            'uploads/khqr/acleda-khqr.png',
            'uploads/khqr/bakong-khqr.png',
        ] as $relativePath) {
            if (is_file(public_path($relativePath))) {
                return asset($relativePath);
            }
        }

        return null;
    }
}
