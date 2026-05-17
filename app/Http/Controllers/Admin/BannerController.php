<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BannerController extends Controller
{
    protected string $table = 'banners';

    public function index(Request $request): View
    {
        $setupReady = Schema::hasTable($this->table);
        $platform = $request->string('platform')->toString();
        $status = $request->string('status')->toString();
        $search = $request->string('search')->toString();

        $banners = new LengthAwarePaginator([], 0, 10);

        if ($setupReady) {
            $query = Banner::query()
                ->with('course')
                ->orderBy('platform')
                ->orderBy('sort_order')
                ->latest('id');

            if ($platform !== '') {
                $query->where('platform', $platform);
            }

            if ($status !== '') {
                $query->where('is_active', $status === 'active');
            }

            if ($search !== '') {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('subtitle', 'like', "%{$search}%")
                        ->orWhereHas('course', fn ($courseQuery) => $courseQuery->where('title', 'like', "%{$search}%"));
                });
            }

            $banners = $query->paginate(10)->withQueryString();
        }

        return view('admin.pages.banners.index', [
            'pageTitle' => 'Banner Management',
            'banners' => $banners,
            'filters' => $request->only(['platform', 'status', 'search']),
            'setupReady' => $setupReady,
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.pages.banners.create', [
            'pageTitle' => 'Create Banner',
            'courses' => $this->courses(),
            'defaultPlatform' => $request->string('platform')->toString() ?: 'web',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (!Schema::hasTable($this->table)) {
            return redirect()
                ->route('admin.banners.index')
                ->with('error', 'Banner table is not created yet. Please run the SQL command first.');
        }

        $data = $request->validate($this->rules());
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        Banner::create($data);

        return redirect()
            ->route('admin.banners.index', ['platform' => $data['platform']])
            ->with('success', 'Banner created successfully.');
    }

    public function edit(Banner $banner): View
    {
        return view('admin.pages.banners.edit', [
            'pageTitle' => 'Edit Banner',
            'banner' => $banner,
            'courses' => $this->courses(),
            'recordId' => $banner->id,
        ]);
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $data = $request->validate($this->rules($banner));
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            if ($banner->image && !str_starts_with($banner->image, 'http') && !str_starts_with($banner->image, 'storage/')) {
                Storage::disk('public')->delete($banner->image);
            }

            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);

        return redirect()
            ->route('admin.banners.index', ['platform' => $data['platform']])
            ->with('success', 'Banner updated successfully.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        if ($banner->image && !str_starts_with($banner->image, 'http') && !str_starts_with($banner->image, 'storage/')) {
            Storage::disk('public')->delete($banner->image);
        }

        $platform = $banner->platform;
        $banner->delete();

        return redirect()
            ->route('admin.banners.index', ['platform' => $platform])
            ->with('success', 'Banner deleted successfully.');
    }

    protected function rules(?Banner $banner = null): array
    {
        $courseRules = ['nullable', 'integer'];

        if (Schema::hasTable('courses')) {
            $courseRules[] = Rule::exists('courses', 'id');
        }

        return [
            'platform' => ['required', Rule::in(['web', 'app'])],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string'],
            'target_course_id' => $courseRules,
            'button_text' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'image' => [$banner ? 'nullable' : 'required', 'image', 'max:4096'],
        ];
    }

    protected function courses()
    {
        if (!Schema::hasTable('courses')) {
            return collect();
        }

        return Course::query()->orderBy('title')->get(['id', 'title']);
    }
}
