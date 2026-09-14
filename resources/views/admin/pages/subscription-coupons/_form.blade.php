@php
    $selectedPlans = collect(old('plan_ids', isset($coupon) ? $coupon->plans->pluck('id')->all() : []))->map(fn ($id) => (int) $id)->all();
@endphp

<div class="admin-field"><label>Name</label><input name="name" value="{{ old('name', $coupon->name ?? '') }}" class="admin-input" required></div>
<div class="admin-field"><label>Coupon Code</label><input name="code" value="{{ old('code', $coupon->code ?? '') }}" class="admin-input" placeholder="WELCOME20" required></div>
<div class="admin-field"><label>Discount Type</label><select name="discount_type" class="admin-select"><option value="percentage" @selected(old('discount_type', $coupon->discount_type ?? 'percentage') === 'percentage')>Percentage</option><option value="fixed" @selected(old('discount_type', $coupon->discount_type ?? '') === 'fixed')>Fixed USD</option></select></div>
<div class="admin-field"><label>Discount Value</label><input type="number" step="0.01" min="0.01" name="discount_value" value="{{ old('discount_value', $coupon->discount_value ?? '') }}" class="admin-input" required></div>
<div class="admin-field"><label>Maximum Discount (optional)</label><input type="number" step="0.01" min="0.01" name="maximum_discount" value="{{ old('maximum_discount', $coupon->maximum_discount ?? '') }}" class="admin-input"></div>
<div class="admin-field"><label>Minimum Plan Amount</label><input type="number" step="0.01" min="0" name="minimum_amount" value="{{ old('minimum_amount', $coupon->minimum_amount ?? 0) }}" class="admin-input"></div>
<div class="admin-field"><label>Total Usage Limit</label><input type="number" min="1" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}" class="admin-input" placeholder="Unlimited"></div>
<div class="admin-field"><label>Per-user Limit</label><input type="number" min="1" name="per_user_limit" value="{{ old('per_user_limit', $coupon->per_user_limit ?? 1) }}" class="admin-input"></div>
<div class="admin-field"><label>Starts At</label><input type="datetime-local" name="starts_at" value="{{ old('starts_at', isset($coupon) ? optional($coupon->starts_at)->format('Y-m-d\TH:i') : '') }}" class="admin-input"></div>
<div class="admin-field"><label>Expires At</label><input type="datetime-local" name="expires_at" value="{{ old('expires_at', isset($coupon) ? optional($coupon->expires_at)->format('Y-m-d\TH:i') : '') }}" class="admin-input"></div>
<div class="admin-field"><label>Status</label><select name="status" class="admin-select"><option value="active" @selected(old('status', $coupon->status ?? 'active') === 'active')>Active</option><option value="inactive" @selected(old('status', $coupon->status ?? '') === 'inactive')>Inactive</option></select></div>
<div class="admin-field" style="grid-column: 1 / -1;">
    <label>Allowed Plans</label>
    {{-- An empty selection makes the coupon available to all subscription plans. --}}
    <select name="plan_ids[]" class="admin-select" multiple size="7">
        @foreach($plans as $plan)<option value="{{ $plan->id }}" @selected(in_array((int) $plan->id, $selectedPlans, true))>{{ $plan->name }}</option>@endforeach
    </select>
    <small>Leave empty for all plans. Hold Ctrl/Command to select multiple plans.</small>
</div>
