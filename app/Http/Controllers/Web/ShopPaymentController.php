<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ShopPayment;
use App\Models\ShopProduct;
use App\Services\ShopBakongPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ShopPaymentController extends Controller
{
    public function create(Request $request, string $product, ShopBakongPaymentService $paymentService): JsonResponse
    {
        $shopProduct = ShopProduct::query()
            ->where('status', 'active')
            ->where(function ($query) use ($product) {
                $query->where('slug', $product);

                if (is_numeric($product)) {
                    $query->orWhere('id', (int) $product);
                }
            })
            ->firstOrFail();

        try {
            $validated = $request->validate([
                'quantity' => ['required', 'integer', 'min:1'],
            ]);
            $payment = $paymentService->prepareCheckout($shopProduct, Auth::user(), (int) $validated['quantity']);

            return response()->json([
                'success' => true,
                'message' => 'Shop payment QR created successfully.',
                'data' => [
                    'payment_id' => $payment->id,
                    'quantity' => (int) $payment->order?->items?->first()?->qty,
                    'amount' => (float) $payment->amount,
                    'currency' => $payment->currency,
                    'stock_qty' => (int) $shopProduct->fresh()->stock_qty,
                    'khqr_string' => $payment->khqr_string,
                    'status' => $payment->status,
                    'expired_at' => optional($payment->expired_at)?->toIso8601String(),
                ],
            ]);
        } catch (Throwable $exception) {
            // Keep the checkout pending when Bakong is temporarily unreachable.
            if (str_contains($exception->getMessage(), 'cURL error 28')
                || str_contains($exception->getMessage(), 'Connection timed out')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bakong verification is temporarily unavailable. Payment remains pending.',
                    'data' => ['status' => 'pending', 'retry_after' => 10],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function status(ShopPayment $shopPayment, ShopBakongPaymentService $paymentService): JsonResponse
    {
        abort_unless((int) $shopPayment->user_id === (int) Auth::id(), 404);

        try {
            $shopPayment = $paymentService->checkStatus($shopPayment);
            $shopPayment->load('order.items.product');
            $product = $shopPayment->order?->items?->first()?->product;

            return response()->json([
                'success' => true,
                'message' => 'Shop payment status checked',
                'data' => [
                    'payment_id' => $shopPayment->id,
                    'status' => $shopPayment->status,
                    'transaction_hash' => $shopPayment->transaction_hash,
                    'paid_at' => optional($shopPayment->paid_at)?->toIso8601String(),
                    'stock_qty' => $product ? (int) $product->stock_qty : null,
                ],
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => ['status' => $shopPayment->fresh()->status],
            ], 503);
        }
    }
}
