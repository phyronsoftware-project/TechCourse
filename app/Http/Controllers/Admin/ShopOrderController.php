<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ShopOrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = collect();
        $shopReady = Schema::hasTable('shop_orders');

        if ($shopReady) {
            $query = DB::table('shop_orders')
                ->leftJoin('users', 'shop_orders.user_id', '=', 'users.id')
                ->select([
                    'shop_orders.id',
                    'shop_orders.order_no',
                    'shop_orders.total_amount',
                    'shop_orders.currency',
                    'shop_orders.status',
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
