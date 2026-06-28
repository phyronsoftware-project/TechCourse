<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ShopCartItem;
use App\Models\ShopFavorite;
use App\Models\ShopProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopInteractionController extends Controller
{
    public function state(Request $request): JsonResponse
    {
        return response()->json($this->buildState($request));
    }

    public function toggleCart(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:shop_products,id'],
            'qty' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = ShopProduct::query()->findOrFail($data['product_id']);
        $requestedQty = (int) ($data['qty'] ?? 1);

        if ($product->stock_qty < 1) {
            return response()->json([
                'message' => __('This product is out of stock.'),
                ...$this->buildState($request),
            ], 422);
        }

        if ($requestedQty > (int) $product->stock_qty) {
            return response()->json([
                'message' => __('Requested quantity is over current stock.'),
                ...$this->buildState($request),
            ], 422);
        }

        $item = ShopCartItem::query()
            ->where('user_id', $request->user()->id)
            ->where('product_id', $data['product_id'])
            ->first();

        if ($item) {
            $item->delete();

            return response()->json([
                'message' => __('Removed from cart'),
                ...$this->buildState($request),
            ]);
        }

        ShopCartItem::query()->create([
            'user_id' => $request->user()->id,
            'product_id' => $data['product_id'],
            'qty' => $requestedQty,
        ]);

        return response()->json([
            'message' => __('Added to cart'),
            ...$this->buildState($request),
        ]);
    }

    public function updateCartQty(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:shop_products,id'],
            'action' => ['required', 'in:plus,minus,remove'],
        ]);

        $item = ShopCartItem::query()
            ->with('product')
            ->where('user_id', $request->user()->id)
            ->where('product_id', $data['product_id'])
            ->first();

        if (! $item) {
            return response()->json([
                'message' => __('Cart item not found'),
                ...$this->buildState($request),
            ], 404);
        }

        if ($data['action'] === 'remove') {
            $item->delete();
        } elseif ($data['action'] === 'plus') {
            if (! $item->product || $item->qty >= (int) $item->product->stock_qty) {
                return response()->json([
                    'message' => __('You cannot add quantity over current stock.'),
                    ...$this->buildState($request),
                ], 422);
            }

            $item->increment('qty');
        } else {
            if ($item->qty <= 1) {
                $item->delete();
            } else {
                $item->decrement('qty');
            }
        }

        return response()->json([
            'message' => __('Cart updated'),
            ...$this->buildState($request),
        ]);
    }

    public function toggleFavorite(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:shop_products,id'],
        ]);

        $favorite = ShopFavorite::query()
            ->where('user_id', $request->user()->id)
            ->where('product_id', $data['product_id'])
            ->first();

        if ($favorite) {
            $favorite->delete();

            return response()->json([
                'message' => __('Removed from favorites'),
                ...$this->buildState($request),
            ]);
        }

        ShopFavorite::query()->create([
            'user_id' => $request->user()->id,
            'product_id' => $data['product_id'],
        ]);

        return response()->json([
            'message' => __('Added to favorites'),
            ...$this->buildState($request),
        ]);
    }

    protected function buildState(Request $request): array
    {
        $user = $request->user();

        $cartItems = ShopCartItem::query()
            ->with(['product.category', 'product.images'])
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->get()
            ->map(fn (ShopCartItem $item) => $this->mapProduct($item->product, $item->qty))
            ->filter()
            ->values();

        $favorites = ShopFavorite::query()
            ->with(['product.category', 'product.images'])
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->get()
            ->map(fn (ShopFavorite $favorite) => $this->mapProduct($favorite->product))
            ->filter()
            ->values();

        return [
            'cart' => $cartItems,
            'favorites' => $favorites,
        ];
    }

    protected function mapProduct(?ShopProduct $product, int $qty = 1): ?array
    {
        if (! $product) {
            return null;
        }

        return [
            'id' => $product->id,
            'url' => route('shop.show', $product->slug ?: $product->id),
            'name' => $product->name,
            'category' => $product->category?->name ?: '-',
            'description' => $product->description ?: '-',
            'sku' => $product->sku ?: '-',
            'barcode' => $product->barcode ?: '-',
            'image' => $product->image_url ?: '',
            'salePrice' => (float) $product->sale_price,
            'costPrice' => (float) $product->cost_price,
            'stock' => (int) $product->stock_qty,
            'qty' => $qty,
        ];
    }
}
