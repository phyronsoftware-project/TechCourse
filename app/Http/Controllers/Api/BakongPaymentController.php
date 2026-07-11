<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\BakongPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class BakongPaymentController extends Controller
{
    public function __construct(
        protected BakongPaymentService $bakongPaymentService
    ) {}

    // Create a Bakong KHQR payment and return the QR payload for frontend display.
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'order_id' => ['nullable', 'integer'],
        ]);
        $validated['user_id'] = $request->user()->id;

        if (! empty($validated['order_id'])) {
            $ownsOrder = Order::query()
                ->whereKey($validated['order_id'])
                ->where('user_id', $request->user()->id)
                ->exists();

            abort_unless($ownsOrder, 404);
        }

        try {
            $payment = $this->bakongPaymentService->createPaymentQr($validated);

            return response()->json([
                'success' => true,
                'message' => 'Payment QR created successfully',
                'data' => $this->paymentPayload($payment, true),
            ]);
        } catch (RuntimeException $exception) {
            return $this->runtimeErrorResponse($exception, 'Unable to create payment QR right now');
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to create payment QR right now',
                'error' => 'BAKONG_CREATE_PAYMENT_ERROR',
            ], 500);
        }
    }

    // Check the current payment status using the backend Bakong verification flow.
    public function status(Request $request, Payment $payment): JsonResponse
    {
        abort_unless((int) $payment->user_id === (int) $request->user()->id, 404);

        try {
            $payment = $this->bakongPaymentService->checkPaymentStatus($payment);

            return response()->json([
                'success' => true,
                'message' => 'Payment status checked',
                'data' => $this->paymentPayload($payment),
            ]);
        } catch (RuntimeException $exception) {
            return $this->runtimeErrorResponse($exception, 'Unable to check payment status right now');
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to check payment status right now',
                'error' => 'BAKONG_STATUS_CHECK_ERROR',
            ], 500);
        }
    }

    // Return the stored payment detail without calling Bakong again.
    public function show(Request $request, Payment $payment): JsonResponse
    {
        abort_unless((int) $payment->user_id === (int) $request->user()->id, 404);

        return response()->json([
            'success' => true,
            'message' => 'Payment detail fetched successfully',
            'data' => $this->paymentPayload($payment, true),
        ]);
    }

    protected function paymentPayload(Payment $payment, bool $withKhqr = false): array
    {
        $payload = [
            'payment_id' => $payment->id,
            'payment_no' => $payment->payment_no,
            'amount' => number_format((float) $payment->amount, 2, '.', ''),
            'currency' => $payment->currency,
            'status' => $payment->status,
            'transaction_hash' => $payment->transaction_hash,
            'paid_at' => optional($payment->paid_at)?->toIso8601String(),
            'expired_at' => optional($payment->expired_at)?->toIso8601String(),
        ];

        if ($withKhqr) {
            $payload['khqr_string'] = $payment->khqr_string;
            $payload['khqr_md5'] = $payment->khqr_md5;
        }

        return $payload;
    }

    protected function runtimeErrorResponse(RuntimeException $exception, string $fallbackMessage): JsonResponse
    {
        $status = $exception->getCode();
        $status = is_int($status) && $status >= 400 && $status <= 599 ? $status : 503;

        return response()->json([
            'success' => false,
            'message' => $fallbackMessage,
            'error' => $this->mapErrorCode($exception->getMessage(), $status),
        ], $status);
    }

    protected function mapErrorCode(string $message, int $status): string
    {
        $normalized = strtolower($message);

        return match (true) {
            str_contains($normalized, 'token') && $status === 401 => 'BAKONG_API_INVALID_TOKEN',
            str_contains($normalized, 'token') => 'BAKONG_TOKEN_MISSING',
            str_contains($normalized, 'account id') => 'BAKONG_KHQR_ACCOUNT_ID_MISSING',
            str_contains($normalized, 'merchant name') => 'BAKONG_MERCHANT_NAME_MISSING',
            str_contains($normalized, 'invalid amount') => 'INVALID_AMOUNT',
            str_contains($normalized, 'expired') => 'PAYMENT_EXPIRED',
            str_contains($normalized, 'amount mismatch') => 'BAKONG_AMOUNT_MISMATCH',
            str_contains($normalized, 'currency mismatch') => 'BAKONG_CURRENCY_MISMATCH',
            str_contains($normalized, 'not found') => 'BAKONG_TRANSACTION_NOT_FOUND',
            str_contains($normalized, 'unavailable') => 'BAKONG_API_UNAVAILABLE',
            $status === 503 => 'BAKONG_API_TIMEOUT',
            default => 'BAKONG_PAYMENT_ERROR',
        };
    }
}
