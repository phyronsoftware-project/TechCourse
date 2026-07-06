<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AbaPayWayService;
use App\Models\ShopCategory;
use App\Models\ShopProduct;
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

    public function show(string $product, AbaPayWayService $abaPaywayService): View
    {
        abort_unless(Schema::hasTable('shop_products'), 404);
        $checkoutQrProvider = $this->resolveCheckoutQrProvider($abaPaywayService);

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
        $shopKhqrModalTitle = $checkoutQrProvider === 'aba' ? 'ABA KHQR' : 'Bakong KHQR';
        $shopKhqrCaption = $checkoutQrProvider === 'aba'
            ? __('Scan with ABA app or any banking app that supports ABA KHQR.')
            : __('Scan our official Bakong or ACLEDA KHQR with any banking app that supports KHQR.');

        try {
            $abaKhqr = $abaPaywayService->generateKhqr([
                'tran_id' => $this->generateShopAbaTranId($shopProduct),
                'amount' => (float) ($shopProduct->sale_price ?: 0),
                'currency' => 'USD',
                'item_name' => $shopProduct->name,
                'first_name' => 'TechCourse',
                'last_name' => 'Shop',
                'email' => '',
                'phone' => '',
            ]);

            $shopKhqrPreviewUrl = data_get($abaKhqr, 'qrImage') ?: data_get($abaKhqr, 'data.qrImage');
            $shopKhqrDeepLink = data_get($abaKhqr, 'abapay_deeplink');
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

    protected function generateShopAbaTranId(ShopProduct $product): string
    {
        return 'TCSHOP' . $product->id . now()->format('His') . strtoupper(\Illuminate\Support\Str::random(4));
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

    protected function resolveCheckoutQrProvider(AbaPayWayService $abaPaywayService): string
    {
        return $abaPaywayService->summary()['is_ready'] ? 'aba' : 'aba';
    }
}
