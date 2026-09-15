<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Course::query()
            ->with(['category', 'creator'])
            ->latest('id');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('admin.pages.courses.index', [
            'pageTitle' => 'Courses',
            'courses' => $query->paginate(10)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.courses.create', [
            'pageTitle' => 'Create Course',
            'categories' => CourseCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'exists:course_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:courses,slug'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'max:4096'],
            'intro_video_url' => ['nullable', 'string', 'max:500'],
            'level' => ['required', 'in:beginner,intermediate,advanced'],
            'language' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'is_free' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'status' => ['required', 'in:draft,published,archived'],
            'duration_text' => ['nullable', 'string', 'max:100'],
        ]);

        $data['slug'] = filled($data['slug'] ?? null) ? Str::slug($data['slug']) : Str::slug($data['title']);
        $data['created_by'] = $request->user()?->id;
        $data['language'] = $data['language'] ?? 'Khmer';
        $data['is_free'] = $request->boolean('is_free');
        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = $data['is_published'] ? now() : null;
        $data['thumbnail'] = $request->file('thumbnail')?->store('courses', 'public');

        if ($data['is_free']) {
            $data['price'] = 0;
        }

        $course = Course::create($data);

        return redirect()
            ->route('admin.courses.show', $course)
            ->with('success', 'Course created successfully.');
    }

    public function show(Course $course): View
    {
        $course->load(['category', 'creator', 'lessons', 'resources', 'reviews.user']);

        return view('admin.pages.courses.show', [
            'pageTitle' => 'Course Details',
            'course' => $course,
            'recordId' => $course->id,
        ]);
    }

    public function edit(Course $course): View
    {
        return view('admin.pages.courses.edit', [
            'pageTitle' => 'Edit Course',
            'course' => $course,
            'recordId' => $course->id,
            'categories' => CourseCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'exists:course_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:courses,slug,' . $course->id],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'max:4096'],
            'intro_video_url' => ['nullable', 'string', 'max:500'],
            'level' => ['required', 'in:beginner,intermediate,advanced'],
            'language' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'is_free' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'status' => ['required', 'in:draft,published,archived'],
            'duration_text' => ['nullable', 'string', 'max:100'],
        ]);

        $data['slug'] = filled($data['slug'] ?? null) ? Str::slug($data['slug']) : Str::slug($data['title']);
        $data['language'] = $data['language'] ?? 'Khmer';
        $data['is_free'] = $request->boolean('is_free');
        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = $data['is_published'] ? ($course->published_at ?? now()) : null;

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail && !str_starts_with($course->thumbnail, 'http') && !str_starts_with($course->thumbnail, 'storage/')) {
                Storage::disk('public')->delete($course->thumbnail);
            }

            $data['thumbnail'] = $request->file('thumbnail')->store('courses', 'public');
        } else {
            unset($data['thumbnail']);
        }

        if ($data['is_free']) {
            $data['price'] = 0;
        }

        $course->update($data);

        return redirect()
            ->route('admin.courses.show', $course)
            ->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        try {
            $thumbnail = DB::transaction(fn () => $this->deleteCourse($course));

            if ($thumbnail) {
                Storage::disk('public')->delete($thumbnail);
            }
        } catch (Throwable) {
            return back()->with('error', 'Unable to delete this course right now.');
        }

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Course deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['required', 'integer', 'distinct', 'exists:courses,id'],
        ]);

        $courses = Course::query()->whereIn('id', $data['ids'])->get();

        try {
            // Delete all selected database rows atomically before removing stored thumbnails.
            $thumbnails = DB::transaction(function () use ($courses) {
                return $courses
                    ->map(fn (Course $course) => $this->deleteCourse($course))
                    ->filter()
                    ->values()
                    ->all();
            });

            if ($thumbnails !== []) {
                Storage::disk('public')->delete($thumbnails);
            }
        } catch (Throwable) {
            return back()->with('error', 'Unable to delete the selected courses right now.');
        }

        return back()->with('success', $courses->count() . ' courses deleted successfully.');
    }

    protected function deleteCourse(Course $course): ?string
    {
        // Keep single and bulk deletion on the same related-record cleanup flow.
        DB::table('lesson_progress')->where('course_id', $course->id)->delete();
        DB::table('course_favorites')->where('course_id', $course->id)->delete();
        DB::table('course_reviews')->where('course_id', $course->id)->delete();
        DB::table('course_enrollments')->where('course_id', $course->id)->delete();
        DB::table('order_items')->where('course_id', $course->id)->delete();
        DB::table('course_resources')->where('course_id', $course->id)->delete();
        DB::table('course_lessons')->where('course_id', $course->id)->delete();

        $thumbnail = $course->thumbnail
            && ! str_starts_with($course->thumbnail, 'http')
            && ! str_starts_with($course->thumbnail, 'storage/')
                ? $course->thumbnail
                : null;

        $course->delete();

        return $thumbnail;
    }
}
