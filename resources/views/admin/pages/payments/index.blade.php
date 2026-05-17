@extends('admin.layouts.app')

@section('title', 'Payments')

@section('content')
    <section class="admin-filter-card p-6">
        <form method="GET" action="{{ route('admin.payments.index') }}" class="space-y-4">
            <div class="admin-filter-grid">
                <div class="admin-field admin-filter-field-wide">
                    <label for="search">Search</label>
                    <div class="admin-input-group">
                        <input id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search by order number, customer name, email" class="admin-input">
                        <span class="admin-input-addon">Q</span>
                    </div>
                </div>

                <div class="admin-field">
                    <label for="status">Payment Status</label>
                    <div class="admin-input-group">
                        <select id="status" name="status" class="admin-select">
                            <option value="">All</option>
                            @foreach (['initiated', 'pending', 'success', 'failed', 'cancelled'] as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        <span class="admin-input-addon">ST</span>
                    </div>
                </div>

                <div class="admin-field">
                    <label for="order_status">Order Status</label>
                    <div class="admin-input-group">
                        <select id="order_status" name="order_status" class="admin-select">
                            <option value="">All</option>
                            @foreach (['pending', 'paid', 'failed', 'cancelled', 'expired'] as $orderStatus)
                                <option value="{{ $orderStatus }}" @selected(($filters['order_status'] ?? '') === $orderStatus)>{{ ucfirst($orderStatus) }}</option>
                            @endforeach
                        </select>
                        <span class="admin-input-addon">ORD</span>
                    </div>
                </div>

                <div class="admin-filter-actions">
                    <button type="submit" class="admin-btn admin-btn-primary">Filter</button>
                    <a href="{{ route('admin.payments.index') }}" class="admin-btn admin-btn-secondary">Reset</a>
                </div>
            </div>
        </form>
    </section>

    <section class="admin-index-panel admin-index-panel-table">
        <div class="admin-page-header">
            <div>
                <h3 class="admin-page-title">Payment Management</h3>
                <p class="admin-page-copy">Review provider status and payment lifecycle.</p>
            </div>
            <span class="admin-chip">ABA later</span>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Order No</th>
                        <th>User</th>
                        <th>Provider</th>
                        <th>Transaction ID</th>
                        <th>Amount</th>
                        <th>Currency</th>
                        <th>Status</th>
                        <th>Paid At</th>
                        <th>Created At</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>{{ $payment->id }}</td>
                            <td>{{ $payment->order?->order_no ?: '-' }}</td>
                            <td>{{ $payment->user?->name ?: '-' }}</td>
                            <td>{{ $payment->payment_provider }}</td>
                            <td>{{ $payment->transaction_id ?: '-' }}</td>
                            <td>${{ number_format((float) $payment->amount, 2) }}</td>
                            <td>{{ $payment->currency }}</td>
                            <td><span class="admin-status-badge admin-status-badge-{{ \Illuminate\Support\Str::slug($payment->status) }}">{{ $payment->status }}</span></td>
                            <td>{{ optional($payment->paid_at)->format('Y-m-d H:i') ?: '-' }}</td>
                            <td>{{ optional($payment->created_at)->format('Y-m-d H:i') ?: '-' }}</td>
                            <td>
                                <details class="admin-action-list">
                                    <summary class="admin-action-trigger" title="Payment actions">
                                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <circle cx="12" cy="5" r="1.75" />
                                            <circle cx="12" cy="12" r="1.75" />
                                            <circle cx="12" cy="19" r="1.75" />
                                        </svg>
                                    </summary>

                                    <div class="admin-action-menu">
                                        <a href="{{ route('admin.payments.show', $payment) }}" class="admin-action-link" title="View payment">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12Z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                            <span>View payment</span>
                                        </a>
                                        <form action="{{ route('admin.payments.destroy', $payment) }}" method="POST" onsubmit="return confirm('Delete this payment?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-action-link admin-action-link-danger text-left" title="Delete payment">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M3 6h18" />
                                                    <path d="M8 6V4h8v2" />
                                                    <path d="M19 6l-1 14H6L5 6" />
                                                </svg>
                                                <span>Delete payment</span>
                                            </button>
                                        </form>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="admin-empty">No payment rows loaded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $payments->links() }}
        </div>
    </section>
@endsection
