<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\SubscriptionCoupon;
use App\Models\SubscriptionCouponUsage;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Validation\ValidationException;

class SubscriptionCouponService
{
    // Validate a code and calculate the exact KHQR amount for one plan.
    public function calculate(?string $code, SubscriptionPlan $plan, User $user): array
    {
        $subtotal = round((float) $plan->price, 2);
        $normalizedCode = strtoupper(trim((string) $code));

        if ($normalizedCode === '') {
            return $this->pricing($subtotal);
        }

        $coupon = SubscriptionCoupon::query()
            ->whereRaw('UPPER(code) = ?', [$normalizedCode])
            ->where('status', 'active')
            ->first();

        if (! $coupon) {
            throw ValidationException::withMessages(['coupon' => __('Coupon code is invalid.')]);
        }

        if (($coupon->starts_at && $coupon->starts_at->isFuture())
            || ($coupon->expires_at && $coupon->expires_at->isPast())) {
            throw ValidationException::withMessages(['coupon' => __('Coupon is not active right now.')]);
        }

        if ((float) $coupon->minimum_amount > $subtotal) {
            throw ValidationException::withMessages(['coupon' => __('This plan does not meet the coupon minimum amount.')]);
        }

        if ($coupon->plans()->exists() && ! $coupon->plans()->whereKey($plan->id)->exists()) {
            throw ValidationException::withMessages(['coupon' => __('Coupon is not available for this plan.')]);
        }

        if ($coupon->usage_limit !== null && $coupon->usages()->count() >= $coupon->usage_limit) {
            throw ValidationException::withMessages(['coupon' => __('Coupon usage limit has been reached.')]);
        }

        if ($coupon->per_user_limit !== null
            && $coupon->usages()->where('user_id', $user->id)->count() >= $coupon->per_user_limit) {
            throw ValidationException::withMessages(['coupon' => __('You have already used this coupon.')]);
        }

        $discount = $coupon->discount_type === 'percentage'
            ? $subtotal * ((float) $coupon->discount_value / 100)
            : (float) $coupon->discount_value;

        if ($coupon->maximum_discount !== null) {
            $discount = min($discount, (float) $coupon->maximum_discount);
        }

        // Keep at least USD 0.01 because the existing KHQR flow requires a payable amount.
        $discount = round(min(max(0, $discount), max(0, $subtotal - 0.01)), 2);

        return $this->pricing($subtotal, $discount, $coupon);
    }

    // Record coupon consumption once after Bakong confirms the payment.
    public function recordUsage(Payment $payment, UserSubscription $subscription): void
    {
        if (! $payment->coupon_id || (float) $payment->discount_amount <= 0) {
            return;
        }

        SubscriptionCouponUsage::query()->firstOrCreate(
            ['payment_id' => $payment->id],
            [
                'coupon_id' => $payment->coupon_id,
                'user_id' => $payment->user_id,
                'subscription_id' => $subscription->id,
                'discount_amount' => $payment->discount_amount,
                'used_at' => $payment->paid_at ?? now(),
            ],
        );
    }

    protected function pricing(float $subtotal, float $discount = 0, ?SubscriptionCoupon $coupon = null): array
    {
        return [
            'coupon' => $coupon,
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'total' => round(max(0, $subtotal - $discount), 2),
        ];
    }
}
