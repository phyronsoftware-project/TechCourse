@extends('admin.layouts.app')

@section('title', 'Edit Resource')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Edit Resource</h2>
        <p class="admin-section-copy">Update download settings and PDF metadata for this course.</p>

        <form action="{{ route('admin.courses.resources.update', [$course, $resource]) }}" method="POST" enctype="multipart/form-data" class="admin-form-grid mt-6">
            @csrf
            @method('PUT')

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Title</label>
                <div class="admin-input-group">
                    <input type="text" name="title" value="{{ old('title', $resource->title) }}" class="admin-input" required>
                    <span class="admin-input-addon">Aa</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Lesson</label>
                <div class="admin-input-group">
                    <select name="lesson_id" class="admin-select">
                        <option value="">No lesson</option>
                        @foreach ($course->lessons as $lesson)
                            <option value="{{ $lesson->id }}" @selected((string) old('lesson_id', $resource->lesson_id) === (string) $lesson->id)>{{ $lesson->title }}</option>
                        @endforeach
                    </select>
                    <span class="admin-input-addon">LSN</span>
                </div>
            </div>

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>PDF Upload</label>
                <input type="file" name="file_upload" accept="application/pdf" class="admin-input" data-file-input data-file-name="#resource-file-name">
                <p id="resource-file-name" class="admin-file-meta">{{ $resource->file_path ? basename($resource->file_path) : 'No file selected' }}</p>
            </div>

            <div class="admin-field">
                <label>Current File</label>
                <div class="admin-input-group">
                    <input type="text" value="{{ $resource->file_path ? basename($resource->file_path) : 'No file' }}" class="admin-input" disabled>
                    <span class="admin-input-addon">PDF</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Sort Order</label>
                <div class="admin-input-group">
                    <input type="number" name="sort_order" value="{{ old('sort_order', $resource->sort_order) }}" class="admin-input">
                    <span class="admin-input-addon">#</span>
                </div>
            </div>

            <label class="admin-checkbox">
                <input type="checkbox" name="is_free" value="1" @checked(old('is_free', $resource->is_free)) class="rounded border-slate-300">
                Free Resource
            </label>

            <label class="admin-checkbox">
                <input type="checkbox" name="is_downloadable" value="1" @checked(old('is_downloadable', $resource->is_downloadable)) class="rounded border-slate-300">
                Downloadable
            </label>

            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="admin-btn admin-btn-primary">Update Resource</button>
                <a href="{{ route('admin.courses.resources.index', $course) }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
