@extends('admin.layouts.app')

@section('title', 'Edit Tech Category')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Edit Tech Category</h2>
        <p class="admin-section-copy">Update technology category title, icon, and display order.</p>

        <form action="{{ route('admin.tech-categories.update', $category) }}" method="POST" class="admin-form-grid mt-6">
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
                <label>Icon</label>
                <div class="admin-input-group">
                    <input type="text" name="icon" value="{{ old('icon', $category->icon) }}" class="admin-input">
                    <span class="admin-input-addon">Ic</span>
                </div>
            </div>

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Subtitle</label>
                <textarea name="subtitle" rows="3" class="admin-textarea">{{ old('subtitle', $category->subtitle) }}</textarea>
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
                    <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" class="admin-input" min="0">
                    <span class="admin-input-addon">#</span>
                </div>
            </div>

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Icon Preview</label>
                <div class="admin-preview-box flex items-center justify-center text-3xl text-blue-600">
                    <i class="{{ old('icon', $category->icon) }}"></i>
                </div>
            </div>

            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="admin-btn admin-btn-primary">Update Category</button>
                <a href="{{ route('admin.tech-categories.index') }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
