<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TechCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class TechCategoryController extends Controller
{
    public function index(Request $request): View
    {
        if (!Schema::hasTable('tech_categories')) {
            return view('admin.pages.tech-categories.index', [
                'pageTitle' => 'Tech Categories',
                'categories' => $this->emptyPaginator($request),
                'tableMissing' => true,
            ]);
        }

        // Keep admin category listing searchable for quick content setup.
        $query = TechCategory::query()->latest('id');

        if (Schema::hasTable('tech_details')) {
            $query->withCount('technologies');
        } else {
            $query->selectRaw('tech_categories.*, 0 as technologies_count');
        }

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

    public function create(): View|RedirectResponse
    {
        if (!Schema::hasTable('tech_categories')) {
            return redirect()
                ->route('admin.tech-categories.index')
                ->with('error', 'Tech categories table is missing on this server. Please run the SQL first.');
        }

        return view('admin.pages.tech-categories.create', [
            'pageTitle' => 'Create Tech Category',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (!Schema::hasTable('tech_categories')) {
            return redirect()
                ->route('admin.tech-categories.index')
                ->with('error', 'Tech categories table is missing on this server. Please run the SQL first.');
        }

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

    public function edit(TechCategory $techCategory): View|RedirectResponse
    {
        if (!Schema::hasTable('tech_categories')) {
            return redirect()
                ->route('admin.tech-categories.index')
                ->with('error', 'Tech categories table is missing on this server. Please run the SQL first.');
        }

        return view('admin.pages.tech-categories.edit', [
            'pageTitle' => 'Edit Tech Category',
            'category' => $techCategory,
            'recordId' => $techCategory->id,
        ]);
    }

    public function update(Request $request, TechCategory $techCategory): RedirectResponse
    {
        if (!Schema::hasTable('tech_categories')) {
            return redirect()
                ->route('admin.tech-categories.index')
                ->with('error', 'Tech categories table is missing on this server. Please run the SQL first.');
        }

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
        if (!Schema::hasTable('tech_categories')) {
            return redirect()
                ->route('admin.tech-categories.index')
                ->with('error', 'Tech categories table is missing on this server. Please run the SQL first.');
        }

        $techCategory->delete();

        return redirect()
            ->route('admin.tech-categories.index')
            ->with('success', 'Tech category deleted successfully.');
    }

    protected function emptyPaginator(Request $request): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, 10, (int) $request->integer('page', 1), [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    }
}
