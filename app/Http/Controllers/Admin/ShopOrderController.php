<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopOrder;
use App\Services\ShopBakongPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ShopOrderController extends Controller
{
    public function show(ShopOrder $shopOrder): View
    {
        $shopOrder->load([
            'user',
            'items.product',
            'payments' => fn ($query) => $query->latest('id'),
        ]);

        return view('admin.pages.shop-orders.show', [
            'pageTitle' => 'Shop Order Details',
            'order' => $shopOrder,
        ]);
    }

    public function markDelivered(ShopOrder $shopOrder): RedirectResponse
    {
        if ($shopOrder->status !== 'paid') {
            return back()->with('error', 'Only paid orders can be marked as delivered.');
        }

        if ($shopOrder->delivery_status === 'delivered') {
            return back()->with('error', 'This order is already marked as delivered.');
        }

        $payload = ['delivery_status' => 'delivered'];
        if (Schema::hasColumn('shop_orders', 'delivered_at')) {
            $payload['delivered_at'] = now();
        }

        $shopOrder->forceFill($payload)->save();

        return back()
            ->with('success', 'Shop order marked as delivered.')
            ->with('print_receipt', true);
    }

    public function index(Request $request, ShopBakongPaymentService $paymentService): View
    {
        $orders = collect();
        $shopReady = Schema::hasTable('shop_orders');

        if ($shopReady) {
            $paymentService->syncExpiredPendingPayments();

            $query = DB::table('shop_orders')
                ->leftJoin('users', 'shop_orders.user_id', '=', 'users.id')
                ->select([
                    'shop_orders.id',
                    'shop_orders.order_no',
                    'shop_orders.total_amount',
                    'shop_orders.currency',
                    'shop_orders.status',
                    'shop_orders.delivery_status',
                    'shop_orders.payment_method',
                    'shop_orders.created_at',
                    'users.name as user_name',
                ])
                ->orderByDesc('shop_orders.id');

            if ($request->filled('search')) {
                $search = $request->string('search');
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('shop_orders.order_no', 'like', "%{$search}%")
                        ->orWhere('users.name', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('shop_orders.status', $request->string('status'));
            }

            $orders = $query->paginate(10)->withQueryString();
        }

        return view('admin.pages.shop-orders.index', [
            'pageTitle' => 'Shop Orders',
            'orders' => $orders,
            'shopReady' => $shopReady,
        ]);
    }
}
