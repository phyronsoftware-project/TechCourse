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
        $accountId = (string) config('services.bakong_khqr.account_id');

        return [
            'account_id' => $accountId,
            'merchant_name' => (string) config('services.bakong_khqr.merchant_name', 'TechCourse'),
            'merchant_city' => (string) config('services.bakong_khqr.merchant_city', 'Phnom Penh'),
            'mobile_number' => (string) config('services.bakong_khqr.mobile_number', ''),
            'app_name' => (string) config('services.bakong_khqr.app_name', 'TechCourse'),
            'app_icon_url' => (string) config('services.bakong_khqr.app_icon_url', ''),
            'callback_url' => (string) config('services.bakong_khqr.callback_url', ''),
            'token' => (string) config('services.bakong_khqr.token', ''),
            'is_ready' => filled($accountId),
        ];
    }

    // Generate a live Bakong KHQR payload, image, and optional deep link for checkout.
    public function generateCheckoutKhqr(array $payload): array
    {
        $summary = $this->summary();

        if (! $summary['is_ready']) {
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
            storeLabel: $courseTitle !== '' ? mb_substr($courseTitle, 0, 25) : null,
            terminalLabel: null,
            mobileNumber: $summary['mobile_number'] !== '' ? $summary['mobile_number'] : null,
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
}
