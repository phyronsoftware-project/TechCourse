@extends('admin.layouts.app')

@section('title', 'Shop Order Details')

@section('content')
    <style>
        /* Keep order metadata compact on desktop while preserving a readable mobile layout. */
        .shop-order-info-table {
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .shop-order-info-table tbody {
            display: contents;
        }

        .shop-order-info-table tr {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 78px;
            min-width: 0;
            overflow: hidden;
            border: 1px solid #dbe5f0;
            border-radius: 12px;
            background: linear-gradient(135deg, #ffffff 0%, #fbfdff 100%);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.035);
        }

        .shop-order-info-table th,
        .shop-order-info-table td {
            width: auto !important;
            min-width: 0;
            padding: 0 14px;
            overflow-wrap: anywhere;
            border: 0 !important;
            box-sizing: border-box;
            display: flex;
            align-items: center;
        }

        .shop-order-info-table th {
            padding-top: 11px;
            background: transparent !important;
            color: #64748b;
            text-align: left;
            font-size: 12px;
            letter-spacing: 0.03em;
            text-transform: none;
        }

        .shop-order-info-table td {
            padding-top: 5px;
            padding-bottom: 11px;
            color: #1e293b;
            font-size: 15px;
            font-weight: 600;
        }

        @media (max-width: 1100px) {
            .shop-order-info-table {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            .shop-order-info-table {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <style>
        .shop-receipt {
            display: none;
        }

        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }

            body * {
                visibility: hidden !important;
            }

            .shop-receipt,
            .shop-receipt * {
                visibility: visible !important;
            }

            .shop-receipt {
                display: block;
                position: absolute;
                top: 0;
                left: 0;
                width: 72mm;
                padding: 4mm;
                color: #000;
                background: #fff;
                font-family: Arial, sans-serif;
                font-size: 11px;
                line-height: 1.35;
            }

            .shop-receipt-header {
                text-align: center;
            }

            .shop-receipt-header h1 {
                margin: 0 0 2px;
                font-size: 16px;
                font-weight: 700;
            }

            .shop-receipt-header p {
                margin: 0;
            }

            .shop-receipt-divider {
                border-top: 1px dashed #000;
                margin: 8px 0;
            }

            .shop-receipt-meta,
            .shop-receipt-total {
                display: flex;
                justify-content: space-between;
                gap: 8px;
            }

            .shop-receipt-client p {
                margin: 2px 0;
                overflow-wrap: anywhere;
            }

            .shop-receipt-items {
                width: 100%;
                border-collapse: collapse;
            }

            .shop-receipt-items th,
            .shop-receipt-items td {
                padding: 3px 0;
                text-align: right;
                vertical-align: top;
            }

            .shop-receipt-items th:first-child,
            .shop-receipt-items td:first-child {
                width: 42%;
                text-align: left;
                overflow-wrap: anywhere;
            }

            .shop-receipt-items th:nth-child(2),
            .shop-receipt-items td:nth-child(2) {
                width: 12%;
            }

            .shop-receipt-items th:nth-child(3),
            .shop-receipt-items td:nth-child(3) {
                width: 21%;
            }

            .shop-receipt-items th:nth-child(4),
            .shop-receipt-items td:nth-child(4) {
                width: 25%;
            }

            .shop-receipt-total {
                font-size: 13px;
                font-weight: 700;
            }

            .shop-receipt-thanks {
                margin-top: 18px;
                text-align: center;
            }
        }
    </style>

    {{-- Print-only 80mm receipt generated from the delivered shop order. --}}
    <section class="shop-receipt" aria-hidden="true">
        @php
            $receiptQuantity = $order->items->sum('qty');
            $receiptSubtotal = (float) ($order->subtotal_amount ?: ((float) $order->total_amount - (float) $order->delivery_fee));
        @endphp
        <div class="shop-receipt-header">
            <h1>{{ config('app.name', 'TechCourse') }}</h1>
            <p>វិក័យបត្រ / Bill</p>
            <p>Order No: {{ $order->order_no }}</p>
            <p>Date: {{ optional($order->created_at)->format('d/m/Y H:i') ?: '-' }}</p>
        </div>

        <div class="shop-receipt-divider"></div>

        <div class="shop-receipt-client">
            <strong>Client Info</strong>
            <p>Name: {{ $order->user?->name ?: '-' }}</p>
            @if ($order->user?->phone)
                <p>Phone: {{ $order->user->phone }}</p>
            @endif
            @if ($order->user?->email)
                <p>Email: {{ $order->user->email }}</p>
            @endif
            @if ($order->user?->address || $order->user?->city || $order->user?->province)
                <p>Address: {{ collect([$order->user?->address, $order->user?->city, $order->user?->province])->filter()->implode(', ') }}</p>
            @endif
        </div>

        <div class="shop-receipt-divider"></div>

        <table class="shop-receipt-items">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Amt</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td>{{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="shop-receipt-divider"></div>

        <div class="shop-receipt-meta">
            <span>Items Qty</span>
            <span>{{ $receiptQuantity }}</span>
        </div>
        <div class="shop-receipt-meta">
            <span>Subtotal</span>
            <span>{{ number_format($receiptSubtotal, 2) }} {{ $order->currency }}</span>
        </div>

        @if ((float) $order->delivery_fee > 0)
            <div class="shop-receipt-meta">
                <span>Delivery</span>
                <span>{{ number_format((float) $order->delivery_fee, 2) }} {{ $order->currency }}</span>
            </div>
        @endif

        <div class="shop-receipt-total">
            <span>TOTAL</span>
            <span>{{ number_format((float) $order->total_amount, 2) }} {{ $order->currency }}</span>
        </div>

        <div class="shop-receipt-thanks">Thank you</div>
    </section>

    <div class="admin-page-header">
        <div>
            <h3 class="admin-page-title">Shop Order Details</h3>
            <p class="admin-page-copy">Customer, item and Bakong payment information for this shop order.</p>
        </div>
        <div class="flex items-center gap-3">
            @if ($order->status === 'paid' && $order->delivery_status !== 'delivered')
                <form action="{{ route('admin.shop-orders.delivered', $order) }}" method="POST" onsubmit="return confirm('Mark this order as delivered?');">
                    @csrf
                    <button type="submit" class="admin-btn admin-btn-primary">Mark as Delivered</button>
                </form>
            @else
                <button type="button" class="admin-btn admin-btn-secondary" disabled title="Only paid orders can be delivered">Mark as Delivered</button>
            @endif
            <a href="{{ route('admin.shop-orders.index') }}" class="admin-btn admin-btn-secondary">Back</a>
        </div>
    </div>

    <section class="admin-form-card p-6">
        <h4 class="admin-section-title">Order Information</h4>
        <table class="admin-meta-table shop-order-info-table">
                <tr><th>Order ID</th><td>{{ $order->id }}</td></tr>
                <tr><th>Order No</th><td>{{ $order->order_no }}</td></tr>
                <tr><th>Customer</th><td>{{ $order->user?->name ?: '-' }}</td></tr>
                <tr><th>Province</th><td>{{ $order->province_name ?: '-' }}</td></tr>
                <tr><th>Email</th><td>{{ $order->user?->email ?: '-' }}</td></tr>
                <tr><th>Phone</th><td>{{ $order->user?->phone ?: '-' }}</td></tr>
                <tr><th>Address</th><td>{{ $order->user?->address ?: '-' }}</td></tr>
                <tr><th>City</th><td>{{ $order->user?->city ?: '-' }}</td></tr>
                <tr><th>Province</th><td>{{ $order->user?->province ?: '-' }}</td></tr>
                <tr><th>Postal Code</th><td>{{ $order->user?->postal_code ?: '-' }}</td></tr>
                <tr><th>Order Status</th><td><span class="admin-status-badge admin-status-badge-{{ \Illuminate\Support\Str::slug($order->status) }}">{{ $order->status }}</span></td></tr>
                <tr><th>Delivery Status</th><td><span class="admin-status-badge admin-status-badge-{{ \Illuminate\Support\Str::slug($order->delivery_status ?: 'pending') }}">{{ $order->delivery_status ?: 'pending' }}</span></td></tr>
                <tr><th>Subtotal</th><td>${{ number_format((float) ($order->subtotal_amount ?: $order->total_amount), 2) }} {{ $order->currency }}</td></tr>
                <tr><th>Delivery Fee</th><td>${{ number_format((float) $order->delivery_fee, 2) }} {{ $order->currency }}</td></tr>
                <tr><th>Total</th><td>${{ number_format((float) $order->total_amount, 2) }} {{ $order->currency }}</td></tr>
                <tr><th>Payment Method</th><td>{{ $order->payment_method ?: '-' }}</td></tr>
                <tr><th>Created At</th><td>{{ optional($order->created_at)->format('Y-m-d H:i:s') ?: '-' }}</td></tr>
                <tr><th>Paid At</th><td>{{ optional($order->paid_at)->format('Y-m-d H:i:s') ?: '-' }}</td></tr>
                <tr><th>Delivered At</th><td>{{ optional($order->delivered_at)->format('Y-m-d H:i:s') ?: '-' }}</td></tr>
        </table>
    </section>

    <section class="admin-form-card p-6 mt-5">
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
                                    @if ($item->image_url ?: $item->product?->image_url)
                                        <img src="{{ $item->image_url ?: $item->product?->image_url }}" alt="{{ $item->product_name }}" class="admin-thumb">
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

    @if (session('print_receipt'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Open the receipt printer dialog only after Mark as Delivered succeeds.
                window.setTimeout(() => window.print(), 300);
            });
        </script>
    @endif
@endsection
