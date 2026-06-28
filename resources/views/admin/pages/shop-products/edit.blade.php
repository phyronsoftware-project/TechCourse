@extends('admin.layouts.app')

@section('title', 'Edit Shop Product')

@section('content')
    <section class="admin-form-card p-6">
        <h2 class="admin-section-title">Edit Shop Product</h2>
        <p class="admin-section-copy">Update product price, stock, image, and product information carefully.</p>

        <form action="{{ route('admin.shop-products.update', $product) }}" method="POST" enctype="multipart/form-data" class="admin-form-grid mt-6">
            @csrf
            @method('PUT')

            <div class="admin-field">
                <label>Name</label>
                <div class="admin-input-group">
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" class="admin-input" required>
                    <span class="admin-input-addon">Aa</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Category</label>
                <div class="admin-input-group">
                    <select name="category_id" class="admin-select" required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <span class="admin-input-addon">CT</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Slug</label>
                <div class="admin-input-group">
                    <input type="text" name="slug" value="{{ old('slug', $product->slug) }}" class="admin-input">
                    <span class="admin-input-addon">/</span>
                </div>
            </div>

            <div class="admin-field">
                <label>SKU</label>
                <div class="admin-input-group">
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="admin-input" required>
                    <span class="admin-input-addon">SKU</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Barcode</label>
                <div class="admin-input-group">
                    <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" class="admin-input">
                    <span class="admin-input-addon">BAR</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Status</label>
                <div class="admin-input-group">
                    <select name="status" class="admin-select">
                        <option value="active" @selected(old('status', $product->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $product->status) === 'inactive')>Inactive</option>
                    </select>
                    <span class="admin-input-addon">ON</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Cost Price</label>
                <div class="admin-input-group">
                    <input type="number" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" step="0.01" min="0" class="admin-input" required>
                    <span class="admin-input-addon">$</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Sale Price</label>
                <div class="admin-input-group">
                    <input type="number" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" step="0.01" min="0" class="admin-input" required>
                    <span class="admin-input-addon">$</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Stock Qty</label>
                <div class="admin-input-group">
                    <input type="number" name="stock_qty" value="{{ old('stock_qty', $product->stock_qty) }}" min="0" class="admin-input" required>
                    <span class="admin-input-addon">#</span>
                </div>
            </div>

            <div class="admin-field">
                <label>Main Image</label>
                <input type="file" name="image" accept="image/*" class="admin-input" data-file-input data-file-name="#shop-product-image-name" data-file-preview="#shop-product-image-preview">
                <p id="shop-product-image-name" class="admin-file-meta">{{ $product->image ? basename($product->image) : 'No file selected' }}</p>
            </div>

            <div class="admin-field">
                <label>Sub Images</label>
                <input type="file" name="sub_images[]" accept="image/*" multiple class="admin-input">
                <p class="admin-file-meta">Add more gallery images for this product detail page.</p>
            </div>

            <div class="admin-field">
                <label>Preview</label>
                <div id="shop-product-image-preview" class="admin-preview-box">
                    @if ($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                        <span data-preview-text class="hidden">No Image</span>
                    @else
                        <img class="hidden" alt="Shop product preview">
                        <span data-preview-text>No Image</span>
                    @endif
                </div>
            </div>

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Existing Sub Images</label>
                @if ($product->images->isNotEmpty())
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:14px;">
                        @foreach ($product->images as $subImage)
                            @php
                                $subImageUrl = str_starts_with($subImage->image_path, 'http://') || str_starts_with($subImage->image_path, 'https://')
                                    ? $subImage->image_path
                                    : asset('storage/' . ltrim($subImage->image_path, '/'));
                            @endphp
                            <label style="display:grid;gap:8px;padding:10px;border:1px solid #d9e5f2;border-radius:14px;background:#fff;">
                                <img src="{{ $subImageUrl }}" alt="Sub image {{ $loop->iteration }}" style="width:100%;height:96px;object-fit:cover;border-radius:10px;border:1px solid #edf2f8;">
                                <span style="display:flex;align-items:center;gap:8px;font-size:13px;color:#475569;">
                                    <input type="checkbox" name="remove_sub_image_ids[]" value="{{ $subImage->id }}">
                                    Remove
                                </span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <p class="admin-file-meta">No sub images uploaded yet.</p>
                @endif
            </div>

            <div class="admin-field" style="grid-column: 1 / -1;">
                <label>Description</label>
                <textarea name="description" rows="5" class="admin-textarea">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="admin-form-actions" style="grid-column: 1 / -1;">
                <button type="submit" class="admin-btn admin-btn-primary">Update Product</button>
                <a href="{{ route('admin.shop-products.index') }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
