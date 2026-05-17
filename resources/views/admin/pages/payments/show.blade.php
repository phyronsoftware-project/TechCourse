@extends('admin.layouts.app')

@section('title', 'Payment Details')

@section('content')
    <section class="admin-form-card p-6">
        <div class="admin-page-header">
            <div>
                <h3 class="admin-page-title">Payment Details</h3>
                <p class="admin-page-copy">Placeholder details page for payment ID: {{ $recordId }}</p>
            </div>
            <a href="{{ route('admin.payments.index') }}" class="admin-btn admin-btn-secondary">Back</a>
        </div>

        <table class="admin-meta-table">
            <tr><th>Payment ID</th><td>{{ $payment->id }}</td></tr>
            <tr><th>Order No</th><td>{{ $payment->order?->order_no ?: '-' }}</td></tr>
            <tr><th>User</th><td>{{ $payment->user?->name ?: '-' }}</td></tr>
            <tr><th>Provider</th><td>{{ $payment->payment_provider }}</td></tr>
            <tr><th>Transaction ID</th><td>{{ $payment->transaction_id ?: '-' }}</td></tr>
            <tr><th>Status</th><td><span class="admin-status-badge admin-status-badge-{{ \Illuminate\Support\Str::slug($payment->status) }}">{{ $payment->status }}</span></td></tr>
            <tr><th>Amount</th><td>${{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}</td></tr>
            <tr><th>Option</th><td>{{ $payment->payment_option ?: '-' }}</td></tr>
        </table>
    </section>
@endsection
