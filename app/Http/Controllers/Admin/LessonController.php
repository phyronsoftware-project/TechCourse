<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseLesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function index(Course $course): View
    {
        $course->load(['lessons' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')]);

        return view('admin.pages.lessons.index', [
            'pageTitle' => 'Lessons',
            'course' => $course,
            'courseId' => $course->id,
            'lessons' => $course->lessons,
        ]);
    }

    public function create(Course $course): View
    {
        return view('admin.pages.lessons.create', [
            'pageTitle' => 'Create Lesson',
            'course' => $course,
            'courseId' => $course->id,
        ]);
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:course_lessons,slug'],
            'description' => ['nullable', 'string'],
            'video_type' => ['required', 'in:upload,youtube,vimeo,mux,external'],
            'video_url' => ['nullable', 'string', 'max:700'],
            'video_file' => ['nullable', 'string', 'max:700'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'is_preview' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['course_id'] = $course->id;
        $data['slug'] = filled($data['slug'] ?? null) ? Str::slug($data['slug']) : Str::slug($data['title']);
        $data['is_preview'] = $request->boolean('is_preview');
        $data['is_published'] = $request->boolean('is_published', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        CourseLesson::create($data);

        return redirect()
            ->route('admin.courses.lessons.index', $course)
            ->with('success', 'Lesson created successfully.');
    }

    public function edit(Course $course, CourseLesson $lesson): View
    {
        return view('admin.pages.lessons.edit', [
            'pageTitle' => 'Edit Lesson',
            'course' => $course,
            'lesson' => $lesson,
            'courseId' => $course->id,
            'recordId' => $lesson->id,
        ]);
    }

    public function update(Request $request, Course $course, CourseLesson $lesson): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:course_lessons,slug,' . $lesson->id],
            'description' => ['nullable', 'string'],
            'video_type' => ['required', 'in:upload,youtube,vimeo,mux,external'],
            'video_url' => ['nullable', 'string', 'max:700'],
            'video_file' => ['nullable', 'string', 'max:700'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'is_preview' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['slug'] = filled($data['slug'] ?? null) ? Str::slug($data['slug']) : Str::slug($data['title']);
        $data['is_preview'] = $request->boolean('is_preview');
        $data['is_published'] = $request->boolean('is_published', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $lesson->update($data);

        return redirect()
            ->route('admin.courses.lessons.index', $course)
            ->with('success', 'Lesson updated successfully.');
    }

    public function destroy(Course $course, CourseLesson $lesson): RedirectResponse
    {
        DB::table('lesson_progress')->where('lesson_id', $lesson->id)->delete();
        DB::table('course_resources')->where('lesson_id', $lesson->id)->update(['lesson_id' => null]);

        $lesson->delete();

        return redirect()
            ->route('admin.courses.lessons.index', $course)
            ->with('success', 'Lesson deleted successfully.');
    }
}
