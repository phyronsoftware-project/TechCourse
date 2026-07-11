@extends('admin.layouts.app')

@section('title', 'Shop Order Details')

@section('content')
    <div class="admin-page-header">
        <div>
            <h3 class="admin-page-title">Shop Order Details</h3>
            <p class="admin-page-copy">Customer, item and Bakong payment information for this shop order.</p>
        </div>
        <a href="{{ route('admin.shop-orders.index') }}" class="admin-btn admin-btn-secondary">Back</a>
    </div>

    <div class="admin-detail-grid">
        <section class="admin-form-card p-6">
            <table class="admin-meta-table">
                <tr><th>Order ID</th><td>{{ $order->id }}</td></tr>
                <tr><th>Order No</th><td>{{ $order->order_no }}</td></tr>
                <tr><th>Customer</th><td>{{ $order->user?->name ?: '-' }}</td></tr>
                <tr><th>Email</th><td>{{ $order->user?->email ?: '-' }}</td></tr>
                <tr><th>Phone</th><td>{{ $order->user?->phone ?: '-' }}</td></tr>
                <tr><th>Address</th><td>{{ $order->user?->address ?: '-' }}</td></tr>
                <tr><th>City</th><td>{{ $order->user?->city ?: '-' }}</td></tr>
                <tr><th>Province</th><td>{{ $order->user?->province ?: '-' }}</td></tr>
                <tr><th>Postal Code</th><td>{{ $order->user?->postal_code ?: '-' }}</td></tr>
                <tr><th>Order Status</th><td><span class="admin-status-badge admin-status-badge-{{ \Illuminate\Support\Str::slug($order->status) }}">{{ $order->status }}</span></td></tr>
                <tr><th>Total</th><td>${{ number_format((float) $order->total_amount, 2) }} {{ $order->currency }}</td></tr>
                <tr><th>Payment Method</th><td>{{ $order->payment_method ?: '-' }}</td></tr>
                <tr><th>Created At</th><td>{{ optional($order->created_at)->format('Y-m-d H:i:s') ?: '-' }}</td></tr>
                <tr><th>Paid At</th><td>{{ optional($order->paid_at)->format('Y-m-d H:i:s') ?: '-' }}</td></tr>
            </table>
        </section>

        <section class="admin-form-card p-6">
            <h4 class="admin-section-title">Order Items</h4>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr><th>Image</th><th>Product</th><th>Quantity</th><th>Unit Price</th><th>Line Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($order->items as $item)
                            <tr>
                                <td>
                                    @if ($item->product?->image_url)
                                        <img src="{{ $item->product->image_url }}" alt="{{ $item->product_name }}" class="admin-thumb">
                                    @else
                                        <span class="admin-thumb-empty">No Img</span>
                                    @endif
                                </td>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->qty }}</td>
                                <td>${{ number_format((float) $item->unit_price, 2) }}</td>
                                <td>${{ number_format((float) $item->line_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="admin-empty">No order items found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <section class="admin-index-panel admin-index-panel-table mt-5">
        <div class="admin-page-header">
            <div>
                <h3 class="admin-page-title">Payment Attempts</h3>
                <p class="admin-page-copy">Pending, success, expired and failed payment records for this order.</p>
            </div>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr><th>ID</th><th>Payment No</th><th>Amount</th><th>Provider</th><th>Status</th><th>Transaction Hash</th><th>Expired At</th><th>Paid At</th></tr>
                </thead>
                <tbody>
                    @forelse ($order->payments as $payment)
                        <tr>
                            <td>{{ $payment->id }}</td>
                            <td>{{ $payment->payment_no ?: '-' }}</td>
                            <td>${{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}</td>
                            <td>{{ $payment->payment_provider ?: '-' }}</td>
                            <td><span class="admin-status-badge admin-status-badge-{{ \Illuminate\Support\Str::slug($payment->status) }}">{{ $payment->status }}</span></td>
                            <td class="break-all">{{ $payment->transaction_hash ?: '-' }}</td>
                            <td>{{ optional($payment->expired_at)->format('Y-m-d H:i:s') ?: '-' }}</td>
                            <td>{{ optional($payment->paid_at)->format('Y-m-d H:i:s') ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="admin-empty">No payment attempts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
