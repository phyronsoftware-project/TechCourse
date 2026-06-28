<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ShopCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = ShopCategory::query()
            ->withCount('products')
            ->latest('id');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('admin.pages.shop-categories.index', [
            'pageTitle' => 'Shop Categories',
            'categories' => $query->paginate(10)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.shop-categories.create', [
            'pageTitle' => 'Create Shop Category',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:shop_categories,slug'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $data['slug'] = filled($data['slug'] ?? null)
            ? Str::slug((string) $data['slug'])
            : Str::slug((string) $data['name']);

        ShopCategory::create($data);

        return redirect()
            ->route('admin.shop-categories.index')
            ->with('success', 'Shop category created successfully.');
    }

    public function edit(ShopCategory $shopCategory): View
    {
        return view('admin.pages.shop-categories.edit', [
            'pageTitle' => 'Edit Shop Category',
            'category' => $shopCategory,
            'recordId' => $shopCategory->id,
        ]);
    }

    public function update(Request $request, ShopCategory $shopCategory): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:shop_categories,slug,' . $shopCategory->id],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $data['slug'] = filled($data['slug'] ?? null)
            ? Str::slug((string) $data['slug'])
            : Str::slug((string) $data['name']);

        $shopCategory->update($data);

        return redirect()
            ->route('admin.shop-categories.index')
            ->with('success', 'Shop category updated successfully.');
    }

    public function destroy(ShopCategory $shopCategory): RedirectResponse
    {
        $shopCategory->delete();

        return redirect()
            ->route('admin.shop-categories.index')
            ->with('success', 'Shop category deleted successfully.');
    }
}
