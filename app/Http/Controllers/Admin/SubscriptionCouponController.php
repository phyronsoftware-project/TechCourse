<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionCoupon;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SubscriptionCouponController extends Controller
{
    public function index(Request $request): View
    {
        $query = SubscriptionCoupon::query()->withCount(['plans', 'usages'])->latest('id');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%"));
        }

        return view('admin.pages.subscription-coupons.index', [
            'pageTitle' => 'Subscription Coupons',
            'coupons' => $query->paginate(12)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.subscription-coupons.create', [
            'pageTitle' => 'Create Subscription Coupon',
            'plans' => SubscriptionPlan::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $planIds = $data['plan_ids'] ?? [];
        unset($data['plan_ids']);
        $data['code'] = strtoupper(trim($data['code']));

        // Save coupon rules and optional plan restrictions together.
        $coupon = SubscriptionCoupon::query()->create($data);
        $coupon->plans()->sync($planIds);

        return redirect()->route('admin.subscription-coupons.index')->with('success', 'Coupon created successfully.');
    }

    public function edit(SubscriptionCoupon $subscriptionCoupon): View
    {
        $subscriptionCoupon->load('plans');

        return view('admin.pages.subscription-coupons.edit', [
            'pageTitle' => 'Edit Subscription Coupon',
            'coupon' => $subscriptionCoupon,
            'plans' => SubscriptionPlan::query()->orderBy('sort_order')->orderBy('name')->get(),
            'recordId' => $subscriptionCoupon->id,
        ]);
    }

    public function update(Request $request, SubscriptionCoupon $subscriptionCoupon): RedirectResponse
    {
        $data = $this->validatedData($request, $subscriptionCoupon);
        $planIds = $data['plan_ids'] ?? [];
        unset($data['plan_ids']);
        $data['code'] = strtoupper(trim($data['code']));

        // Keep previous usage history while updating future coupon rules.
        $subscriptionCoupon->update($data);
        $subscriptionCoupon->plans()->sync($planIds);

        return redirect()->route('admin.subscription-coupons.index')->with('success', 'Coupon updated successfully.');
    }

    public function destroy(SubscriptionCoupon $subscriptionCoupon): RedirectResponse
    {
        if ($subscriptionCoupon->usages()->exists()) {
            return back()->with('warning', 'This coupon has usage history. Set it inactive instead of deleting it.');
        }

        $subscriptionCoupon->delete();

        return redirect()->route('admin.subscription-coupons.index')->with('success', 'Coupon deleted successfully.');
    }

    protected function validatedData(Request $request, ?SubscriptionCoupon $coupon = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:80', Rule::unique('subscription_coupons', 'code')->ignore($coupon?->id)],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'gt:0', 'max:999999.99'],
            'maximum_discount' => ['nullable', 'numeric', 'gt:0'],
            'minimum_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive'],
            'plan_ids' => ['nullable', 'array'],
            'plan_ids.*' => ['integer', 'exists:subscription_plans,id'],
        ]);

        if ($data['discount_type'] === 'percentage' && (float) $data['discount_value'] > 100) {
            throw ValidationException::withMessages(['discount_value' => 'Percentage discount cannot exceed 100.']);
        }

        if (filled($data['expires_at'] ?? null)) {
            $startsAt = filled($data['starts_at'] ?? null) ? Carbon::parse($data['starts_at']) : now();

            if (Carbon::parse($data['expires_at'])->lessThanOrEqualTo($startsAt)) {
                throw ValidationException::withMessages(['expires_at' => 'Expiration must be after the coupon start time.']);
            }
        }

        $data['minimum_amount'] = $data['minimum_amount'] ?? 0;
        $data['per_user_limit'] = $data['per_user_limit'] ?? 1;

        return $data;
    }
}
