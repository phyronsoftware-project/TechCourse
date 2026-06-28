@extends('admin.layouts.app')

@section('title', 'Create Shop Category')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Create Shop Category</h2>
        <p class="admin-section-copy">Add a shopping category for product grouping in the shop module.</p>

        <form action="{{ route('admin.shop-categories.store') }}" method="POST" class="admin-form-grid mt-6">
            @csrf

            <div class="admin-field">
                <label>Name</label>
                <div class="admin-input-group">
                    <input type="text" name="name" value="{{ old('name') }}" class="admin-input" placeholder="Enter shop category name" required>
                    <span class="admin-input-addon">Aa</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Slug</label>
                <div class="admin-input-group">
                    <input type="text" name="slug" value="{{ old('slug') }}" class="admin-input" placeholder="shop-category-slug">
                    <span class="admin-input-addon">/</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Status</label>
                <div class="admin-input-group">
                    <select name="status" class="admin-select">
                        <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    </select>
                    <span class="admin-input-addon">ON</span>
                </div>
            </div>

            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="admin-btn admin-btn-primary">Save Category</button>
                <a href="{{ route('admin.shop-categories.index') }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
