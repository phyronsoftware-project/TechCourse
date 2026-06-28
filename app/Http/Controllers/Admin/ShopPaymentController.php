<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ShopPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $payments = collect();
        $shopReady = Schema::hasTable('shop_payments');

        if ($shopReady) {
            $query = DB::table('shop_payments')
                ->leftJoin('users', 'shop_payments.user_id', '=', 'users.id')
                ->leftJoin('shop_orders', 'shop_payments.shop_order_id', '=', 'shop_orders.id')
                ->select([
                    'shop_payments.id',
                    'shop_payments.transaction_id',
                    'shop_payments.amount',
                    'shop_payments.currency',
                    'shop_payments.status',
                    'shop_payments.payment_provider',
                    'shop_payments.paid_at',
                    'shop_orders.order_no',
                    'users.name as user_name',
                ])
                ->orderByDesc('shop_payments.id');

            if ($request->filled('search')) {
                $search = $request->string('search');
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('shop_payments.transaction_id', 'like', "%{$search}%")
                        ->orWhere('shop_orders.order_no', 'like', "%{$search}%")
                        ->orWhere('users.name', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('shop_payments.status', $request->string('status'));
            }

            $payments = $query->paginate(10)->withQueryString();
        }

        return view('admin.pages.shop-payments.index', [
            'pageTitle' => 'Shop Payments',
            'payments' => $payments,
            'shopReady' => $shopReady,
        ]);
    }
}
