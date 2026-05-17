@extends('admin.layouts.app')

@section('title', 'Edit Banner')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Edit Banner</h2>
        <p class="admin-section-copy">Update banner content, platform, schedule, and course link.</p>

        <form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data" class="admin-form-grid mt-6">
            @csrf
            @method('PUT')

            <div class="admin-field">
                <label>Platform</label>
                <div class="admin-input-group">
                    <select name="platform" class="admin-select" required>
                        <option value="web" @selected(old('platform', $banner->platform) === 'web')>Web</option>
                        <option value="app" @selected(old('platform', $banner->platform) === 'app')>App</option>
                    </select>
                    <span class="admin-input-addon">PL</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Sort Order</label>
                <div class="admin-input-group">
                    <input type="number" name="sort_order" value="{{ old('sort_order', $banner->sort_order) }}" class="admin-input">
                    <span class="admin-input-addon">#</span>
                </div>
            </div>

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Title</label>
                <div class="admin-input-group">
                    <input type="text" name="title" value="{{ old('title', $banner->title) }}" class="admin-input" required>
                    <span class="admin-input-addon">Aa</span>
                </div>
            </div>

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Subtitle</label>
                <textarea name="subtitle" rows="4" class="admin-textarea">{{ old('subtitle', $banner->subtitle) }}</textarea>
            </div>

            <div class="admin-field">
                <label>Target Course</label>
                <div class="admin-input-group">
                    <select name="target_course_id" class="admin-select">
                        <option value="">No linked course</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}" @selected((string) old('target_course_id', $banner->target_course_id) === (string) $course->id)>{{ $course->title }}</option>
                        @endforeach
                    </select>
                    <span class="admin-input-addon">CRS</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Button Text</label>
                <div class="admin-input-group">
                    <input type="text" name="button_text" value="{{ old('button_text', $banner->button_text) }}" class="admin-input">
                    <span class="admin-input-addon">BTN</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Starts At</label>
                <input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($banner->starts_at)->format('Y-m-d\TH:i')) }}" class="admin-input">
            </div>

            <div class="admin-field">
                <label>Ends At</label>
                <input type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($banner->ends_at)->format('Y-m-d\TH:i')) }}" class="admin-input">
            </div>

            <div class="admin-field">
                <label>Replace Image</label>
                <input type="file" name="image" accept="image/*" class="admin-input">
                <p class="admin-file-meta">Leave empty if you want to keep current image.</p>
            </div>

            <div class="admin-field">
                <label>Current Image</label>
                <div class="admin-preview-box !w-full !max-w-[260px] !justify-start !p-2">
                    @if ($banner->image_url)
                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="h-full w-full rounded object-cover">
                    @else
                        <span data-preview-text>No Image</span>
                    @endif
                </div>
            </div>

            <label class="admin-checkbox">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $banner->is_active)) class="rounded border-slate-300">
                Active banner
            </label>

            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="admin-btn admin-btn-primary">Update Banner</button>
                <a href="{{ route('admin.banners.index', ['platform' => $banner->platform]) }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
