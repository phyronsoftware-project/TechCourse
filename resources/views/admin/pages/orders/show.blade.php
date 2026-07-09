@extends('admin.layouts.app')

@section('title', 'Order Detail')

@section('content')
    <div class="admin-page-header">
        <div>
            <h3 class="admin-page-title">Order Detail</h3>
            <p class="admin-page-copy">Backend order record with related payment history.</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="admin-btn admin-btn-secondary">Back</a>
    </div>

    <div class="admin-detail-grid">
        <div class="admin-form-card p-6">
            <table class="admin-meta-table">
                <tr><th>Order Number</th><td>{{ $order->order_no }}</td></tr>
                <tr><th>Customer</th><td>{{ $order->user?->name ?: '-' }}</td></tr>
                <tr><th>Email</th><td>{{ $order->user?->email ?: '-' }}</td></tr>
                <tr><th>Phone</th><td>{{ $order->user?->phone ?: '-' }}</td></tr>
                <tr><th>Status</th><td><span class="admin-status-badge admin-status-badge-{{ \Illuminate\Support\Str::slug($order->status) }}">{{ $order->status }}</span></td></tr>
                <tr><th>Payment Method</th><td>{{ $order->payment_method ?: '-' }}</td></tr>
                <tr><th>Total Amount</th><td>${{ number_format((float) $order->total_amount, 2) }} {{ $order->currency }}</td></tr>
                <tr><th>Paid At</th><td>{{ optional($order->paid_at)->format('Y-m-d H:i:s') ?: '-' }}</td></tr>
            </table>
        </div>

        <div class="admin-form-card p-6">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>SKU</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($order->items as $item)
                            <tr>
                                <td>{{ $item->course_title }}</td>
                                <td>{{ $item->course?->slug ?: '-' }}</td>
                                <td>1</td>
                                <td>${{ number_format((float) $item->price, 2) }}</td>
                                <td>${{ number_format((float) $item->price, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="admin-empty">No items found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-form-card p-6">
            <h4 class="admin-section-title">Payments</h4>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Payment No</th>
                            <th>Provider</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Paid At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($order->payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_no ?: '-' }}</td>
                                <td>{{ $payment->payment_provider }}</td>
                                <td>{{ $payment->status }}</td>
                                <td>${{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}</td>
                                <td>{{ optional($payment->paid_at)->format('Y-m-d H:i:s') ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="admin-empty">No payments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-form-card p-6">
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
                        @forelse ($order->paymentHistories as $history)
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
        </div>
    </div>
@endsection
