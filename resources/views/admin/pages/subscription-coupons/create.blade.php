@extends('admin.layouts.app')
@section('title', 'Create Subscription Coupon')
@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Create Subscription Coupon</h2>
        <p class="admin-section-copy">Configure discount, limits, dates, and allowed plans.</p>
        <form method="POST" action="{{ route('admin.subscription-coupons.store') }}" class="admin-form-grid mt-6">
            @csrf
            @include('admin.pages.subscription-coupons._form')
            <div class="admin-form-actions" style="grid-column: 1 / -1;"><button class="admin-btn admin-btn-primary">Save Coupon</button><a href="{{ route('admin.subscription-coupons.index') }}" class="admin-btn admin-btn-secondary">Cancel</a></div>
        </form>
    </section>
@endsection
