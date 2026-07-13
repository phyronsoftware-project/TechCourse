<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryFeeController extends Controller
{
    public function index(): View
    {
        $phnomPenh = Province::query()->where('code', 'PP')->first();
        $otherProvinces = Province::query()->where('code', '!=', 'PP')->orderBy('name_en')->get();

        return view('admin.pages.delivery-fees.index', [
            'pageTitle' => 'Delivery Fees',
            'phnomPenh' => $phnomPenh,
            'otherProvinces' => $otherProvinces,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phnom_penh_fee' => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'other_provinces_fee' => ['required', 'numeric', 'min:0', 'max:9999.99'],
        ]);

        // Save both delivery groups together from one admin form.
        Province::query()->where('code', 'PP')->update([
            'delivery_fee' => round((float) $data['phnom_penh_fee'], 2),
        ]);
        Province::query()->where('code', '!=', 'PP')->update([
            'delivery_fee' => round((float) $data['other_provinces_fee'], 2),
        ]);

        return redirect()
            ->route('admin.delivery-fees.index')
            ->with('success', 'All delivery fees updated successfully.');
    }
}
