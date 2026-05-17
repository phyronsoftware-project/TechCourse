@extends('admin.layouts.app')

@section('title', 'Edit Lesson')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Edit Lesson</h2>
        <p class="admin-section-copy">Update video lesson information for {{ $course->title }}.</p>

        <form action="{{ route('admin.courses.lessons.update', [$course, $lesson]) }}" method="POST" class="admin-form-grid mt-6">
            @csrf
            @method('PUT')

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Title</label>
                <div class="admin-input-group">
                    <input type="text" name="title" value="{{ old('title', $lesson->title) }}" class="admin-input" required>
                    <span class="admin-input-addon">Aa</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Slug</label>
                <div class="admin-input-group">
                    <input type="text" name="slug" value="{{ old('slug', $lesson->slug) }}" class="admin-input">
                    <span class="admin-input-addon">/</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Video Type</label>
                <div class="admin-input-group">
                    <select name="video_type" class="admin-select">
                        @foreach (['upload', 'youtube', 'vimeo', 'mux', 'external'] as $type)
                            <option value="{{ $type }}" @selected(old('video_type', $lesson->video_type) === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                    <span class="admin-input-addon">VID</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Video URL</label>
                <div class="admin-input-group">
                    <input type="text" name="video_url" value="{{ old('video_url', $lesson->video_url) }}" class="admin-input">
                    <span class="admin-input-addon">URL</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Video File</label>
                <div class="admin-input-group">
                    <input type="text" name="video_file" value="{{ old('video_file', $lesson->video_file) }}" class="admin-input">
                    <span class="admin-input-addon">MP4</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Duration Seconds</label>
                <div class="admin-input-group">
                    <input type="number" name="duration_seconds" value="{{ old('duration_seconds', $lesson->duration_seconds) }}" class="admin-input">
                    <span class="admin-input-addon">SEC</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Sort Order</label>
                <div class="admin-input-group">
                    <input type="number" name="sort_order" value="{{ old('sort_order', $lesson->sort_order) }}" class="admin-input">
                    <span class="admin-input-addon">#</span>
                </div>
            </div>

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Description</label>
                <textarea name="description" rows="4" class="admin-textarea">{{ old('description', $lesson->description) }}</textarea>
            </div>

            <label class="admin-checkbox">
                <input type="checkbox" name="is_preview" value="1" @checked(old('is_preview', $lesson->is_preview)) class="rounded border-slate-300">
                Preview Lesson
            </label>

            <label class="admin-checkbox">
                <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $lesson->is_published)) class="rounded border-slate-300">
                Published
            </label>

            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="admin-btn admin-btn-primary">Update Lesson</button>
                <a href="{{ route('admin.courses.lessons.index', $course) }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
