<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentHistory;

class PaymentHistoryService
{
    // Store a compact backend audit trail for each payment/order state change.
    public function log(?Payment $payment, string $event, ?string $message = null, array $payload = [], ?Order $order = null): PaymentHistory
    {
        $resolvedOrder = $order ?: $payment?->order;

        return PaymentHistory::query()->create([
            'payment_id' => $payment?->id,
            'order_id' => $resolvedOrder?->id,
            'user_id' => $payment?->user_id ?: $resolvedOrder?->user_id,
            'event' => $event,
            'payment_status' => $payment?->status,
            'order_status' => $resolvedOrder?->status,
            'message' => $message,
            'payload' => $payload,
        ]);
    }
}
