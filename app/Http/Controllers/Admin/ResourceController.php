<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ResourceController extends Controller
{
    public function index(Course $course): View
    {
        $course->load([
            'resources' => fn ($query) => $query->with('lesson')->orderBy('sort_order')->orderBy('id'),
            'lessons' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
        ]);

        return view('admin.pages.resources.index', [
            'pageTitle' => 'Course Resources',
            'course' => $course,
            'courseId' => $course->id,
            'resources' => $course->resources,
        ]);
    }

    public function create(Course $course): View
    {
        return view('admin.pages.resources.create', [
            'pageTitle' => 'Create Resource',
            'course' => $course->load('lessons'),
            'courseId' => $course->id,
        ]);
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'lesson_id' => ['nullable', Rule::exists('course_lessons', 'id')->where(fn ($query) => $query->where('course_id', $course->id))],
            'title' => ['required', 'string', 'max:255'],
            'file_upload' => [
                'required',
                'file',
                'max:25600',
                function (string $attribute, mixed $file, \Closure $fail): void {
                    if (! $file || ! $file->isValid()) {
                        $fail('The uploaded PDF file is invalid.');
                        return;
                    }

                    $extension = strtolower((string) ($file->getClientOriginalExtension() ?: $file->extension()));
                    $mimeType = strtolower((string) $file->getMimeType());

                    if ($extension !== 'pdf' && ! str_contains($mimeType, 'pdf')) {
                        $fail('Please upload a valid PDF file.');
                    }
                },
            ],
            'is_free' => ['nullable', 'boolean'],
            'is_downloadable' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], [
            'lesson_id.exists' => 'The selected lesson does not belong to this course.',
            'file_upload.required' => 'Please choose a PDF file before saving.',
            'file_upload.max' => 'The PDF file must not be larger than 25 MB.',
        ]);

        $file = $request->file('file_upload');
        $data['course_id'] = $course->id;
        $data['file_path'] = $file->store('resources', 'public');
        $data['file_type'] = strtolower($file->getClientOriginalExtension() ?: 'pdf');
        $data['file_size'] = (int) $file->getSize();
        $data['is_free'] = $request->boolean('is_free');
        $data['is_downloadable'] = $request->boolean('is_downloadable', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        unset($data['file_upload']);

        CourseResource::create($data);

        return redirect()
            ->route('admin.courses.resources.index', $course)
            ->with('success', 'Resource created successfully.');
    }

    public function edit(Course $course, CourseResource $resource): View
    {
        return view('admin.pages.resources.edit', [
            'pageTitle' => 'Edit Resource',
            'course' => $course->load('lessons'),
            'resource' => $resource,
            'courseId' => $course->id,
            'recordId' => $resource->id,
        ]);
    }

    public function update(Request $request, Course $course, CourseResource $resource): RedirectResponse
    {
        $data = $request->validate([
            'lesson_id' => ['nullable', Rule::exists('course_lessons', 'id')->where(fn ($query) => $query->where('course_id', $course->id))],
            'title' => ['required', 'string', 'max:255'],
            'file_upload' => [
                'nullable',
                'file',
                'max:25600',
                function (string $attribute, mixed $file, \Closure $fail): void {
                    if (! $file) {
                        return;
                    }

                    if (! $file->isValid()) {
                        $fail('The uploaded PDF file is invalid.');
                        return;
                    }

                    $extension = strtolower((string) ($file->getClientOriginalExtension() ?: $file->extension()));
                    $mimeType = strtolower((string) $file->getMimeType());

                    if ($extension !== 'pdf' && ! str_contains($mimeType, 'pdf')) {
                        $fail('Please upload a valid PDF file.');
                    }
                },
            ],
            'is_free' => ['nullable', 'boolean'],
            'is_downloadable' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], [
            'lesson_id.exists' => 'The selected lesson does not belong to this course.',
            'file_upload.max' => 'The PDF file must not be larger than 25 MB.',
        ]);

        if ($request->hasFile('file_upload')) {
            if ($resource->file_path && !str_starts_with($resource->file_path, 'http') && !str_starts_with($resource->file_path, 'storage/')) {
                Storage::disk('public')->delete($resource->file_path);
            }

            $file = $request->file('file_upload');
            $data['file_path'] = $file->store('resources', 'public');
            $data['file_type'] = strtolower($file->getClientOriginalExtension() ?: 'pdf');
            $data['file_size'] = (int) $file->getSize();
        }

        $data['is_free'] = $request->boolean('is_free');
        $data['is_downloadable'] = $request->boolean('is_downloadable', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        unset($data['file_upload']);

        $resource->update($data);

        return redirect()
            ->route('admin.courses.resources.index', $course)
            ->with('success', 'Resource updated successfully.');
    }

    public function destroy(Course $course, CourseResource $resource): RedirectResponse
    {
        if ($resource->file_path && !str_starts_with($resource->file_path, 'http') && !str_starts_with($resource->file_path, 'storage/')) {
            Storage::disk('public')->delete($resource->file_path);
        }

        $resource->delete();

        return redirect()
            ->route('admin.courses.resources.index', $course)
            ->with('success', 'Resource deleted successfully.');
    }
}
