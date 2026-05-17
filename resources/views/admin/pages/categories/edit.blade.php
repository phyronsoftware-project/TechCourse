@extends('admin.layouts.app')

@section('title', 'Edit Category')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Edit Category</h2>
        <p class="admin-section-copy">Update category data and display order.</p>

        <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data" class="admin-form-grid mt-6">
            @csrf
            @method('PUT')

            <div class="admin-field">
                <label>Name</label>
                <div class="admin-input-group">
                    <input type="text" name="name" value="{{ old('name', $category->name) }}" class="admin-input" required>
                    <span class="admin-input-addon">Aa</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Slug</label>
                <div class="admin-input-group">
                    <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" class="admin-input">
                    <span class="admin-input-addon">/</span>
                </div>
            </div>

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Description</label>
                <textarea name="description" rows="4" class="admin-textarea">{{ old('description', $category->description) }}</textarea>
            </div>

            <div class="admin-field">
                <label>Image Upload</label>
                <input type="file" name="image" accept="image/*" class="admin-input" data-file-input data-file-name="#category-image-name" data-file-preview="#category-image-preview">
                <p id="category-image-name" class="admin-file-meta">{{ $category->image ? basename($category->image) : 'No file selected' }}</p>
            </div>

            <div class="admin-field">
                <label>Status</label>
                <div class="admin-input-group">
                    <select name="status" class="admin-select">
                        <option value="active" @selected(old('status', $category->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $category->status) === 'inactive')>Inactive</option>
                    </select>
                    <span class="admin-input-addon">ON</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Sort Order</label>
                <div class="admin-input-group">
                    <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" class="admin-input">
                    <span class="admin-input-addon">#</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Preview</label>
                <div id="category-image-preview" class="admin-preview-box">
                    @if ($category->image_url)
                        <img src="{{ $category->image_url }}" alt="{{ $category->name }}">
                        <span data-preview-text class="hidden">No Image</span>
                    @else
                        <img class="hidden" alt="Category preview">
                        <span data-preview-text>No Image</span>
                    @endif
                </div>
            </div>

            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="admin-btn admin-btn-primary">Update Category</button>
                <a href="{{ route('admin.categories.index') }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
