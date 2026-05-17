@extends('admin.layouts.app')

@section('title', 'Course Details')

@section('content')
    <section class="space-y-4">
        <div class="admin-form-card p-6">
            <h3 class="admin-page-title">Course Details</h3>
            <p class="admin-page-copy">{{ $course->title }} • {{ $course->category?->name ?: 'No category' }}</p>

            <table class="admin-meta-table mt-5">
                <tr><th>ID</th><td>{{ $course->id }}</td></tr>
                <tr>
                    <th>Thumbnail</th>
                    <td>
                        @if ($course->thumbnail_url)
                            <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="admin-thumb">
                        @else
                            -
                        @endif
                    </td>
                </tr>
                <tr><th>Slug</th><td>{{ $course->slug }}</td></tr>
                <tr><th>Price</th><td>${{ number_format((float) $course->price, 2) }} {{ $course->currency }}</td></tr>
                <tr><th>Level</th><td>{{ ucfirst($course->level) }}</td></tr>
                <tr><th>Status</th><td>{{ $course->status }}</td></tr>
                <tr><th>Published</th><td>{{ $course->is_published ? 'Yes' : 'No' }}</td></tr>
                <tr><th>Total Lessons</th><td>{{ $course->lessons->count() }}</td></tr>
                <tr><th>Total Resources</th><td>{{ $course->resources->count() }}</td></tr>
            </table>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.courses.edit', $course) }}" class="admin-btn admin-btn-primary">Edit Course</a>
            <a href="{{ route('admin.courses.lessons.index', $recordId) }}" class="admin-btn admin-btn-secondary">View Lessons</a>
            <a href="{{ route('admin.courses.resources.index', $recordId) }}" class="admin-btn admin-btn-secondary">View Resources</a>
        </div>
    </section>
@endsection
