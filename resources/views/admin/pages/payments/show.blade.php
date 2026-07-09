@extends('admin.layouts.app')

@section('title', 'Payment Details')

@section('content')
    <div class="admin-page-header">
        <div>
            <h3 class="admin-page-title">Payment Details</h3>
            <p class="admin-page-copy">Backend payment record and Bakong history log.</p>
        </div>
        <a href="{{ route('admin.payments.index') }}" class="admin-btn admin-btn-secondary">Back</a>
    </div>

    <div class="admin-detail-grid">
        <section class="admin-form-card p-6">
            <table class="admin-meta-table">
                <tr><th>Payment ID</th><td>{{ $payment->id }}</td></tr>
                <tr><th>Payment No</th><td>{{ $payment->payment_no ?: '-' }}</td></tr>
                <tr><th>Order No</th><td>{{ $payment->order?->order_no ?: '-' }}</td></tr>
                <tr><th>User</th><td>{{ $payment->user?->name ?: '-' }}</td></tr>
                <tr><th>Provider</th><td>{{ $payment->payment_provider }}</td></tr>
                <tr><th>Transaction ID</th><td>{{ $payment->transaction_id ?: '-' }}</td></tr>
                <tr><th>Transaction Hash</th><td>{{ $payment->transaction_hash ?: '-' }}</td></tr>
                <tr><th>Status</th><td><span class="admin-status-badge admin-status-badge-{{ \Illuminate\Support\Str::slug($payment->status) }}">{{ $payment->status }}</span></td></tr>
                <tr><th>Amount</th><td>${{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}</td></tr>
                <tr><th>Option</th><td>{{ $payment->payment_option ?: '-' }}</td></tr>
                <tr><th>Paid At</th><td>{{ optional($payment->paid_at)->format('Y-m-d H:i:s') ?: '-' }}</td></tr>
                <tr><th>Expired At</th><td>{{ optional($payment->expired_at)->format('Y-m-d H:i:s') ?: '-' }}</td></tr>
            </table>
        </section>

        <section class="admin-form-card p-6">
            <h4 class="admin-section-title">Payment History</h4>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Event</th>
                            <th>Payment Status</th>
                            <th>Order Status</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payment->histories as $history)
                            <tr>
                                <td>{{ optional($history->created_at)->format('Y-m-d H:i:s') ?: '-' }}</td>
                                <td>{{ $history->event }}</td>
                                <td>{{ $history->payment_status ?: '-' }}</td>
                                <td>{{ $history->order_status ?: '-' }}</td>
                                <td>{{ $history->message ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="admin-empty">No payment history found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
