@extends('admin.layouts.app')
@section('title', 'Edit Subscription Coupon')
@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Edit Subscription Coupon</h2>
        <p class="admin-section-copy">Update future coupon rules without removing usage history.</p>
        <form method="POST" action="{{ route('admin.subscription-coupons.update', $coupon) }}" class="admin-form-grid mt-6">
            @csrf @method('PUT')
            @include('admin.pages.subscription-coupons._form')
            <div class="admin-form-actions" style="grid-column: 1 / -1;"><button class="admin-btn admin-btn-primary">Update Coupon</button><a href="{{ route('admin.subscription-coupons.index') }}" class="admin-btn admin-btn-secondary">Cancel</a></div>
        </form>
    </section>
@endsection
