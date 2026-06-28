@extends('admin.layouts.app')

@section('title', 'Shop Payments')

@section('content')
    @if (! $shopReady)
        <section class="admin-form-card p-6">
            <h2 class="admin-section-title">Shop Payments</h2>
            <p class="admin-section-copy">Shopping payment table is not created yet, so this page is separated from course payments and waiting for shop payment SQL setup.</p>
            <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm leading-7 text-slate-600">
                Required tables for shop only flow:
                <br>`shop_orders`
                <br>`shop_order_items`
                <br>`shop_payments`
            </div>
        </section>
    @else
        <section class="admin-filter-card p-6">
            <form method="GET" action="{{ route('admin.shop-payments.index') }}" class="space-y-4">
                <div class="admin-filter-grid">
                    <div class="admin-field admin-filter-field-wide">
                        <label for="search">Search</label>
                        <div class="admin-input-group">
                            <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Transaction, order no, customer..." class="admin-input">
                            <span class="admin-input-addon">Q</span>
                        </div>
                    </div>

                    <div class="admin-field">
                        <label for="status">Status</label>
                        <div class="admin-input-group">
                            <select id="status" name="status" class="admin-select">
                                <option value="">All</option>
                                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                                <option value="success" @selected(request('status') === 'success')>Success</option>
                                <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                            </select>
                            <span class="admin-input-addon">ST</span>
                        </div>
                    </div>

                    <div class="admin-filter-actions">
                        <button type="submit" class="admin-btn admin-btn-primary">Filter</button>
                        <a href="{{ route('admin.shop-payments.index') }}" class="admin-btn admin-btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </section>

        <section class="admin-index-panel admin-index-panel-table">
            <div class="admin-page-header">
                <div>
                    <h3 class="admin-page-title">Shop Payments</h3>
                    <p class="admin-page-copy">Only shopping product payments are shown here. Course payments are not mixed.</p>
                </div>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Transaction ID</th>
                            <th>Order No</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Provider</th>
                            <th>Status</th>
                            <th>Paid At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td>{{ $payment->id }}</td>
                                <td>{{ $payment->transaction_id ?: '-' }}</td>
                                <td>{{ $payment->order_no ?: '-' }}</td>
                                <td>{{ $payment->user_name ?: '-' }}</td>
                                <td>${{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}</td>
                                <td>{{ $payment->payment_provider ?: '-' }}</td>
                                <td><span class="admin-status-badge admin-status-badge-{{ \Illuminate\Support\Str::slug($payment->status) }}">{{ $payment->status }}</span></td>
                                <td>{{ $payment->paid_at ? \Illuminate\Support\Carbon::parse($payment->paid_at)->format('d M Y H:i') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="admin-empty">No shop payment rows loaded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5">
                {{ $payments->links() }}
            </div>
        </section>
    @endif
@endsection
