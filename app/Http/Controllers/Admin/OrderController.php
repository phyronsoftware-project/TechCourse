<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::query()
            ->with(['user', 'items.course', 'payments'])
            ->latest('id');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('order_no', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('admin.pages.orders.index', [
            'pageTitle' => 'Orders',
            'orders' => $query->paginate(10)->withQueryString(),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'items.course', 'payments']);

        return view('admin.pages.orders.show', [
            'pageTitle' => 'Order Details',
            'order' => $order,
            'recordId' => $order->id,
        ]);
    }

    public function destroy(Order $order): RedirectResponse
    {
        DB::transaction(function () use ($order) {
            DB::table('payments')->where('order_id', $order->id)->delete();
            DB::table('course_enrollments')->where('order_id', $order->id)->delete();
            DB::table('order_items')->where('order_id', $order->id)->delete();
            $order->delete();
        });

        return redirect()
            ->route('admin.orders.index')
            ->with('success', 'Order deleted successfully.');
    }
}
