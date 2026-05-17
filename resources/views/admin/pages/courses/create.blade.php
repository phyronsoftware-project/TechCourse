@extends('admin.layouts.app')

@section('title', 'Create Course')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Create Course</h2>
        <p class="admin-section-copy">Create a free or paid course for TechCourse.</p>

        <form action="{{ route('admin.courses.store') }}" method="POST" enctype="multipart/form-data" class="admin-form-grid mt-6">
            @csrf

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Title</label>
                <div class="admin-input-group">
                    <input type="text" name="title" value="{{ old('title') }}" class="admin-input" placeholder="Course title" required>
                    <span class="admin-input-addon">Aa</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Category</label>
                <div class="admin-input-group">
                    <select name="category_id" class="admin-select">
                        <option value="">Select category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <span class="admin-input-addon">CAT</span>
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
                <label>Price</label>
                <div class="admin-input-group">
                    <input type="number" step="0.01" name="price" value="{{ old('price', 0) }}" class="admin-input" required>
                    <span class="admin-input-addon">$</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Currency</label>
                <div class="admin-input-group">
                    <input type="text" name="currency" value="{{ old('currency', 'USD') }}" class="admin-input" required>
                    <span class="admin-input-addon">CUR</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Level</label>
                <div class="admin-input-group">
                    <select name="level" class="admin-select">
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                    <span class="admin-input-addon">LV</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Status</label>
                <div class="admin-input-group">
                    <select name="status" class="admin-select">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                    <span class="admin-input-addon">ST</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Language</label>
                <div class="admin-input-group">
                    <input type="text" name="language" value="{{ old('language', 'Khmer') }}" class="admin-input">
                    <span class="admin-input-addon">LG</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Duration Text</label>
                <div class="admin-input-group">
                    <input type="text" name="duration_text" value="{{ old('duration_text') }}" class="admin-input">
                    <span class="admin-input-addon">TM</span>
                </div>
            </div>

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Short Description</label>
                <textarea name="short_description" rows="3" class="admin-textarea">{{ old('short_description') }}</textarea>
            </div>

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Description</label>
                <textarea name="description" rows="5" class="admin-textarea">{{ old('description') }}</textarea>
            </div>

            <div class="admin-field">
                <label>Thumbnail Upload</label>
                <input type="file" name="thumbnail" accept="image/*" class="admin-input" data-file-input data-file-name="#course-thumbnail-name" data-file-preview="#course-thumbnail-preview">
                <p id="course-thumbnail-name" class="admin-file-meta">No file selected</p>
            </div>

            <div class="admin-field">
                <label>Preview</label>
                <div id="course-thumbnail-preview" class="admin-preview-box">
                    <img class="hidden" alt="Course thumbnail preview">
                    <span data-preview-text>No Image</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Intro Video URL</label>
                <div class="admin-input-group">
                    <input type="text" name="intro_video_url" value="{{ old('intro_video_url') }}" class="admin-input">
                    <span class="admin-input-addon">URL</span>
                </div>
            </div>

            <label class="admin-checkbox">
                <input type="checkbox" name="is_free" value="1" class="rounded border-slate-300">
                Free Course
            </label>

            <label class="admin-checkbox">
                <input type="checkbox" name="is_published" value="1" class="rounded border-slate-300">
                Published
            </label>

            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="admin-btn admin-btn-primary">Save Course</button>
                <a href="{{ route('admin.courses.index') }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
