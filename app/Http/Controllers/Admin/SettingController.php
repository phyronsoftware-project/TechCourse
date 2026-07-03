<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AbaPayWayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(AbaPayWayService $abaPaywayService)
    {
        return view('admin.pages.settings.index', [
            'pageTitle' => 'Settings',
            'abaSummary' => $abaPaywayService->summary(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Settings structure is ready. Save logic is not implemented yet.');
    }
}
