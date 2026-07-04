@extends('admin.layouts.app')

@section('title', 'Create Tech Category')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Create Tech Category</h2>
        <p class="admin-section-copy">Add a technology category for the new technology page.</p>

        <form action="{{ route('admin.tech-categories.store') }}" method="POST" class="admin-form-grid mt-6">
            @csrf

            <div class="admin-field">
                <label>Name</label>
                <div class="admin-input-group">
                    <input type="text" name="name" value="{{ old('name') }}" class="admin-input" placeholder="Frontend, Backend, AI..." required>
                    <span class="admin-input-addon">Aa</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Icon</label>
                <div class="admin-input-group">
                    <input type="text" name="icon" value="{{ old('icon', 'fa-solid fa-microchip') }}" class="admin-input" placeholder="fa-solid fa-microchip">
                    <span class="admin-input-addon">Ic</span>
                </div>
            </div>

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Subtitle</label>
                <textarea name="subtitle" rows="3" class="admin-textarea" placeholder="Short detail below category title">{{ old('subtitle') }}</textarea>
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

            <div class="admin-field">
                <label>Sort Order</label>
                <div class="admin-input-group">
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="admin-input" min="0">
                    <span class="admin-input-addon">#</span>
                </div>
            </div>

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Icon Preview</label>
                <div class="admin-preview-box flex items-center justify-center text-3xl text-blue-600">
                    <i class="{{ old('icon', 'fa-solid fa-microchip') }}"></i>
                </div>
            </div>

            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="admin-btn admin-btn-primary">Save Category</button>
                <a href="{{ route('admin.tech-categories.index') }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
