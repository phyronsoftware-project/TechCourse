@extends('admin.layouts.app')

@section('title', 'Create Category')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Create Category</h2>
        <p class="admin-section-copy">Add a new course category for TechCourse.</p>

        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="admin-form-grid mt-6">
            @csrf

            <div class="admin-field">
                <label>Name</label>
                <div class="admin-input-group">
                    <input type="text" name="name" value="{{ old('name') }}" class="admin-input" placeholder="Enter category name" required>
                    <span class="admin-input-addon">Aa</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Slug</label>
                <div class="admin-input-group">
                    <input type="text" name="slug" value="{{ old('slug') }}" class="admin-input" placeholder="category-slug">
                    <span class="admin-input-addon">/</span>
                </div>
            </div>

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Description</label>
                <textarea name="description" rows="4" class="admin-textarea">{{ old('description') }}</textarea>
            </div>

            <div class="admin-field">
                <label>Image Upload</label>
                <input type="file" name="image" accept="image/*" class="admin-input" data-file-input data-file-name="#category-image-name" data-file-preview="#category-image-preview">
                <p id="category-image-name" class="admin-file-meta">No file selected</p>
            </div>

            <div class="admin-field">
                <label>Preview</label>
                <div id="category-image-preview" class="admin-preview-box">
                    <img class="hidden" alt="Category preview">
                    <span data-preview-text>No Image</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Status</label>
                <div class="admin-input-group">
                    <select name="status" class="admin-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <span class="admin-input-addon">ON</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Sort Order</label>
                <div class="admin-input-group">
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="admin-input">
                    <span class="admin-input-addon">#</span>
                </div>
            </div>

            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="admin-btn admin-btn-primary">Save Category</button>
                <a href="{{ route('admin.categories.index') }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
