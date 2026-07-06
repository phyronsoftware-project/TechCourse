<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\AbaPayWayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    // Handle ABA PayWay pushback notifications for course checkout payments.
    public function abaReturn(Request $request, AbaPayWayService $abaPaywayService): JsonResponse
    {
        $payload = $request->json()->all();
        $signature = $request->header('X-PAYWAY-HMAC-SHA512');

        if (! is_array($payload) || blank(data_get($payload, 'tran_id'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid ABA callback payload.',
            ], 422);
        }

        if (! $abaPaywayService->verifyCallbackSignature($payload, $signature)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid ABA callback signature.',
            ], 401);
        }

        $payment = Payment::query()
            ->with(['order.items'])
            ->where('payment_provider', 'aba_payway')
            ->where('transaction_id', (string) data_get($payload, 'tran_id'))
            ->latest('id')
            ->first();

        if (! $payment) {
            return response()->json([
                'success' => false,
                'message' => 'ABA payment transaction was not found.',
            ], 404);
        }

        $existingCallbackPayload = is_array($payment->callback_payload) ? $payment->callback_payload : [];
        $payment->forceFill([
            'callback_payload' => array_merge($existingCallbackPayload, [
                'aba_return' => $payload,
                'aba_return_received_at' => now()->toIso8601String(),
            ]),
        ])->save();

        $transactionStatus = (string) data_get($payload, 'status');

        if ($abaPaywayService->isSuccessStatus($transactionStatus)) {
            $checkTransaction = null;

            try {
                $checkTransaction = $abaPaywayService->checkTransaction((string) $payment->transaction_id);
            } catch (\Throwable $exception) {
                $checkTransaction = [
                    'check_transaction_error' => $exception->getMessage(),
                ];
            }

            $this->finalizeAbaCoursePayment($payment, $payload, $checkTransaction);
        } elseif (in_array($transactionStatus, ['1', '2', '3'], true)) {
            $payment->forceFill(['status' => 'failed'])->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'ABA callback received successfully.',
        ]);
    }

    // Respond to ABA cancel callbacks without changing unrelated flow.
    public function abaCancel(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'ABA cancel callback received.',
            'data' => $request->all(),
        ]);
    }

    // Unlock the purchased course only when the ABA payment status is successful.
    protected function finalizeAbaCoursePayment(Payment $payment, array $callbackPayload, ?array $checkTransaction = null): void
    {
        DB::transaction(function () use ($payment, $callbackPayload, $checkTransaction) {
            $order = $payment->order;

            if (! $order) {
                return;
            }

            $paidAt = now();
            $existingResponsePayload = is_array($payment->response_payload) ? $payment->response_payload : [];
            $existingCallbackPayload = is_array($payment->callback_payload) ? $payment->callback_payload : [];

            $payment->forceFill([
                'status' => 'success',
                'paid_at' => $paidAt,
                'callback_payload' => array_merge($existingCallbackPayload, [
                    'aba_check_transaction' => $checkTransaction,
                ]),
                'response_payload' => array_merge($existingResponsePayload, [
                    'aba_callback' => $callbackPayload,
                ]),
            ])->save();

            $order->forceFill([
                'status' => 'paid',
                'payment_method' => 'aba_payway',
                'paid_at' => $paidAt,
            ])->save();

            $courseItem = OrderItem::query()
                ->where('order_id', $order->id)
                ->whereNotNull('course_id')
                ->first();

            if (! $courseItem || ! $courseItem->course_id) {
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
    }
}
