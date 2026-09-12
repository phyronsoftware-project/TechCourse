<?php

use App\Models\Payment;
use App\Models\ShopPayment;
use App\Services\BakongPaymentService;
use App\Services\ShopBakongPaymentService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Reconcile pending KHQR payments even when the customer closes the browser.
Artisan::command('payments:reconcile-bakong', function () {
    $courseService = app(BakongPaymentService::class);
    $shopService = app(ShopBakongPaymentService::class);
    $checked = 0;

    if (Schema::hasTable('payments')) {
        Payment::query()
            ->where('payment_provider', 'bakong_open_api')
            ->where('status', 'pending')
            ->whereNotNull('khqr_md5')
            // Ignore stale rows and avoid checking the same QR more than once every two minutes.
            ->where('expired_at', '>=', now()->subMinutes(15))
            ->latest('id')
            ->limit(5)
            ->get()
            ->filter(function (Payment $payment): bool {
                $checkedAt = data_get($payment->bakong_response, 'checked_at');

                return ! $checkedAt || Carbon::parse($checkedAt)->lte(now()->subMinutes(2));
            })
            ->each(function (Payment $payment) use ($courseService, &$checked) {
                try {
                    $courseService->checkPaymentStatus($payment);
                    $checked++;
                } catch (Throwable $exception) {
                    Log::warning('Scheduled course Bakong reconciliation failed.', [
                        'payment_id' => $payment->id,
                        'error' => $exception->getMessage(),
                    ]);
                }
            });
    }

    if ($shopService->isReady()) {
        ShopPayment::query()
            ->where('payment_provider', 'bakong_open_api')
            // Reconcile active rows plus recent rows falsely failed by Bakong quota exhaustion.
            ->whereIn('status', ['pending', 'failed'])
            ->whereNotNull('khqr_md5')
            ->where('expired_at', '>=', now()->subDays(2))
            ->latest('id')
            ->limit(5)
            ->get()
            ->filter(function (ShopPayment $payment): bool {
                $quotaFailure = (int) data_get($payment->bakong_response, 'check_transaction_by_md5.errorCode', 0) === 17;
                if ($payment->status === 'failed' && ! $quotaFailure) {
                    return false;
                }

                $checkedAt = data_get($payment->bakong_response, 'checked_at');
                $retryBefore = $quotaFailure ? now()->subMinutes(30) : now()->subMinutes(2);

                return ! $checkedAt || Carbon::parse($checkedAt)->lte($retryBefore);
            })
            ->each(function (ShopPayment $payment) use ($shopService, &$checked) {
                try {
                    $shopService->checkStatus($payment);
                    $checked++;
                } catch (Throwable $exception) {
                    Log::warning('Scheduled shop Bakong reconciliation failed.', [
                        'payment_id' => $payment->id,
                        'error' => $exception->getMessage(),
                    ]);
                }
            });
    }

    $this->info("Reconciled {$checked} pending Bakong payment(s).");
})->purpose('Confirm pending course and shop KHQR payments with Bakong Open API.');

Schedule::command('payments:reconcile-bakong')
    ->everyMinute()
    ->withoutOverlapping();
