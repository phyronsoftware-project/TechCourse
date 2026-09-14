@extends('admin.layouts.app')
@section('title', 'Subscription Coupons')
@section('content')
    <section class="admin-filter-card p-6"><form method="GET" action="{{ route('admin.subscription-coupons.index') }}" class="admin-filter-grid"><div class="admin-field admin-filter-field-wide"><label>Search</label><input name="search" value="{{ request('search') }}" class="admin-input" placeholder="Coupon name or code..."></div><div class="admin-filter-actions"><button class="admin-btn admin-btn-primary">Filter</button><a href="{{ route('admin.subscription-coupons.index') }}" class="admin-btn admin-btn-secondary">Reset</a></div></form></section>
    <section class="admin-index-panel admin-index-panel-table">
        <div class="admin-page-header"><div><h3 class="admin-page-title">Subscription Coupons</h3><p class="admin-page-copy">Manage subscription discounts and usage rules.</p></div><a href="{{ route('admin.subscription-coupons.create') }}" class="admin-btn admin-btn-primary">Create Coupon</a></div>
        <div class="admin-table-wrap"><table class="admin-table">
            <thead><tr><th>Code</th><th>Name</th><th>Discount</th><th>Plans</th><th>Used</th><th>Limit</th><th>Expires</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($coupons as $coupon)
                    <tr>
                        <td><strong>{{ $coupon->code }}</strong></td><td>{{ $coupon->name }}</td>
                        <td>{{ $coupon->discount_type === 'percentage' ? rtrim(rtrim(number_format((float) $coupon->discount_value, 2), '0'), '.') . '%' : 'USD ' . number_format((float) $coupon->discount_value, 2) }}</td>
                        <td>{{ $coupon->plans_count ?: 'All' }}</td><td>{{ $coupon->usages_count }}</td><td>{{ $coupon->usage_limit ?: 'Unlimited' }}</td><td>{{ optional($coupon->expires_at)->format('Y-m-d H:i') ?: 'Never' }}</td>
                        <td><span class="admin-status-badge admin-status-badge-{{ $coupon->status }}">{{ $coupon->status }}</span></td>
                        <td><a href="{{ route('admin.subscription-coupons.edit', $coupon) }}" class="admin-btn admin-btn-secondary">Edit</a><form method="POST" action="{{ route('admin.subscription-coupons.destroy', $coupon) }}" class="inline" onsubmit="return confirm('Delete this coupon?')">@csrf @method('DELETE')<button class="admin-btn admin-btn-secondary">Delete</button></form></td>
                    </tr>
                @empty<tr><td colspan="9" class="admin-empty">No coupons yet.</td></tr>@endforelse
            </tbody>
        </table></div>
        <div class="mt-5">{{ $coupons->links() }}</div>
    </section>
@endsection
