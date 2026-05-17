@extends('admin.layouts.app')

@section('title', 'Create Resource')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Create Resource</h2>
        <p class="admin-section-copy">Attach PDF or downloadable resource to {{ $course->title }}.</p>

        <form action="{{ route('admin.courses.resources.store', $course) }}" method="POST" enctype="multipart/form-data" class="admin-form-grid mt-6">
            @csrf

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Title</label>
                <div class="admin-input-group">
                    <input type="text" name="title" value="{{ old('title') }}" class="admin-input" required>
                    <span class="admin-input-addon">Aa</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Lesson</label>
                <div class="admin-input-group">
                    <select name="lesson_id" class="admin-select">
                        <option value="">No lesson</option>
                        @foreach ($course->lessons as $lesson)
                            <option value="{{ $lesson->id }}" @selected((string) old('lesson_id') === (string) $lesson->id)>{{ $lesson->title }}</option>
                        @endforeach
                    </select>
                    <span class="admin-input-addon">LSN</span>
                </div>
            </div>

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>PDF Upload</label>
                <input type="file" name="file_upload" accept="application/pdf" class="admin-input" data-file-input data-file-name="#resource-file-name" required>
                <p id="resource-file-name" class="admin-file-meta">No file selected</p>
                <p class="admin-file-meta">Upload lesson PDF or course PDF resource.</p>
            </div>

            <div class="admin-field">
                <label>Sort Order</label>
                <div class="admin-input-group">
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="admin-input">
                    <span class="admin-input-addon">#</span>
                </div>
            </div>

            <label class="admin-checkbox">
                <input type="checkbox" name="is_free" value="1" class="rounded border-slate-300">
                Free Resource
            </label>

            <label class="admin-checkbox">
                <input type="checkbox" name="is_downloadable" value="1" checked class="rounded border-slate-300">
                Downloadable
            </label>

            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="admin-btn admin-btn-primary">Save Resource</button>
                <a href="{{ route('admin.courses.resources.index', $course) }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
