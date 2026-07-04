<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TechCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TechCategoryController extends Controller
{
    public function index(Request $request): View
    {
        // Keep admin category listing searchable for quick content setup.
        $query = TechCategory::query()
            ->withCount('technologies')
            ->latest('id');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('subtitle', 'like', "%{$search}%")
                    ->orWhere('icon', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('admin.pages.tech-categories.index', [
            'pageTitle' => 'Tech Categories',
            'categories' => $query->paginate(10)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.tech-categories.create', [
            'pageTitle' => 'Create Tech Category',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:120'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['icon'] = $data['icon'] ?: 'fa-solid fa-microchip';

        TechCategory::create($data);

        return redirect()
            ->route('admin.tech-categories.index')
            ->with('success', 'Tech category created successfully.');
    }

    public function edit(TechCategory $techCategory): View
    {
        return view('admin.pages.tech-categories.edit', [
            'pageTitle' => 'Edit Tech Category',
            'category' => $techCategory,
            'recordId' => $techCategory->id,
        ]);
    }

    public function update(Request $request, TechCategory $techCategory): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:120'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['icon'] = $data['icon'] ?: 'fa-solid fa-microchip';

        $techCategory->update($data);

        return redirect()
            ->route('admin.tech-categories.index')
            ->with('success', 'Tech category updated successfully.');
    }

    public function destroy(TechCategory $techCategory): RedirectResponse
    {
        $techCategory->delete();

        return redirect()
            ->route('admin.tech-categories.index')
            ->with('success', 'Tech category deleted successfully.');
    }
}
