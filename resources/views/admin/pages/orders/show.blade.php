@extends('admin.layouts.app')

@section('title', 'Order Detail')

@section('content')
    <div class="admin-page-header">
        <div>
            <h3 class="admin-page-title">Order Detail</h3>
            <p class="admin-page-copy">Placeholder detail page for order ID: {{ $recordId }}</p>
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
                <tr><th>Total Amount</th><td>${{ number_format((float) $order->total_amount, 2) }}</td></tr>
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
    </div>
@endsection
