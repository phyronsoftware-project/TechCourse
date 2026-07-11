<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ShopPayment;
use App\Models\ShopProduct;
use App\Services\ShopBakongPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ShopPaymentController extends Controller
{
    public function create(string $product, ShopBakongPaymentService $paymentService): JsonResponse
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
            $payment = $paymentService->prepareCheckout($shopProduct, Auth::user());

            return response()->json([
                'success' => true,
                'message' => 'Shop payment QR created successfully.',
                'data' => [
                    'payment_id' => $payment->id,
                    'khqr_string' => $payment->khqr_string,
                    'status' => $payment->status,
                    'expired_at' => optional($payment->expired_at)?->toIso8601String(),
                ],
            ]);
        } catch (Throwable $exception) {
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

            return response()->json([
                'success' => true,
                'message' => 'Shop payment status checked',
                'data' => [
                    'payment_id' => $shopPayment->id,
                    'status' => $shopPayment->status,
                    'transaction_hash' => $shopPayment->transaction_hash,
                    'paid_at' => optional($shopPayment->paid_at)?->toIso8601String(),
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
