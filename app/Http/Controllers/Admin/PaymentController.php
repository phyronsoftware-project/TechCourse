<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Payment::query()
            ->with(['order', 'user'])
            ->latest('id');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('transaction_id', 'like', "%{$search}%")
                    ->orWhereHas('order', fn ($orderQuery) => $orderQuery->where('order_no', 'like', "%{$search}%"))
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('order_status')) {
            $orderStatus = $request->string('order_status');
            $query->whereHas('order', fn ($orderQuery) => $orderQuery->where('status', $orderStatus));
        }

        return view('admin.pages.payments.index', [
            'pageTitle' => 'Payments',
            'payments' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search', 'status', 'order_status']),
        ]);
    }

    public function show(Payment $payment): View
    {
        $payment->load(['order', 'user']);

        return view('admin.pages.payments.show', [
            'pageTitle' => 'Payment Details',
            'payment' => $payment,
            'recordId' => $payment->id,
        ]);
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $payment->delete();

        return redirect()
            ->route('admin.payments.index')
            ->with('success', 'Payment deleted successfully.');
    }
}
