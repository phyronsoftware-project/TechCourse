@extends('admin.layouts.app')

@section('title', 'Create Lesson')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Create Lesson</h2>
        <p class="admin-section-copy">Create lesson for course: {{ $course->title }}</p>

        <form action="{{ route('admin.courses.lessons.store', $course) }}" method="POST" enctype="multipart/form-data" class="admin-form-grid mt-6" data-lesson-video-form>
            @csrf

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Title</label>
                <div class="admin-input-group">
                    <input type="text" name="title" value="{{ old('title') }}" class="admin-input" required>
                    <span class="admin-input-addon">Aa</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Slug</label>
                <div class="admin-input-group">
                    <input type="text" name="slug" value="{{ old('slug') }}" class="admin-input">
                    <span class="admin-input-addon">/</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Video Type</label>
                <div class="admin-input-group">
                    <select name="video_type" class="admin-select" data-video-type-select>
                        @foreach (['upload', 'youtube', 'vimeo', 'mux', 'external'] as $type)
                            <option value="{{ $type }}" @selected(old('video_type', 'upload') === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                    <span class="admin-input-addon">VID</span>
                </div>
            </div>

            {{-- Show URL input only for non-upload video types. --}}
            <div class="admin-field" data-video-url-field>
                <label>Video URL</label>
                <div class="admin-input-group">
                    <input type="text" name="video_url" value="{{ old('video_url') }}" class="admin-input" data-video-url-input>
                    <span class="admin-input-addon">URL</span>
                </div>
            </div>

            {{-- Show file input only when upload video type is selected. --}}
            <div class="admin-field" data-video-file-field>
                <label>Video File</label>
                <div class="admin-input-group" style="align-items: stretch;">
                    <input type="file" name="video_file_upload" accept="video/mp4,video/quicktime,video/webm,video/x-m4v,video/x-msvideo,video/x-matroska" class="admin-input" data-video-file-input>
                    <span class="admin-input-addon">MP4</span>
                </div>
                <p class="admin-section-copy mt-2 text-xs text-slate-500">Choose local video when Video Type is set to upload.</p>
            </div>

            <div class="admin-field">
                <label>Duration Seconds</label>
                <div class="admin-input-group">
                    <input type="number" name="duration_seconds" value="{{ old('duration_seconds', 0) }}" class="admin-input">
                    <span class="admin-input-addon">SEC</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Sort Order</label>
                <div class="admin-input-group">
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="admin-input">
                    <span class="admin-input-addon">#</span>
                </div>
            </div>

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Description</label>
                <textarea name="description" rows="4" class="admin-textarea">{{ old('description') }}</textarea>
            </div>

            <label class="admin-checkbox">
                <input type="checkbox" name="is_preview" value="1" class="rounded border-slate-300">
                Preview Lesson
            </label>

            <label class="admin-checkbox">
                <input type="checkbox" name="is_published" value="1" checked class="rounded border-slate-300">
                Published
            </label>

            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="admin-btn admin-btn-primary">Save Lesson</button>
                <a href="{{ route('admin.courses.lessons.index', $course) }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>

    <script>
        (() => {
            const form = document.querySelector('[data-lesson-video-form]');

            if (!form) {
                return;
            }

            const videoTypeSelect = form.querySelector('[data-video-type-select]');
            const videoUrlField = form.querySelector('[data-video-url-field]');
            const videoFileField = form.querySelector('[data-video-file-field]');
            const videoUrlInput = form.querySelector('[data-video-url-input]');
            const videoFileInput = form.querySelector('[data-video-file-input]');

            // Match the visible lesson input to the selected video type.
            const syncLessonVideoFields = () => {
                const selectedType = videoTypeSelect?.value || 'upload';
                const isUpload = selectedType === 'upload';

                if (videoUrlField) {
                    videoUrlField.hidden = isUpload;
                }

                if (videoFileField) {
                    videoFileField.hidden = !isUpload;
                }

                if (videoUrlInput) {
                    videoUrlInput.disabled = isUpload;
                }

                if (videoFileInput) {
                    videoFileInput.disabled = !isUpload;
                }
            };

            syncLessonVideoFields();
            videoTypeSelect?.addEventListener('change', syncLessonVideoFields);
        })();
    </script>
@endsection
