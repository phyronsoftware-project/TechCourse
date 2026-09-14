<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Services\BakongPaymentService;
use App\Services\SubscriptionCouponService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class SubscriptionController extends Controller
{
    public function index(): View
    {
        $plans = SubscriptionPlan::query()
            ->with(['courses' => fn ($query) => $query->orderBy('title')])
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $subscriptions = Auth::check()
            ? UserSubscription::query()
                ->with('plan')
                ->where('user_id', Auth::id())
                ->where('status', 'active')
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->orderByDesc('expires_at')
                ->get()
            : collect();

        return view('web.pages.subscriptions.index', compact('plans', 'subscriptions'));
    }

    public function checkout(
        Request $request,
        SubscriptionPlan $subscriptionPlan,
        BakongPaymentService $bakongPaymentService,
        SubscriptionCouponService $subscriptionCouponService
    ): View|RedirectResponse {
        abort_unless($subscriptionPlan->status === 'active', 404);

        if ((float) $subscriptionPlan->price <= 0) {
            return redirect()->route('subscriptions.index')->with('warning', __('This plan does not require payment.'));
        }

        $subscriptionPlan->load(['courses' => fn ($query) => $query->orderBy('title')]);
        $couponCode = strtoupper(trim($request->string('coupon')->toString()));
        $couponError = null;

        try {
            $pricing = $subscriptionCouponService->calculate($couponCode, $subscriptionPlan, Auth::user());
        } catch (ValidationException $exception) {
            // Keep the original-price checkout available while showing the coupon error.
            $couponError = collect($exception->errors())->flatten()->first();
            $pricing = $subscriptionCouponService->calculate(null, $subscriptionPlan, Auth::user());
        }

        $coupon = $pricing['coupon'];

        $subscription = UserSubscription::query()
            ->where('user_id', Auth::id())
            ->where('plan_id', $subscriptionPlan->id)
            ->where('source', 'payment')
            ->where('status', 'pending')
            ->whereHas('payments', function ($query) use ($coupon, $pricing) {
                $query
                    ->where('status', 'pending')
                    ->where('amount', $pricing['total'])
                    ->where('expired_at', '>', now());

                $coupon ? $query->where('coupon_id', $coupon->id) : $query->whereNull('coupon_id');
            })
            ->with(['payments' => function ($query) use ($coupon, $pricing) {
                $query->where('status', 'pending')->where('amount', $pricing['total']);
                $coupon ? $query->where('coupon_id', $coupon->id) : $query->whereNull('coupon_id');
                $query->latest('id');
            }])
            ->latest('id')
            ->first();

        $payment = $subscription?->payments->first();

        if (! $subscription || ! $payment) {
            try {
                [$subscription, $payment] = DB::transaction(function () use ($subscriptionPlan, $bakongPaymentService, $coupon, $pricing) {
                    // Create a pending entitlement before attaching its KHQR payment.
                    $subscription = UserSubscription::query()->create([
                        'user_id' => Auth::id(),
                        'plan_id' => $subscriptionPlan->id,
                        'source' => 'payment',
                        'status' => 'pending',
                    ]);

                    $payment = $bakongPaymentService->createPaymentQr([
                        'user_id' => Auth::id(),
                        'subscription_id' => $subscription->id,
                        'coupon_id' => $coupon?->id,
                        'coupon_code' => $coupon?->code,
                        'subtotal_amount' => $pricing['subtotal'],
                        'discount_amount' => $pricing['discount'],
                        'amount' => $pricing['total'],
                    ]);

                    return [$subscription, $payment];
                });
            } catch (Throwable $exception) {
                report($exception);

                return redirect()
                    ->route('subscriptions.index')
                    ->with('error', $exception->getMessage());
            }
        }

        return view('web.pages.subscriptions.checkout', [
            'plan' => $subscriptionPlan,
            'subscription' => $subscription,
            'payment' => $payment,
            'pricing' => $pricing,
            'couponCode' => $couponCode,
            'couponError' => $couponError,
        ]);
    }
}
