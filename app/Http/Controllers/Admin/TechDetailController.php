<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TechCategory;
use App\Models\TechDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TechDetailController extends Controller
{
    public function index(Request $request): View
    {
        // Load technology rows with category filter support for admin usage.
        $query = TechDetail::query()
            ->with('category')
            ->latest('id');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('website_url', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        return view('admin.pages.tech-details.index', [
            'pageTitle' => 'Tech Detail',
            'technologies' => $query->paginate(10)->withQueryString(),
            'categories' => TechCategory::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.tech-details.create', [
            'pageTitle' => 'Create Tech Detail',
            'categories' => TechCategory::query()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateTechnology($request);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        TechDetail::create($data);

        return redirect()
            ->route('admin.tech-details.index')
            ->with('success', 'Tech detail created successfully.');
    }

    public function edit(TechDetail $techDetail): View
    {
        return view('admin.pages.tech-details.edit', [
            'pageTitle' => 'Edit Tech Detail',
            'technology' => $techDetail,
            'recordId' => $techDetail->id,
            'categories' => TechCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, TechDetail $techDetail): RedirectResponse
    {
        $data = $this->validateTechnology($request);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $techDetail->update($data);

        return redirect()
            ->route('admin.tech-details.index')
            ->with('success', 'Tech detail updated successfully.');
    }

    public function destroy(TechDetail $techDetail): RedirectResponse
    {
        $techDetail->delete();

        return redirect()
            ->route('admin.tech-details.index')
            ->with('success', 'Tech detail deleted successfully.');
    }

    protected function validateTechnology(Request $request): array
    {
        // Match admin inputs with the new technology SQL structure.
        return $request->validate([
            'category_id' => ['required', 'exists:tech_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'detail' => ['nullable', 'string'],
            'website_url' => ['nullable', 'url', 'max:500'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
