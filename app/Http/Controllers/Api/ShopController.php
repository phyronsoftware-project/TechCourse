<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShopCategory;
use App\Models\ShopFavorite;
use App\Models\ShopProduct;
use App\Models\ShopCartItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ShopController extends Controller
{
    public function home(Request $request): JsonResponse
    {
        try {
            $user = $request->user('sanctum');

            $categories = $this->baseCategoryQuery()
                ->limit(10)
                ->get()
                ->map(fn (ShopCategory $category) => $this->categoryPayload($category))
                ->values();

            $featuredProducts = $this->productCollection(
                $this->baseProductQuery()->latest('id')->limit(8)->get(),
                $user?->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Shop home data fetched successfully.',
                'data' => [
                    'categories' => $categories,
                    'featured_products' => $featuredProducts,
                    'latest_products' => $featuredProducts,
                ],
            ]);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch shop home data right now.',
                'data' => (object) [],
            ], 500);
        }
    }

    public function categories(): JsonResponse
    {
        try {
            $categories = $this->baseCategoryQuery()
                ->get()
                ->map(fn (ShopCategory $category) => $this->categoryPayload($category))
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Shop categories fetched successfully.',
                'data' => [
                    'categories' => $categories,
                ],
            ]);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch shop categories right now.',
                'data' => (object) [],
            ], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = $this->baseProductQuery();
            $userId = $request->user('sanctum')?->id;

            if ($request->filled('category')) {
                $category = trim($request->string('category')->toString());

                if (! $this->isAllFilterValue($category)) {
                    $query->whereHas('category', function ($builder) use ($category) {
                        $builder->where('slug', $category)
                            ->orWhere('name', $category);
                    });
                }
            }

            if ($request->filled('search')) {
                $search = $request->string('search')->toString();

                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            }

            match ($request->string('sort')->toString()) {
                'name' => $query->orderBy('name'),
                'price_low' => $query->orderBy('sale_price'),
                'price_high' => $query->orderByDesc('sale_price'),
                default => $query->latest('id'),
            };

            $perPage = min(max((int) $request->integer('per_page', 12), 1), 30);
            $products = $query->paginate($perPage)->withQueryString();

            return response()->json([
                'success' => true,
                'message' => 'Products fetched successfully.',
                'data' => [
                    'items' => $this->productCollection(collect($products->items()), $userId),
                    'pagination' => [
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage(),
                        'per_page' => $products->perPage(),
                        'total' => $products->total(),
                    ],
                ],
            ]);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch products right now.',
                'data' => (object) [],
            ], 500);
        }
    }

    public function show(Request $request, string $product): JsonResponse
    {
        try {
            $userId = $request->user('sanctum')?->id;

            $shopProduct = $this->baseProductQuery()
                ->where(function ($builder) use ($product) {
                    $builder->where('slug', $product);

                    if (is_numeric($product)) {
                        $builder->orWhere('id', (int) $product);
                    }
                })
                ->first();

            if (! $shopProduct) {
                throw new NotFoundHttpException();
            }

            $gallery = collect([$shopProduct->image_url])
                ->merge($shopProduct->images->pluck('image_path')->map(fn ($path) => $this->normalizeImagePath($path)))
                ->filter()
                ->values();

            $relatedProducts = $this->productCollection(
                $this->baseProductQuery()
                    ->where('category_id', $shopProduct->category_id)
                    ->where('id', '!=', $shopProduct->id)
                    ->latest('id')
                    ->limit(8)
                    ->get(),
                $userId
            );

            return response()->json([
                'success' => true,
                'message' => 'Product detail fetched successfully.',
                'data' => [
                    'product' => $this->productPayload($shopProduct, $userId),
                    'gallery' => $gallery,
                    'related_products' => $relatedProducts,
                ],
            ]);
        } catch (NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
                'errors' => (object) [],
            ], 404);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch product detail right now.',
                'data' => (object) [],
            ], 500);
        }
    }

    public function cart(Request $request): JsonResponse
    {
        try {
            $user = $request->user('sanctum');

            $items = ShopCartItem::query()
                ->with(['product.category', 'product.images'])
                ->where('user_id', $user->id)
                ->latest('id')
                ->get()
                ->map(fn (ShopCartItem $item) => $this->cartItemPayload($item, $user->id))
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Cart fetched successfully.',
                'data' => [
                    'items' => $items,
                    'summary' => $this->cartSummaryPayload($items),
                ],
            ]);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch cart right now.',
                'data' => (object) [],
            ], 500);
        }
    }

    public function storeCartItem(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'product_id' => ['required', 'integer', 'exists:shop_products,id'],
                'qty' => ['nullable', 'integer', 'min:1'],
            ]);

            $user = $request->user('sanctum');
            $product = $this->baseProductQuery()->findOrFail((int) $validated['product_id']);
            $qty = max((int) ($validated['qty'] ?? 1), 1);

            if ((int) $product->stock_qty < $qty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Requested quantity is over current stock.',
                    'errors' => [
                        'qty' => ['Requested quantity is over current stock.'],
                    ],
                ], 422);
            }

            $item = ShopCartItem::query()->firstOrNew([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);

            $item->qty = min((int) $product->stock_qty, ($item->exists ? (int) $item->qty : 0) + $qty);
            $item->save();
            $item->loadMissing(['product.category', 'product.images']);

            return response()->json([
                'success' => true,
                'message' => 'Product added to cart successfully.',
                'data' => [
                    'item' => $this->cartItemPayload($item, $user->id),
                ],
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to update cart right now.',
                'data' => (object) [],
            ], 500);
        }
    }

    public function updateCartQuantity(Request $request, string $product): JsonResponse
    {
        try {
            $validated = $request->validate([
                'qty' => ['required', 'integer', 'min:0'],
            ]);

            $user = $request->user('sanctum');
            $productModel = $this->resolveProduct($product);
            $item = ShopCartItem::query()->where('user_id', $user->id)->where('product_id', $productModel->id)->first();
            $qty = (int) $validated['qty'];

            if ($qty === 0) {
                $item?->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Product removed from cart successfully.',
                    'data' => [
                        'removed_product_id' => $productModel->id,
                    ],
                ]);
            }

            if ((int) $productModel->stock_qty < $qty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Requested quantity is over current stock.',
                    'errors' => [
                        'qty' => ['Requested quantity is over current stock.'],
                    ],
                ], 422);
            }

            $item ??= new ShopCartItem([
                'user_id' => $user->id,
                'product_id' => $productModel->id,
            ]);

            $item->qty = $qty;
            $item->save();
            $item->loadMissing(['product.category', 'product.images']);

            return response()->json([
                'success' => true,
                'message' => 'Cart quantity updated successfully.',
                'data' => [
                    'item' => $this->cartItemPayload($item, $user->id),
                ],
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
                'errors' => (object) [],
            ], 404);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to update cart quantity right now.',
                'data' => (object) [],
            ], 500);
        }
    }

    public function favorites(Request $request): JsonResponse
    {
        try {
            $user = $request->user('sanctum');

            $items = ShopFavorite::query()
                ->with(['product.category', 'product.images'])
                ->where('user_id', $user->id)
                ->latest('id')
                ->get()
                ->map(fn (ShopFavorite $favorite) => $favorite->product ? $this->productPayload($favorite->product, $user->id) : null)
                ->filter()
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Favorites fetched successfully.',
                'data' => [
                    'items' => $items,
                ],
            ]);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch favorites right now.',
                'data' => (object) [],
            ], 500);
        }
    }

    public function toggleFavorite(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'product_id' => ['required', 'integer', 'exists:shop_products,id'],
            ]);

            $user = $request->user('sanctum');
            $product = $this->baseProductQuery()->findOrFail((int) $validated['product_id']);

            $favorite = ShopFavorite::query()->where('user_id', $user->id)->where('product_id', $product->id)->first();

            if ($favorite) {
                $favorite->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Product removed from favorites successfully.',
                    'data' => [
                        'is_favorite' => false,
                        'product' => $this->productPayload($product, $user->id),
                    ],
                ]);
            }

            ShopFavorite::query()->create([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product added to favorites successfully.',
                'data' => [
                    'is_favorite' => true,
                    'product' => $this->productPayload($product, $user->id),
                ],
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to update favorites right now.',
                'data' => (object) [],
            ], 500);
        }
    }

    protected function baseCategoryQuery()
    {
        $query = ShopCategory::query()->orderBy('name');

        if (Schema::hasColumn('shop_categories', 'status')) {
            $query->where('status', 'active');
        }

        return $query;
    }

    protected function baseProductQuery()
    {
        $query = ShopProduct::query()->with(['category', 'images']);

        if (Schema::hasColumn('shop_products', 'status')) {
            $query->where('status', 'active');
        }

        return $query;
    }

    protected function categoryPayload(ShopCategory $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'status' => $category->status ?? 'active',
        ];
    }

    protected function productCollection($products, ?int $userId)
    {
        return $products->map(fn (ShopProduct $product) => $this->productPayload($product, $userId))->values();
    }

    protected function productPayload(ShopProduct $product, ?int $userId): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'cost_price' => (float) $product->cost_price,
            'sale_price' => (float) $product->sale_price,
            'stock_qty' => (int) $product->stock_qty,
            'image_url' => $product->image_url,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'category' => $product->category ? $this->categoryPayload($product->category) : null,
            'gallery' => $product->images->map(fn ($image) => $this->normalizeImagePath($image->image_path))->filter()->values(),
            'is_favorite' => $this->isFavorite($product->id, $userId),
            'in_cart' => $this->inCart($product->id, $userId),
        ];
    }

    protected function cartItemPayload(ShopCartItem $item, ?int $userId): array
    {
        return [
            'id' => $item->id,
            'qty' => (int) $item->qty,
            'subtotal' => (float) ($item->qty * (float) ($item->product?->sale_price ?? 0)),
            'product' => $item->product ? $this->productPayload($item->product, $userId) : null,
        ];
    }

    protected function cartSummaryPayload($items): array
    {
        $itemCount = $items->sum(fn ($item) => (int) ($item['qty'] ?? 0));
        $subtotal = $items->sum(fn ($item) => (float) ($item['subtotal'] ?? 0));

        return [
            'items_count' => $itemCount,
            'subtotal' => round($subtotal, 2),
            'total' => round($subtotal, 2),
        ];
    }

    protected function isFavorite(int $productId, ?int $userId): bool
    {
        if (! $userId || ! Schema::hasTable('shop_favorites')) {
            return false;
        }

        return ShopFavorite::query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    protected function inCart(int $productId, ?int $userId): bool
    {
        if (! $userId || ! Schema::hasTable('shop_cart_items')) {
            return false;
        }

        return ShopCartItem::query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
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

    protected function isAllFilterValue(string $value): bool
    {
        return in_array(mb_strtolower(trim($value)), ['all', 'all-products', '*'], true);
    }

    protected function resolveProduct(string $product): ShopProduct
    {
        $productModel = $this->baseProductQuery()
            ->where(function ($builder) use ($product) {
                $builder->where('slug', $product);

                if (is_numeric($product)) {
                    $builder->orWhere('id', (int) $product);
                }
            })
            ->first();

        if (! $productModel) {
            throw new NotFoundHttpException();
        }

        return $productModel;
    }
}
