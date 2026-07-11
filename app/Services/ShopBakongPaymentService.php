<?php

namespace App\Services;

use App\Models\ShopOrder;
use App\Models\ShopOrderItem;
use App\Models\ShopPayment;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class ShopBakongPaymentService
{
    public function __construct(protected BakongKhqrService $bakongKhqrService) {}

    public function isReady(): bool
    {
        return Schema::hasTable('shop_orders')
            && Schema::hasTable('shop_order_items')
            && Schema::hasTable('shop_payments');
    }

    // Reuse one active checkout so refreshing a product page does not create duplicate orders.
    public function prepareCheckout(ShopProduct $product, User $user, int $quantity = 1): ShopPayment
    {
        if (! $this->isReady()) {
            throw new RuntimeException('Shop payment tables are not configured yet.');
        }

        $quantity = max(1, $quantity);

        $existing = ShopPayment::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('expired_at', '>', now())
            ->whereHas('order.items', fn ($query) => $query
                ->where('product_id', $product->id)
                ->where('qty', $quantity))
            ->latest('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        // Replace an active checkout when the customer changes the requested quantity.
        $differentQuantityPayment = ShopPayment::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('expired_at', '>', now())
            ->whereHas('order.items', fn ($query) => $query->where('product_id', $product->id))
            ->latest('id')
            ->first();

        if ($differentQuantityPayment) {
            $this->closeUnpaidCheckout($differentQuantityPayment, 'cancelled');
        }

        $expiredPending = ShopPayment::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('expired_at', '<=', now())
            ->whereHas('order.items', fn ($query) => $query->where('product_id', $product->id))
            ->latest('id')
            ->first();

        // Do not let a slow Bakong status check block a fresh checkout after QR expiry.
        if ($expiredPending) {
            try {
                if ($this->checkStatus($expiredPending)->status === 'success') {
                    return $expiredPending->fresh();
                }
            } catch (\Throwable) {
                // Continue with a new QR when Bakong is temporarily unreachable.
            }

            if ($expiredPending->fresh()?->status === 'pending') {
                $this->closeUnpaidCheckout($expiredPending, 'expired');
            }
        }

        $amount = round((float) $product->sale_price * $quantity, 2);
        if ($amount <= 0) {
            throw new RuntimeException('This product does not have a valid payment amount.');
        }

        $orderNo = 'SHOP-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        $paymentNo = 'SPAY-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        $khqr = $this->bakongKhqrService->generateCheckoutKhqr([
            'amount' => $amount,
            'currency' => 'USD',
            'order_no' => $orderNo,
            'bill_number' => $paymentNo,
            'course_title' => $product->name,
        ]);
        $khqrString = (string) data_get($khqr, 'qr_string', '');

        if ($khqrString === '') {
            throw new RuntimeException('Unable to generate the shop KHQR payload.');
        }

        return DB::transaction(function () use ($product, $user, $quantity, $amount, $orderNo, $paymentNo, $khqr, $khqrString) {
            $lockedProduct = ShopProduct::query()->lockForUpdate()->findOrFail($product->id);

            $activePayment = ShopPayment::query()
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->where('expired_at', '>', now())
                ->whereHas('order.items', fn ($query) => $query
                    ->where('product_id', $lockedProduct->id)
                    ->where('qty', $quantity))
                ->latest('id')
                ->first();

            if ($activePayment) {
                return $activePayment;
            }

            if ((int) $lockedProduct->stock_qty < $quantity) {
                throw new RuntimeException('This product is out of stock.');
            }

            $order = ShopOrder::query()->create([
                'user_id' => $user->id,
                'order_no' => $orderNo,
                'total_amount' => $amount,
                'currency' => 'USD',
                'status' => 'pending',
                'payment_method' => 'bakong_khqr',
            ]);

            ShopOrderItem::query()->create([
                'shop_order_id' => $order->id,
                'product_id' => $lockedProduct->id,
                'product_name' => $lockedProduct->name,
                'qty' => $quantity,
                'unit_price' => round($amount / $quantity, 2),
                'line_total' => $amount,
            ]);

            // Reserve stock until this payment succeeds, fails, or expires.
            $lockedProduct->decrement('stock_qty', $quantity);

            return ShopPayment::query()->create([
                'shop_order_id' => $order->id,
                'user_id' => $user->id,
                'payment_no' => $paymentNo,
                'payment_provider' => 'bakong_open_api',
                'amount' => $amount,
                'currency' => 'USD',
                'khqr_string' => $khqrString,
                'khqr_md5' => (string) (data_get($khqr, 'md5') ?: md5($khqrString)),
                'bakong_response' => [
                    'khqr_generation' => [
                        'generated_at' => now()->toIso8601String(),
                        'deep_link' => data_get($khqr, 'deep_link'),
                    ],
                ],
                'status' => 'pending',
                'expired_at' => now()->addMinutes(max(1, (int) config('bakong.dynamic_expire_minutes', 10))),
            ]);
        });
    }

    public function checkStatus(ShopPayment $payment): ShopPayment
    {
        if ($payment->status === 'success') {
            return $payment;
        }

        if (in_array($payment->status, ['failed', 'cancelled'], true)) {
            return $payment;
        }

        $response = $this->checkTransactionByMd5((string) $payment->khqr_md5);
        $payment->forceFill([
            'bakong_response' => array_merge($payment->bakong_response ?: [], [
                'check_transaction_by_md5' => $response,
                'checked_at' => now()->toIso8601String(),
            ]),
        ])->save();

        if (! $this->isPaid($response)) {
            $errorCode = (int) data_get($response, 'errorCode', 0);

            if ($errorCode !== 0 && $errorCode !== 1) {
                return $this->closeUnpaidCheckout($payment, 'failed');
            }

            return $payment->isExpired()
                ? $this->closeUnpaidCheckout($payment, 'expired')
                : $payment->fresh();
        }

        $remoteAmount = (float) data_get($response, 'data.amount', -1);
        $remoteCurrency = strtoupper((string) data_get($response, 'data.currency', ''));
        $remoteAccount = strtolower((string) data_get($response, 'data.toAccountId', ''));
        $merchantAccount = strtolower((string) config('bakong.khqr_account_id', ''));

        if (abs($remoteAmount - (float) $payment->amount) >= 0.00001
            || $remoteCurrency !== strtoupper((string) $payment->currency)
            || ($merchantAccount !== '' && $remoteAccount !== '' && $remoteAccount !== $merchantAccount)) {
            return $this->closeUnpaidCheckout($payment, 'failed');
        }

        $transactionHash = (string) data_get($response, 'data.hash');
        if (ShopPayment::query()->where('transaction_hash', $transactionHash)->where('id', '!=', $payment->id)->exists()) {
            return $this->closeUnpaidCheckout($payment, 'failed');
        }

        return DB::transaction(function () use ($payment, $transactionHash) {
            $lockedPayment = ShopPayment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($lockedPayment->status === 'success') {
                return $lockedPayment;
            }

            $paidAt = now();

            $lockedPayment->forceFill([
                'status' => 'success',
                'transaction_id' => $transactionHash,
                'transaction_hash' => $transactionHash,
                'paid_at' => $paidAt,
            ])->save();

            $lockedPayment->order()->update([
                'status' => 'paid',
                'payment_method' => 'bakong_khqr',
                'paid_at' => $paidAt,
            ]);

            return $lockedPayment->fresh();
        });
    }

    protected function checkTransactionByMd5(string $md5): array
    {
        if ($md5 === '' || ! filled(config('bakong.open_api_token'))) {
            throw new RuntimeException('Bakong payment verification is not configured.');
        }

        $baseUrl = rtrim((string) config('bakong.open_api_base_url'), '/');

        foreach (['/v1/check_transaction_by_md5', '/local/v1/check_transaction_by_md5'] as $path) {
            $response = Http::acceptJson()
                ->timeout(15)
                ->withToken((string) config('bakong.open_api_token'))
                ->post($baseUrl.$path, ['md5' => $md5]);

            if ($response->status() === 404) {
                continue;
            }

            if ($response->status() === 401) {
                throw new RuntimeException('Bakong API rejected the access token.');
            }

            if (! $response->successful() || ! is_array($response->json())) {
                throw new RuntimeException('Bakong API is unavailable right now.');
            }

            return $response->json();
        }

        throw new RuntimeException('Bakong transaction status endpoint was not found.');
    }

    protected function isPaid(array $response): bool
    {
        return (int) data_get($response, 'responseCode', 1) === 0
            && filled(data_get($response, 'data.hash'));
    }

    protected function closeUnpaidCheckout(ShopPayment $payment, string $status): ShopPayment
    {
        return DB::transaction(function () use ($payment, $status) {
            $lockedPayment = ShopPayment::query()->lockForUpdate()->findOrFail($payment->id);
            $order = $lockedPayment->order()->lockForUpdate()->first();

            if ($lockedPayment->status !== 'pending' || ! $order || $order->status !== 'pending') {
                return $lockedPayment;
            }

            $item = $order->items()->first();
            if ($item) {
                ShopProduct::query()->whereKey($item->product_id)->increment('stock_qty', $item->qty);
            }

            $lockedPayment->forceFill(['status' => $status])->save();
            $order->forceFill(['status' => $status])->save();

            return $lockedPayment->fresh();
        });
    }
}
