<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseCategory;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = CourseCategory::query()->withCount('courses')->latest('id');

        if ($request->filled('search') && Schema::hasColumn('course_categories', 'name')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && Schema::hasColumn('course_categories', 'status')) {
            $query->where('status', $request->string('status'));
        }

        return view('admin.pages.categories.index', [
            'pageTitle' => 'Categories',
            'categories' => $query->paginate(10)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.categories.create', [
            'pageTitle' => 'Create Category',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:course_categories,slug'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:4096'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['slug'] = filled($data['slug'] ?? null) ? Str::slug($data['slug']) : Str::slug($data['name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['image'] = $request->file('image')?->store('categories', 'public');

        CourseCategory::create($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(CourseCategory $category): View
    {
        return view('admin.pages.categories.edit', [
            'pageTitle' => 'Edit Category',
            'category' => $category,
            'recordId' => $category->id,
        ]);
    }

    public function update(Request $request, CourseCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:course_categories,slug,' . $category->id],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:4096'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['slug'] = filled($data['slug'] ?? null) ? Str::slug($data['slug']) : Str::slug($data['name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($request->hasFile('image')) {
            if ($category->image && !str_starts_with($category->image, 'http') && !str_starts_with($category->image, 'storage/')) {
                Storage::disk('public')->delete($category->image);
            }

            $data['image'] = $request->file('image')->store('categories', 'public');
        } else {
            unset($data['image']);
        }

        $category->update($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(CourseCategory $category): RedirectResponse
    {
        try {
            DB::transaction(function () use ($category) {
                Course::query()
                    ->where('category_id', $category->id)
                    ->get()
                    ->each(function (Course $course): void {
                        DB::table('lesson_progress')->where('course_id', $course->id)->delete();
                        DB::table('course_favorites')->where('course_id', $course->id)->delete();
                        DB::table('course_reviews')->where('course_id', $course->id)->delete();
                        DB::table('course_enrollments')->where('course_id', $course->id)->delete();
                        DB::table('order_items')->where('course_id', $course->id)->delete();
                        DB::table('course_resources')->where('course_id', $course->id)->delete();
                        DB::table('course_lessons')->where('course_id', $course->id)->delete();

                        if ($course->thumbnail && !str_starts_with($course->thumbnail, 'http') && !str_starts_with($course->thumbnail, 'storage/')) {
                            Storage::disk('public')->delete($course->thumbnail);
                        }

                        $course->delete();
                    });

                if ($category->image && !str_starts_with($category->image, 'http') && !str_starts_with($category->image, 'storage/')) {
                    Storage::disk('public')->delete($category->image);
                }

                $category->delete();
            });
        } catch (Throwable) {
            return back()->with('error', 'Unable to delete this category and its courses right now.');
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category and related courses deleted successfully.');
    }
}
