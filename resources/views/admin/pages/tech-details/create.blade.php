@extends('admin.layouts.app')

@section('title', 'Create Tech Detail')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Create Tech Detail</h2>
        <p class="admin-section-copy">Add one technology card under a selected category.</p>

        <form action="{{ route('admin.tech-details.store') }}" method="POST" class="admin-form-grid mt-6">
            @csrf

            <div class="admin-field">
                <label>Category</label>
                <div class="admin-input-group">
                    <select name="category_id" class="admin-select" required>
                        <option value="">Select Category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <span class="admin-input-addon">CT</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Name</label>
                <div class="admin-input-group">
                    <input type="text" name="name" value="{{ old('name') }}" class="admin-input" placeholder="Laravel, React, Docker..." required>
                    <span class="admin-input-addon">Aa</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Title</label>
                <div class="admin-input-group">
                    <input type="text" name="title" value="{{ old('title') }}" class="admin-input" placeholder="Short display title">
                    <span class="admin-input-addon">Tt</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Website URL</label>
                <div class="admin-input-group">
                    <input type="url" name="website_url" value="{{ old('website_url') }}" class="admin-input" placeholder="https://example.com">
                    <span class="admin-input-addon">URL</span>
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

            <div class="admin-field">
                <label>Sort Order</label>
                <div class="admin-input-group">
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="admin-input" min="0">
                    <span class="admin-input-addon">#</span>
                </div>
            </div>

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Detail</label>
                <textarea name="detail" rows="6" class="admin-textarea" placeholder="Technology detail for public page">{{ old('detail') }}</textarea>
            </div>

            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="admin-btn admin-btn-primary">Save Technology</button>
                <a href="{{ route('admin.tech-details.index') }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
