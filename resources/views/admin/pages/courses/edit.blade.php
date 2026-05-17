@extends('admin.layouts.app')

@section('title', 'Edit Course')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Edit Course</h2>
        <p class="admin-section-copy">Update the content, pricing, and publishing status of this course.</p>

        <form action="{{ route('admin.courses.update', $course) }}" method="POST" enctype="multipart/form-data" class="admin-form-grid mt-6">
            @csrf
            @method('PUT')

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Title</label>
                <div class="admin-input-group">
                    <input type="text" name="title" value="{{ old('title', $course->title) }}" class="admin-input" required>
                    <span class="admin-input-addon">Aa</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Category</label>
                <div class="admin-input-group">
                    <select name="category_id" class="admin-select">
                        <option value="">Select category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id', $course->category_id) === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <span class="admin-input-addon">CAT</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Slug</label>
                <div class="admin-input-group">
                    <input type="text" name="slug" value="{{ old('slug', $course->slug) }}" class="admin-input">
                    <span class="admin-input-addon">/</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Price</label>
                <div class="admin-input-group">
                    <input type="number" step="0.01" name="price" value="{{ old('price', $course->price) }}" class="admin-input" required>
                    <span class="admin-input-addon">$</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Currency</label>
                <div class="admin-input-group">
                    <input type="text" name="currency" value="{{ old('currency', $course->currency) }}" class="admin-input" required>
                    <span class="admin-input-addon">CUR</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Level</label>
                <div class="admin-input-group">
                    <select name="level" class="admin-select">
                        @foreach (['beginner', 'intermediate', 'advanced'] as $level)
                            <option value="{{ $level }}" @selected(old('level', $course->level) === $level)>{{ ucfirst($level) }}</option>
                        @endforeach
                    </select>
                    <span class="admin-input-addon">LV</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Status</label>
                <div class="admin-input-group">
                    <select name="status" class="admin-select">
                        @foreach (['draft', 'published', 'archived'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $course->status) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <span class="admin-input-addon">ST</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Language</label>
                <div class="admin-input-group">
                    <input type="text" name="language" value="{{ old('language', $course->language) }}" class="admin-input">
                    <span class="admin-input-addon">LG</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Duration Text</label>
                <div class="admin-input-group">
                    <input type="text" name="duration_text" value="{{ old('duration_text', $course->duration_text) }}" class="admin-input">
                    <span class="admin-input-addon">TM</span>
                </div>
            </div>

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Short Description</label>
                <textarea name="short_description" rows="3" class="admin-textarea">{{ old('short_description', $course->short_description) }}</textarea>
            </div>

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Description</label>
                <textarea name="description" rows="5" class="admin-textarea">{{ old('description', $course->description) }}</textarea>
            </div>

            <div class="admin-field">
                <label>Thumbnail Upload</label>
                <input type="file" name="thumbnail" accept="image/*" class="admin-input" data-file-input data-file-name="#course-thumbnail-name" data-file-preview="#course-thumbnail-preview">
                <p id="course-thumbnail-name" class="admin-file-meta">{{ $course->thumbnail ? basename($course->thumbnail) : 'No file selected' }}</p>
            </div>

            <div class="admin-field">
                <label>Current Thumbnail</label>
                <div id="course-thumbnail-preview" class="admin-preview-box">
                    @if ($course->thumbnail_url)
                        <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}">
                        <span data-preview-text class="hidden">No Image</span>
                    @else
                        <img class="hidden" alt="Course thumbnail preview">
                        <span data-preview-text>No Image</span>
                    @endif
                </div>
            </div>

            <div class="admin-field">
                <label>Intro Video URL</label>
                <div class="admin-input-group">
                    <input type="text" name="intro_video_url" value="{{ old('intro_video_url', $course->intro_video_url) }}" class="admin-input">
                    <span class="admin-input-addon">URL</span>
                </div>
            </div>

            <label class="admin-checkbox">
                <input type="checkbox" name="is_free" value="1" @checked(old('is_free', $course->is_free)) class="rounded border-slate-300">
                Free Course
            </label>

            <label class="admin-checkbox">
                <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $course->is_published)) class="rounded border-slate-300">
                Published
            </label>

            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="admin-btn admin-btn-primary">Update Course</button>
                <a href="{{ route('admin.courses.show', $course) }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
