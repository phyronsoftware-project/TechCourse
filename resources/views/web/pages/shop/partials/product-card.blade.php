@php
    $productImages = collect([$product->image])->merge($product->images->pluck('image_path'))->filter()->values();
    $salePrice = (float) $product->sale_price;
    $costPrice = (float) $product->cost_price;
    $saveAmount = max($costPrice - $salePrice, 0);
    $deliveryFee = $selectedProvince?->delivery_fee;
    $productUrl = route('shop.show', $product->slug ?: $product->id);
@endphp

{{-- Product card keeps shared skeleton hooks so slow images feel smoother. --}}
<article class="shop-card" data-skeleton-card>
    <div class="shop-card__media" data-skeleton-image>
        <span class="shop-card__warranty"><span>{{ __('New') }}</span></span>
        <a href="{{ $productUrl }}" class="shop-card__media-link" aria-label="{{ $product->name }}">
            @if ($product->image_url)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
            @endif
        </a>
    </div>

    <div class="shop-card__body">
        <div class="shop-card__category" data-skeleton-line>{{ $product->category?->name ?: '-' }}</div>
        <h2 class="shop-card__title" data-skeleton-line>
            <a href="{{ $productUrl }}" class="shop-card__title-link">{{ $product->name }}</a>
        </h2>
        <div class="shop-card__copy" data-skeleton-block>{{ $product->description }}</div>

        <div class="shop-card__meta" data-skeleton-line>
            <span class="shop-card__badge {{ $product->stock_qty > 0 ? 'is-stock' : 'is-out' }}">{{ $product->stock_qty > 0 ? __('In Stock') : __('Out of Stock') }}</span>
            <span>{{ __('Qty') }}: {{ $product->stock_qty }}</span>
        </div>

        <div class="shop-card__prices">
            <div class="shop-card__price-row" data-skeleton-line>
                <span class="shop-card__sale">${{ number_format($salePrice, 2) }}</span>
                <div class="shop-card__cost-wrap">
                    @if ($saveAmount > 0)
                        <span class="shop-card__save">${{ number_format($saveAmount, 2) }} {{ __('Off') }}</span>
                    @endif
                    <span class="shop-card__cost">${{ number_format($costPrice, 2) }}</span>
                </div>
            </div>

            <div class="shop-card__bottom-row">
                <div class="shop-card__installment" data-skeleton-block>
                    <span class="shop-card__installment-line">
                        {{ __('Delivery fee') }}:
                        <strong>{{ $deliveryFee !== null ? '$'.number_format((float) $deliveryFee, 2) : '--' }}</strong>
                    </span>
                    <span class="shop-card__fee-description">{{ \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 15, '...') }}</span>
                </div>
                <button
                    type="button"
                    class="shop-card__favorite"
                    data-favorite-toggle
                    data-id="{{ $product->id }}"
                    data-url="{{ $productUrl }}"
                    data-name="{{ e($product->name) }}"
                    data-category="{{ e($product->category?->name ?: '-') }}"
                    data-sku="{{ e($product->sku) }}"
                    data-sale="${{ number_format((float) $product->sale_price, 2) }}"
                    data-cost="${{ number_format((float) $product->cost_price, 2) }}"
                    data-stock="{{ e((string) $product->stock_qty) }}"
                    data-image="{{ $product->image_url ?: '' }}"
                    aria-label="{{ __('Toggle favorite') }}"
                    title="{{ __('Toggle favorite') }}"
                >
                    <i class="fa-regular fa-heart"></i>
                </button>
            </div>
        </div>

        <div class="shop-card__actions">
            <button
                type="button"
                class="shop-card__btn shop-card__btn--primary"
                data-cart-add
                data-id="{{ $product->id }}"
                data-name="{{ e($product->name) }}"
                data-category="{{ e($product->category?->name ?: '-') }}"
                data-description="{{ e($product->description ?: '-') }}"
                data-sku="{{ e($product->sku) }}"
                data-barcode="{{ e($product->barcode ?: '-') }}"
                data-stock="{{ e((string) $product->stock_qty) }}"
                data-sale="${{ number_format((float) $product->sale_price, 2) }}"
                data-cost="${{ number_format((float) $product->cost_price, 2) }}"
                data-images='@json($productImages)'
                data-image="{{ $product->image_url ?: '' }}"
            >{{ __('Add to Cart') }}</button>
        </div>
    </div>
</article>
