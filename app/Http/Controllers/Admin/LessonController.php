<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseLesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        // Validate lesson media fields and support local video uploads.
        $data = $this->validateLessonData($request);

        $data['course_id'] = $course->id;
        $data['slug'] = filled($data['slug'] ?? null) ? Str::slug($data['slug']) : Str::slug($data['title']);
        $data['is_preview'] = $request->boolean('is_preview');
        $data['is_published'] = $request->boolean('is_published', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data = $this->syncLessonVideoMedia($request, $data);

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
        // Validate lesson media fields and support local video uploads.
        $data = $this->validateLessonData($request, $lesson);

        $data['slug'] = filled($data['slug'] ?? null) ? Str::slug($data['slug']) : Str::slug($data['title']);
        $data['is_preview'] = $request->boolean('is_preview');
        $data['is_published'] = $request->boolean('is_published', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data = $this->syncLessonVideoMedia($request, $data, $lesson);

        $lesson->update($data);

        return redirect()
            ->route('admin.courses.lessons.index', $course)
            ->with('success', 'Lesson updated successfully.');
    }

    public function destroy(Course $course, CourseLesson $lesson): RedirectResponse
    {
        if ($lesson->video_file && ! str_starts_with($lesson->video_file, 'http') && ! str_starts_with($lesson->video_file, 'storage/')) {
            Storage::disk('public')->delete($lesson->video_file);
        }

        DB::table('lesson_progress')->where('lesson_id', $lesson->id)->delete();
        DB::table('course_resources')->where('lesson_id', $lesson->id)->update(['lesson_id' => null]);

        $lesson->delete();

        return redirect()
            ->route('admin.courses.lessons.index', $course)
            ->with('success', 'Lesson deleted successfully.');
    }

    protected function validateLessonData(Request $request, ?CourseLesson $lesson = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:course_lessons,slug,' . ($lesson?->id ?? 'NULL')],
            'description' => ['nullable', 'string'],
            'video_type' => ['required', 'in:upload,youtube,vimeo,mux,external'],
            'video_url' => [
                \Illuminate\Validation\Rule::requiredIf(fn () => $request->input('video_type') !== 'upload'),
                'nullable',
                'string',
                'max:700',
            ],
            'video_file_upload' => [
                \Illuminate\Validation\Rule::requiredIf(fn () => $request->input('video_type') === 'upload' && ! $lesson?->video_file),
                'nullable',
                'file',
                'max:256000',
                function (string $attribute, mixed $file, \Closure $fail): void {
                    if (! $file) {
                        return;
                    }

                    if (! $file->isValid()) {
                        $fail('The uploaded video file is invalid.');
                        return;
                    }

                    $extension = strtolower((string) ($file->getClientOriginalExtension() ?: $file->extension()));
                    $mimeType = strtolower((string) $file->getMimeType());
                    $allowedExtensions = ['mp4', 'mov', 'm4v', 'webm', 'avi', 'mkv'];
                    $looksLikeVideo = str_starts_with($mimeType, 'video/');

                    if (! in_array($extension, $allowedExtensions, true) && ! $looksLikeVideo) {
                        $fail('Please upload a valid video file.');
                    }
                },
            ],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'is_preview' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], [
            'video_file_upload.max' => 'The uploaded video must not be larger than 250 MB.',
        ]);
    }

    protected function syncLessonVideoMedia(Request $request, array $data, ?CourseLesson $lesson = null): array
    {
        // Save only the field that matches the selected video type.
        if (($data['video_type'] ?? null) === 'upload') {
            if ($request->hasFile('video_file_upload')) {
                if ($lesson?->video_file && ! str_starts_with($lesson->video_file, 'http') && ! str_starts_with($lesson->video_file, 'storage/')) {
                    Storage::disk('public')->delete($lesson->video_file);
                }

                $data['video_file'] = $request->file('video_file_upload')->store('lesson-videos', 'public');
            } elseif ($lesson?->video_file) {
                $data['video_file'] = $lesson->video_file;
            } else {
                $data['video_file'] = null;
            }

            $data['video_url'] = null;
        } else {
            if ($lesson?->video_file && ! str_starts_with($lesson->video_file, 'http') && ! str_starts_with($lesson->video_file, 'storage/')) {
                Storage::disk('public')->delete($lesson->video_file);
            }

            $data['video_file'] = null;
        }

        unset($data['video_file_upload']);

        return $data;
    }
}
