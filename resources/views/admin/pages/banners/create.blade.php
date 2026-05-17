@extends('admin.layouts.app')

@section('title', 'Create Banner')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Create Banner</h2>
        <p class="admin-section-copy">Create a web or app banner and connect it to a course for later click-through flow.</p>

        <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="admin-form-grid mt-6">
            @csrf

            <div class="admin-field">
                <label>Platform</label>
                <div class="admin-input-group">
                    <select name="platform" class="admin-select" required>
                        <option value="web" @selected(old('platform', $defaultPlatform) === 'web')>Web</option>
                        <option value="app" @selected(old('platform', $defaultPlatform) === 'app')>App</option>
                    </select>
                    <span class="admin-input-addon">PL</span>
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
                <label>Title</label>
                <div class="admin-input-group">
                    <input type="text" name="title" value="{{ old('title') }}" class="admin-input" placeholder="Banner title" required>
                    <span class="admin-input-addon">Aa</span>
                </div>
            </div>

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Subtitle</label>
                <textarea name="subtitle" rows="4" class="admin-textarea">{{ old('subtitle') }}</textarea>
            </div>

            <div class="admin-field">
                <label>Target Course</label>
                <div class="admin-input-group">
                    <select name="target_course_id" class="admin-select">
                        <option value="">No linked course</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}" @selected((string) old('target_course_id') === (string) $course->id)>{{ $course->title }}</option>
                        @endforeach
                    </select>
                    <span class="admin-input-addon">CRS</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Button Text</label>
                <div class="admin-input-group">
                    <input type="text" name="button_text" value="{{ old('button_text', 'View Course') }}" class="admin-input" placeholder="View Course">
                    <span class="admin-input-addon">BTN</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Starts At</label>
                <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="admin-input">
            </div>

            <div class="admin-field">
                <label>Ends At</label>
                <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" class="admin-input">
            </div>

            <div class="admin-field">
                <label>Banner Image</label>
                <input type="file" name="image" accept="image/*" class="admin-input">
                <p class="admin-file-meta">Recommended wide image for web or app promotion banner.</p>
            </div>

            <label class="admin-checkbox">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-slate-300">
                Active banner
            </label>

            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="admin-btn admin-btn-primary">Save Banner</button>
                <a href="{{ route('admin.banners.index', ['platform' => $defaultPlatform]) }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
