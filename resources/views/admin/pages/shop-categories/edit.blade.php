@extends('admin.layouts.app')

@section('title', 'Edit Shop Category')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Edit Shop Category</h2>
        <p class="admin-section-copy">Update shopping category details carefully.</p>

        <form action="{{ route('admin.shop-categories.update', $category) }}" method="POST" class="admin-form-grid mt-6">
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

            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="admin-btn admin-btn-primary">Update Category</button>
                <a href="{{ route('admin.shop-categories.index') }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
