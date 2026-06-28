@extends('admin.layouts.app')

@section('title', 'Create Shop Product')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Create Shop Product</h2>
        <p class="admin-section-copy">Add a product row that matches the shopping SQL structure.</p>

        <form action="{{ route('admin.shop-products.store') }}" method="POST" enctype="multipart/form-data" class="admin-form-grid mt-6">
            @csrf

            <div class="admin-field">
                <label>Name</label>
                <div class="admin-input-group">
                    <input type="text" name="name" value="{{ old('name') }}" class="admin-input" required>
                    <span class="admin-input-addon">Aa</span>
                </div>
            </div>

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
                <label>Slug</label>
                <div class="admin-input-group">
                    <input type="text" name="slug" value="{{ old('slug') }}" class="admin-input" placeholder="shop-product-slug">
                    <span class="admin-input-addon">/</span>
                </div>
            </div>

            <div class="admin-field">
                <label>SKU</label>
                <div class="admin-input-group">
                    <input type="text" name="sku" value="{{ old('sku') }}" class="admin-input" required>
                    <span class="admin-input-addon">SKU</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Barcode</label>
                <div class="admin-input-group">
                    <input type="text" name="barcode" value="{{ old('barcode') }}" class="admin-input">
                    <span class="admin-input-addon">BAR</span>
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
                <label>Cost Price</label>
                <div class="admin-input-group">
                    <input type="number" name="cost_price" value="{{ old('cost_price', 0) }}" step="0.01" min="0" class="admin-input" required>
                    <span class="admin-input-addon">$</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Sale Price</label>
                <div class="admin-input-group">
                    <input type="number" name="sale_price" value="{{ old('sale_price', 0) }}" step="0.01" min="0" class="admin-input" required>
                    <span class="admin-input-addon">$</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Stock Qty</label>
                <div class="admin-input-group">
                    <input type="number" name="stock_qty" value="{{ old('stock_qty', 0) }}" min="0" class="admin-input" required>
                    <span class="admin-input-addon">#</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Main Image</label>
                <input type="file" name="image" accept="image/*" class="admin-input" data-file-input data-file-name="#shop-product-image-name" data-file-preview="#shop-product-image-preview">
                <p id="shop-product-image-name" class="admin-file-meta">No file selected</p>
            </div>

            <div class="admin-field">
                <label>Sub Images</label>
                <input type="file" name="sub_images[]" accept="image/*" multiple class="admin-input">
                <p class="admin-file-meta">You can select multiple sub images for product detail gallery.</p>
            </div>

            <div class="admin-field">
                <label>Preview</label>
                <div id="shop-product-image-preview" class="admin-preview-box">
                    <img class="hidden" alt="Shop product preview">
                    <span data-preview-text>No Image</span>
                </div>
            </div>

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Description</label>
                <textarea name="description" rows="5" class="admin-textarea">{{ old('description') }}</textarea>
            </div>

            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="admin-btn admin-btn-primary">Save Product</button>
                <a href="{{ route('admin.shop-products.index') }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
