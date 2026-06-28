@extends('admin.layouts.app')

@section('title', 'Shop Orders')

@section('content')
    @if (! $shopReady)
        <section class="admin-form-card p-6">
            <h2 class="admin-section-title">Shop Orders</h2>
            <p class="admin-section-copy">Shopping order table is not created yet, so this page is separated from course orders and waiting for shop order SQL setup.</p>
            <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm leading-7 text-slate-600">
                Required tables for shop only flow:
                <br>`shop_orders`
                <br>`shop_order_items`
                <br>`shop_payments`
            </div>
        </section>
    @else
        <section class="admin-filter-card p-6">
            <form method="GET" action="{{ route('admin.shop-orders.index') }}" class="space-y-4">
                <div class="admin-filter-grid">
                    <div class="admin-field admin-filter-field-wide">
                        <label for="search">Search</label>
                        <div class="admin-input-group">
                            <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Order no or customer..." class="admin-input">
                            <span class="admin-input-addon">Q</span>
                        </div>
                    </div>

                    <div class="admin-field">
                        <label for="status">Status</label>
                        <div class="admin-input-group">
                            <select id="status" name="status" class="admin-select">
                                <option value="">All</option>
                                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                                <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                                <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                                <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                            </select>
                            <span class="admin-input-addon">ST</span>
                        </div>
                    </div>

                    <div class="admin-filter-actions">
                        <button type="submit" class="admin-btn admin-btn-primary">Filter</button>
                        <a href="{{ route('admin.shop-orders.index') }}" class="admin-btn admin-btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </section>

        <section class="admin-index-panel admin-index-panel-table">
            <div class="admin-page-header">
                <div>
                    <h3 class="admin-page-title">Shop Orders</h3>
                    <p class="admin-page-copy">Only shopping product orders are shown here. Course orders are not mixed.</p>
                </div>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Order No</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td>{{ $order->order_no }}</td>
                                <td>{{ $order->user_name ?: '-' }}</td>
                                <td>${{ number_format((float) $order->total_amount, 2) }} {{ $order->currency }}</td>
                                <td>{{ $order->payment_method ?: '-' }}</td>
                                <td><span class="admin-status-badge admin-status-badge-{{ \Illuminate\Support\Str::slug($order->status) }}">{{ $order->status }}</span></td>
                                <td>{{ \Illuminate\Support\Carbon::parse($order->created_at)->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="admin-empty">No shop order rows loaded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5">
                {{ $orders->links() }}
            </div>
        </section>
    @endif
@endsection
