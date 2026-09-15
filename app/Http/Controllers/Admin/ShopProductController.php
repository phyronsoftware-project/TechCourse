<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopCategory;
use App\Models\ShopProduct;
use App\Models\ShopProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class ShopProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = ShopProduct::query()
            ->with(['category'])
            ->withCount('images')
            ->latest('id');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        return view('admin.pages.shop-products.index', [
            'pageTitle' => 'Shop Products',
            'products' => $query->paginate(10)->withQueryString(),
            'categories' => ShopCategory::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.shop-products.create', [
            'pageTitle' => 'Create Shop Product',
            'categories' => ShopCategory::query()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request);
        $data['slug'] = filled($data['slug'] ?? null)
            ? Str::slug((string) $data['slug'])
            : Str::slug((string) $data['name']);
        $data['image'] = $request->file('image')?->store('shop/products', 'public');

        $product = ShopProduct::create($data);
        $this->storeSubImages($request, $product);

        return redirect()
            ->route('admin.shop-products.index')
            ->with('success', 'Shop product created successfully.');
    }

    public function edit(ShopProduct $shopProduct): View
    {
        $shopProduct->load('images');

        return view('admin.pages.shop-products.edit', [
            'pageTitle' => 'Edit Shop Product',
            'product' => $shopProduct,
            'recordId' => $shopProduct->id,
            'categories' => ShopCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ShopProduct $shopProduct): RedirectResponse
    {
        $data = $this->validateProduct($request, $shopProduct);
        $data['slug'] = filled($data['slug'] ?? null)
            ? Str::slug((string) $data['slug'])
            : Str::slug((string) $data['name']);

        if ($request->hasFile('image')) {
            if ($shopProduct->image && !str_starts_with($shopProduct->image, 'http') && !str_starts_with($shopProduct->image, 'storage/')) {
                Storage::disk('public')->delete($shopProduct->image);
            }

            $data['image'] = $request->file('image')->store('shop/products', 'public');
        } else {
            unset($data['image']);
        }

        $shopProduct->update($data);
        $this->removeSelectedSubImages($request, $shopProduct);
        $this->storeSubImages($request, $shopProduct);

        return redirect()
            ->route('admin.shop-products.index')
            ->with('success', 'Shop product updated successfully.');
    }

    public function destroy(ShopProduct $shopProduct): RedirectResponse
    {
        try {
            $imagePaths = DB::transaction(fn () => $this->deleteProduct($shopProduct));

            if ($imagePaths !== []) {
                Storage::disk('public')->delete($imagePaths);
            }
        } catch (Throwable) {
            return back()->with('error', 'Unable to delete this shop product right now.');
        }

        return redirect()
            ->route('admin.shop-products.index')
            ->with('success', 'Shop product deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['required', 'integer', 'distinct', 'exists:shop_products,id'],
        ]);

        $products = ShopProduct::query()->whereIn('id', $data['ids'])->get();

        try {
            // Delete selected products atomically and clean their stored images after commit.
            $imagePaths = DB::transaction(function () use ($products) {
                return $products
                    ->flatMap(fn (ShopProduct $product) => $this->deleteProduct($product))
                    ->values()
                    ->all();
            });

            if ($imagePaths !== []) {
                Storage::disk('public')->delete($imagePaths);
            }
        } catch (Throwable) {
            return back()->with('error', 'Unable to delete the selected shop products right now.');
        }

        return back()->with('success', $products->count() . ' shop products deleted successfully.');
    }

    protected function validateProduct(Request $request, ?ShopProduct $shopProduct = null): array
    {
        $productId = $shopProduct?->id;

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:shop_categories,id'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('shop_products', 'slug')->ignore($productId)],
            'description' => ['nullable', 'string'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'stock_qty' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:4096'],
            'sub_images' => ['nullable', 'array'],
            'sub_images.*' => ['nullable', 'image', 'max:4096'],
            'remove_sub_image_ids' => ['nullable', 'array'],
            'remove_sub_image_ids.*' => ['integer'],
            'status' => ['required', 'in:active,inactive'],
            'sku' => ['required', 'string', 'max:100', Rule::unique('shop_products', 'sku')->ignore($productId)],
            'barcode' => ['nullable', 'string', 'max:120', Rule::unique('shop_products', 'barcode')->ignore($productId)],
        ]);
    }

    protected function storeSubImages(Request $request, ShopProduct $product): void
    {
        if (! $request->hasFile('sub_images')) {
            return;
        }

        foreach ($request->file('sub_images', []) as $file) {
            if (! $file) {
                continue;
            }

            ShopProductImage::create([
                'product_id' => $product->id,
                'image_path' => $file->store('shop/products/gallery', 'public'),
            ]);
        }
    }

    protected function removeSelectedSubImages(Request $request, ShopProduct $product): void
    {
        $ids = collect($request->input('remove_sub_image_ids', []))
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($ids->isEmpty()) {
            return;
        }

        $images = $product->images()->whereIn('id', $ids)->get();
        foreach ($images as $image) {
            if ($image->image_path && !str_starts_with($image->image_path, 'http') && !str_starts_with($image->image_path, 'storage/')) {
                Storage::disk('public')->delete($image->image_path);
            }
        }

        $product->images()->whereIn('id', $ids)->delete();
    }

    protected function deleteProduct(ShopProduct $product): array
    {
        // Reuse identical image cleanup for single and bulk product deletion.
        $product->loadMissing('images');
        $imagePaths = collect([$product->image])
            ->merge($product->images->pluck('image_path'))
            ->filter(fn ($path) => filled($path)
                && ! str_starts_with($path, 'http')
                && ! str_starts_with($path, 'storage/'))
            ->values()
            ->all();

        $product->images()->delete();
        $product->delete();

        return $imagePaths;
    }
}
