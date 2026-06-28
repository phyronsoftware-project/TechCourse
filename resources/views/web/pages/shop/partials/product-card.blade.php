@php
    $productImages = collect([$product->image])->merge($product->images->pluck('image_path'))->filter()->values();
    $salePrice = (float) $product->sale_price;
    $costPrice = (float) $product->cost_price;
    $saveAmount = max($costPrice - $salePrice, 0);
    $monthlyPrice = $salePrice > 0 ? ceil(($salePrice / 12) * 100) / 100 : 0;
    $productUrl = route('shop.show', $product->slug ?: $product->id);
@endphp

<article class="shop-card">
    <div class="shop-card__media">
        <span class="shop-card__ribbon">{{ __('New') }}</span>
        <div class="shop-card__warranty">
            <img src="{{ asset('Logo-Socail/warranty.png') }}" alt="{{ __('1 Year Warranty') }}">
        </div>
        <a href="{{ $productUrl }}" class="shop-card__media-link" aria-label="{{ $product->name }}">
            @if ($product->image_url)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
            @endif
        </a>
    </div>

    <div class="shop-card__body">
        <div class="shop-card__category">{{ $product->category?->name ?: '-' }}</div>
        <h2 class="shop-card__title">
            <a href="{{ $productUrl }}" class="shop-card__title-link">{{ $product->name }}</a>
        </h2>
        <div class="shop-card__copy">{{ $product->description }}</div>

        <div class="shop-card__meta">
            <span class="shop-card__badge {{ $product->stock_qty > 0 ? 'is-stock' : 'is-out' }}">{{ $product->stock_qty > 0 ? __('In Stock') : __('Out of Stock') }}</span>
            <span>{{ __('Qty') }}: {{ $product->stock_qty }}</span>
        </div>

        <div class="shop-card__prices">
            <div class="shop-card__price-row">
                <span class="shop-card__sale">${{ number_format($salePrice, 2) }}</span>
                <div class="shop-card__cost-wrap">
                    @if ($saveAmount > 0)
                        <span class="shop-card__save">${{ number_format($saveAmount, 2) }} {{ __('Off') }}</span>
                    @endif
                    <span class="shop-card__cost">${{ number_format($costPrice, 2) }}</span>
                </div>
            </div>

            <div class="shop-card__bottom-row">
                <div class="shop-card__installment">
                    <span class="shop-card__installment-line">{{ __('Or') }} <strong>${{ number_format($monthlyPrice, 2) }}</strong>/mo.</span>
                    <span>{{ __('for 12 mo.') }}<sup>*</sup></span>
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
