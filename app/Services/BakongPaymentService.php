<?php

namespace App\Services;

use App\Models\CourseEnrollment;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class BakongPaymentService
{
    public function __construct(
        protected BakongKhqrService $bakongKhqrService,
        protected PaymentHistoryService $paymentHistoryService
    ) {}

    // Create a pending Bakong KHQR payment that the frontend can display as QR.
    public function createPaymentQr(array $data): Payment
    {
        $this->assertCreateConfig();

        // Keep API-created USD KHQR payments at 2 decimal places for dollar-based checkout.
        $amount = round((float) ($data['amount'] ?? 0), 2);
        if ($amount < 1) {
            throw new RuntimeException('Invalid amount.', 422);
        }

        $paymentNo = $this->generatePaymentNo();
        $khqr = $this->bakongKhqrService->generateCheckoutKhqr([
            'amount' => $amount,
            'currency' => 'USD',
            'bill_number' => $paymentNo,
            'order_no' => $paymentNo,
        ]);

        $khqrString = (string) ($khqr['qr_string'] ?? '');
        if ($khqrString === '') {
            throw new RuntimeException('Unable to generate Bakong KHQR string.');
        }

        $payment = Payment::query()->create([
            'order_id' => $data['order_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'payment_no' => $paymentNo,
            'payment_provider' => 'bakong_open_api',
            'payment_option' => 'bakong_khqr',
            'amount' => $amount,
            'currency' => 'USD',
            'khqr_string' => $khqrString,
            'khqr_md5' => md5($khqrString),
            'status' => 'pending',
            'expired_at' => now()->addMinutes(max(1, (int) config('bakong.dynamic_expire_minutes', 10))),
            'bakong_response' => [
                'khqr_generation' => [
                    'md5' => $khqr['md5'] ?? null,
                    'generated_at' => now()->toIso8601String(),
                ],
            ],
        ]);

        // Record the first backend entry for API-generated Bakong payments.
        $this->paymentHistoryService->log($payment, 'api_payment_created', 'Bakong payment QR created from API.', [
            'amount' => $amount,
            'currency' => 'USD',
        ]);

        return $payment;
    }

    // Only backend verification with Bakong Open API can move the payment to success.
    public function checkPaymentStatus(Payment $payment): Payment
    {
        if ($payment->isSuccess()) {
            return $this->finalizeConfirmedPayment($payment)->fresh();
        }

        $this->assertStatusConfig();

        try {
            $result = $this->callCheckTransactionByMd5((string) $payment->khqr_md5);
            $payment = $this->storeBakongResponse($payment, $result);
            $recentStatusCheckExists = $payment->histories()
                ->where('event', 'status_checked')
                ->where('created_at', '>=', now()->subMinute())
                ->exists();

            if (! $recentStatusCheckExists) {
                $this->paymentHistoryService->log($payment, 'status_checked', 'Bakong payment status checked from Open API.', [
                    'bakong_response' => $result,
                ]);
            }
        } catch (RuntimeException $exception) {
            if ($exception->getCode() >= 500 || $exception->getCode() === 0) {
                Log::warning('Bakong payment status check failed.', [
                    'payment_id' => $payment->id,
                    'payment_no' => $payment->payment_no,
                    'error' => $exception->getMessage(),
                    'error_code' => $exception->getCode(),
                ]);
            }

            throw $exception;
        } catch (Throwable $exception) {
            Log::error('Unexpected Bakong payment status error.', [
                'payment_id' => $payment->id,
                'payment_no' => $payment->payment_no,
                'error' => $exception->getMessage(),
            ]);

            throw new RuntimeException('Unable to check payment status right now.', 503);
        }

        if (! $this->isBakongPaid($result)) {
            // Quota exhaustion is inconclusive, so never expire a payment that may already be paid.
            if ((int) data_get($result, 'errorCode', 0) === 17) {
                return $payment->fresh();
            }

            // Check Bakong once before expiring so a payment made near the deadline is not missed.
            if ($payment->isExpired()) {
                $payment->forceFill(['status' => 'expired'])->save();
                $this->paymentHistoryService->log($payment->fresh(), 'payment_expired', 'Bakong payment expired without a confirmed transaction.');
            }

            return $payment;
        }

        if (! $this->amountMatches($payment, $result)) {
            Log::warning('Bakong payment amount mismatch detected.', [
                'payment_id' => $payment->id,
                'payment_no' => $payment->payment_no,
            ]);

            $payment->forceFill(['status' => 'failed'])->save();
            $this->paymentHistoryService->log($payment->fresh(), 'payment_failed', 'Bakong amount mismatch detected.', [
                'bakong_response' => $result,
            ]);

            throw new RuntimeException('Bakong amount mismatch detected.', 409);
        }

        if (! $this->currencyMatches($payment, $result)) {
            Log::warning('Bakong payment currency mismatch detected.', [
                'payment_id' => $payment->id,
                'payment_no' => $payment->payment_no,
            ]);

            $payment->forceFill(['status' => 'failed'])->save();
            $this->paymentHistoryService->log($payment->fresh(), 'payment_failed', 'Bakong currency mismatch detected.', [
                'bakong_response' => $result,
            ]);

            throw new RuntimeException('Bakong currency mismatch detected.', 409);
        }

        if (! $this->accountMatches($result)) {
            $payment->forceFill(['status' => 'failed'])->save();
            $this->paymentHistoryService->log($payment->fresh(), 'payment_failed', 'Bakong receiving account mismatch detected.', [
                'bakong_response' => $result,
            ]);

            throw new RuntimeException('Bakong receiving account mismatch detected.', 409);
        }

        $transactionHash = $this->extractTransactionHash($result);
        $hashAlreadyUsed = filled($transactionHash) && Payment::query()
            ->where('transaction_hash', $transactionHash)
            ->where('id', '!=', $payment->id)
            ->exists();

        if ($hashAlreadyUsed) {
            $payment->forceFill(['status' => 'failed'])->save();
            $this->paymentHistoryService->log($payment->fresh(), 'payment_failed', 'Duplicate Bakong transaction hash rejected.');

            throw new RuntimeException('This Bakong transaction was already used.', 409);
        }

        if (! $payment->isSuccess()) {
            $payment->forceFill([
                'status' => 'success',
                'transaction_id' => $transactionHash,
                'transaction_hash' => $transactionHash,
                'paid_at' => $this->extractPaidAt($result) ?? now(),
            ])->save();

            $this->paymentHistoryService->log($payment->fresh(), 'payment_confirmed_by_api', 'Bakong payment confirmed by backend API verification.', [
                'bakong_response' => $result,
            ]);
        }

        $payment = $this->finalizeConfirmedPayment($payment);

        return $payment->fresh();
    }

    protected function assertCreateConfig(): void
    {
        if (! filled(config('bakong.open_api_token'))) {
            throw new RuntimeException('Bakong Open API token is not configured.', 500);
        }

        if (! filled(config('bakong.khqr_account_id'))) {
            throw new RuntimeException('Bakong KHQR account ID is not configured.', 500);
        }

        if (! filled(config('bakong.merchant_name'))) {
            throw new RuntimeException('Bakong merchant name is not configured.', 500);
        }
    }

    protected function assertStatusConfig(): void
    {
        if (! filled(config('bakong.open_api_token'))) {
            throw new RuntimeException('Bakong Open API token is not configured.', 500);
        }
    }

    protected function generatePaymentNo(): string
    {
        do {
            $paymentNo = 'PAY-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Payment::query()->where('payment_no', $paymentNo)->exists());

        return $paymentNo;
    }

    protected function callCheckTransactionByMd5(string $md5): array
    {
        if ($md5 === '') {
            throw new RuntimeException('Payment KHQR MD5 is missing.', 422);
        }

        $baseUrl = rtrim((string) config('bakong.open_api_base_url', 'https://api-bakong.nbc.gov.kh'), '/');
        $payload = ['md5' => $md5];
        $paths = [
            '/v1/check_transaction_by_md5',
            '/local/v1/check_transaction_by_md5',
        ];

        $lastException = null;
        $quotaResponse = null;

        foreach ($paths as $path) {
            try {
                $response = Http::acceptJson()
                    ->timeout(15)
                    ->withToken((string) config('bakong.open_api_token'))
                    ->post($baseUrl.$path, $payload);

                if ($response->status() === 404) {
                    continue;
                }

                if ($response->status() === 401) {
                    throw new RuntimeException('Bakong API rejected the token.', 401);
                }

                if (! $response->successful()) {
                    throw new RuntimeException('Bakong API is unavailable right now.', 503);
                }

                $data = $response->json();
                if (! is_array($data)) {
                    throw new RuntimeException('Bakong API returned an invalid response.', 503);
                }

                $responseCode = data_get($data, 'responseCode');
                if ($responseCode !== null && (int) $responseCode !== 0) {
                    $message = (string) data_get($data, 'responseMessage', '');
                    $errorCode = (int) data_get($data, 'errorCode', 0);

                    // Official errorCode=1 means no payment yet and must remain pending.
                    if ($errorCode === 1 || str_contains(strtolower($message), 'not found') || str_contains(strtolower($message), 'could not be found')) {
                        return $data;
                    }

                    // Preserve quota exhaustion as pending even when the fallback endpoint is forbidden.
                    if ($errorCode === 17) {
                        $quotaResponse = $data;
                        continue;
                    }

                    throw new RuntimeException($message !== '' ? $message : 'Bakong API returned an error.', 503);
                }

                return $data;
            } catch (Throwable $exception) {
                $lastException = $exception;
            }
        }

        if (is_array($quotaResponse)) {
            return $quotaResponse;
        }

        if ($lastException instanceof RuntimeException) {
            throw $lastException;
        }

        throw new RuntimeException('Unable to check payment status right now.', 503);
    }

    protected function storeBakongResponse(Payment $payment, array $response): Payment
    {
        $existingResponse = is_array($payment->bakong_response) ? $payment->bakong_response : [];

        $payment->forceFill([
            'bakong_response' => array_merge($existingResponse, [
                'check_transaction_by_md5' => $response,
                'checked_at' => now()->toIso8601String(),
            ]),
        ])->save();

        return $payment->fresh();
    }

    protected function isBakongPaid(array $response): bool
    {
        $trackingStatus = strtoupper((string) data_get($response, 'data.trackingStatus', ''));
        $status = strtolower((string) data_get($response, 'data.status', data_get($response, 'status', '')));
        $responseCode = data_get($response, 'responseCode');
        $transactionHash = (string) (
            data_get($response, 'data.hash')
            ?? data_get($response, 'data.transactionHash')
            ?? data_get($response, 'data.txHash')
            ?? data_get($response, 'hash')
        );

        // Official Bakong md5/hash lookup can confirm a paid transaction by returning responseCode=0 with a transaction hash.
        if ((int) $responseCode === 0 && $transactionHash !== '') {
            return true;
        }

        return in_array($trackingStatus, ['ACKNOWLEDGED_BY_FI', 'SUCCESS'], true)
            || in_array($status, ['success', 'paid', 'completed'], true);
    }

    protected function amountMatches(Payment $payment, array $response): bool
    {
        $remoteAmount = data_get($response, 'data.amount', data_get($response, 'amount'));

        if ($remoteAmount === null || $remoteAmount === '') {
            return true;
        }

        return abs((float) $remoteAmount - (float) $payment->amount) < 0.00001;
    }

    protected function currencyMatches(Payment $payment, array $response): bool
    {
        $remoteCurrency = strtoupper((string) data_get($response, 'data.currency', data_get($response, 'currency', '')));

        if ($remoteCurrency === '') {
            return true;
        }

        return $remoteCurrency === strtoupper((string) $payment->currency);
    }

    protected function accountMatches(array $response): bool
    {
        $remoteAccount = strtolower((string) data_get($response, 'data.toAccountId', ''));
        $merchantAccount = strtolower((string) config('bakong.khqr_account_id', ''));

        return $merchantAccount === '' || $remoteAccount === '' || $remoteAccount === $merchantAccount;
    }

    protected function extractTransactionHash(array $response): ?string
    {
        return data_get($response, 'data.hash')
            ?? data_get($response, 'data.transactionHash')
            ?? data_get($response, 'data.txHash')
            ?? data_get($response, 'hash');
    }

    protected function extractPaidAt(array $response): ?Carbon
    {
        $value = data_get($response, 'data.timestamp')
            ?? data_get($response, 'data.createdDate')
            ?? data_get($response, 'data.transactionDate');

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    protected function finalizeConfirmedPayment(Payment $payment): Payment
    {
        if (! $payment->isSuccess()) {
            return $payment;
        }

        $payment->loadMissing('order.items');
        $order = $payment->order;

        if (! $order) {
            return $payment;
        }

        $orderWasPaid = $order->status === 'paid';

        // Finalize the related order and unlock the purchased course only after backend Bakong confirmation.
        DB::transaction(function () use ($payment, $order) {
            $paidAt = $payment->paid_at ?? now();

            $order->forceFill([
                'status' => 'paid',
                'payment_method' => 'bakong_khqr',
                'paid_at' => $paidAt,
            ])->save();

            $courseItem = OrderItem::query()
                ->where('order_id', $order->id)
                ->whereNotNull('course_id')
                ->first();

            if (! $courseItem || ! $payment->user_id) {
                return;
            }

            CourseEnrollment::query()->updateOrCreate(
                [
                    'user_id' => $payment->user_id,
                    'course_id' => $courseItem->course_id,
                ],
                [
                    'order_id' => $order->id,
                    'access_type' => 'paid',
                    'status' => 'active',
                    'started_at' => $paidAt,
                ],
            );
        });

        if (! $orderWasPaid) {
            // Keep the related order state change visible in backend history.
            $this->paymentHistoryService->log($payment->fresh('order'), 'order_paid', 'Order marked as paid after Bakong confirmation.');
        }

        return $payment->fresh();
    }
}
