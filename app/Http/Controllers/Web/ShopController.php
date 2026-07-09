<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ShopCategory;
use App\Models\ShopProduct;
use App\Services\BakongKhqrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $activeCategory = $request->string('category')->toString();
        $search = $request->string('search')->toString();

        $products = collect();
        $categories = collect();

        try {
            if (Schema::hasTable('shop_categories')) {
                $categories = ShopCategory::query()
                    ->when(Schema::hasColumn('shop_categories', 'status'), fn ($query) => $query->where('status', 'active'))
                    ->orderBy('name')
                    ->get();
            }

            if (Schema::hasTable('shop_products')) {
                $query = ShopProduct::query()
                    ->with(['category', 'images'])
                    ->when(Schema::hasColumn('shop_products', 'status'), fn ($builder) => $builder->where('status', 'active'));

                if ($activeCategory !== '') {
                    $query->whereHas('category', function ($builder) use ($activeCategory) {
                        $builder->where('slug', $activeCategory)
                            ->orWhere('name', $activeCategory);
                    });
                }

                if ($search !== '') {
                    $query->where(function ($builder) use ($search) {
                        $builder->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%")
                            ->orWhere('barcode', 'like', "%{$search}%");
                    });
                }

                $products = $query->latest('id')->paginate(20)->withQueryString();
                $products->onEachSide(1);
            }
        } catch (Throwable) {
            $products = collect();
            $categories = collect();
        }

        return view('web.pages.shop.index', [
            'products' => $products,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'search' => $search,
            'shopReady' => Schema::hasTable('shop_categories') && Schema::hasTable('shop_products') && Schema::hasTable('shop_product_images'),
        ]);
    }

    public function show(string $product, BakongKhqrService $bakongKhqrService): View
    {
        abort_unless(Schema::hasTable('shop_products'), 404);
        $checkoutQrProvider = $this->resolveCheckoutQrProvider();

        $shopProduct = ShopProduct::query()
            ->with(['category', 'images'])
            ->when(Schema::hasColumn('shop_products', 'status'), fn ($builder) => $builder->where('status', 'active'))
            ->where(function ($builder) use ($product) {
                $builder->where('slug', $product);

                if (is_numeric($product)) {
                    $builder->orWhere('id', (int) $product);
                }
            })
            ->firstOrFail();

        $relatedProducts = ShopProduct::query()
            ->with(['category', 'images'])
            ->when(Schema::hasColumn('shop_products', 'status'), fn ($builder) => $builder->where('status', 'active'))
            ->where('category_id', $shopProduct->category_id)
            ->where('id', '!=', $shopProduct->id)
            ->latest('id')
            ->take(8)
            ->get();

        $gallery = collect([$shopProduct->image_url])
            ->merge($shopProduct->images->pluck('image_path')->map(fn ($path) => $this->normalizeImagePath($path)))
            ->filter()
            ->values();

        $shopKhqrPreviewUrl = null;
        $shopKhqrDeepLink = null;
        $shopKhqrError = null;
        $shopKhqrModalTitle = 'Bakong KHQR';
        $shopKhqrCaption = __('Scan our official Bakong KHQR with any banking app that supports KHQR.');

        try {
            // Pause ABA shop QR generation for now while Bakong is the active QR option.
            $bakongKhqr = $bakongKhqrService->generateCheckoutKhqr([
                'amount' => (float) ($shopProduct->sale_price ?: 0),
                'currency' => 'USD',
                'order_no' => 'SHOP-' . $shopProduct->id,
                'bill_number' => 'SHOP-' . $shopProduct->id . '-' . now()->format('YmdHis'),
                'course_title' => $shopProduct->name,
            ]);

            $shopKhqrPreviewUrl = data_get($bakongKhqr, 'image_data_uri');
            $shopKhqrDeepLink = data_get($bakongKhqr, 'deep_link');
        } catch (Throwable $exception) {
            $shopKhqrError = $exception->getMessage();
        }

        return view('web.pages.shop.show', [
            'product' => $shopProduct,
            'gallery' => $gallery,
            'relatedProducts' => $relatedProducts,
            'checkoutQrProvider' => $checkoutQrProvider,
            'shopKhqrModalTitle' => $shopKhqrModalTitle,
            'shopKhqrCaption' => $shopKhqrCaption,
            'shopKhqrPreviewUrl' => $shopKhqrPreviewUrl,
            'shopKhqrDeepLink' => $shopKhqrDeepLink,
            'shopKhqrError' => $shopKhqrError,
            'shopReady' => Schema::hasTable('shop_categories') && Schema::hasTable('shop_products') && Schema::hasTable('shop_product_images'),
        ]);
    }

    protected function normalizeImagePath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $normalizedPath = str_starts_with($path, 'storage/')
            ? ltrim(substr($path, strlen('storage/')), '/')
            : ltrim($path, '/');

        return route('media.public', ['path' => $normalizedPath]);
    }

    protected function resolveCheckoutQrProvider(): string
    {
        // Keep ABA shop checkout paused for now until the bank-specific flow is implemented later.
        return 'bakong';
    }
}
