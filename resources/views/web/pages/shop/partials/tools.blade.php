<div class="shop-cart-rail">
    <button type="button" class="shop-cart-rail__btn" data-cart-toggle aria-label="{{ __('Open cart') }}">
        <i class="fa-solid fa-cart-shopping"></i>
        <span class="shop-cart-rail__count" data-cart-count>0</span>
    </button>
    <button type="button" class="shop-cart-rail__btn" data-favorite-open aria-label="{{ __('Wishlist') }}">
        <i class="fa-regular fa-heart"></i>
        <span class="shop-cart-rail__count is-hidden" data-favorite-count>0</span>
    </button>
    <button type="button" class="shop-cart-rail__btn" data-scroll-top aria-label="{{ __('Back to top') }}">
        <span class="shop-cart-rail__label">TOP</span>
    </button>
</div>

<div class="shop-cart-backdrop" data-cart-backdrop></div>
<aside class="shop-cart-drawer" data-cart-drawer aria-hidden="true">
    <div class="shop-cart-drawer__head">
        <div>
            <h2 class="shop-cart-drawer__title">{{ __('Shopping Cart') }}</h2>
            <div class="shop-cart-item__meta">{{ __('Review your selected products before checkout.') }}</div>
        </div>
        <button type="button" class="shop-cart-drawer__close" data-cart-close aria-label="{{ __('Close cart') }}">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="shop-cart-drawer__body" data-cart-items></div>

    <div class="shop-cart-drawer__foot">
        <div class="shop-cart-summary">
            <div class="shop-cart-summary__row">
                <span>{{ __('Items') }}</span>
                <strong data-cart-items-total>0</strong>
            </div>
            <div class="shop-cart-summary__row">
                <span>{{ __('Total') }}</span>
                <strong data-cart-total>$0.00</strong>
            </div>
        </div>
        <button type="button" class="shop-cart-checkout" data-cart-checkout>{{ __('Checkout Now') }}</button>
        <div class="shop-cart-note">{{ __('Checkout UI is ready here. Later we can connect this to shop order and payment tables.') }}</div>
    </div>
</aside>

<aside class="shop-cart-drawer shop-favorite-drawer" data-favorite-drawer aria-hidden="true">
    <div class="shop-cart-drawer__head">
        <div>
            <h2 class="shop-cart-drawer__title">{{ __('Favorite Products') }}</h2>
            <div class="shop-cart-item__meta">{{ __('Review your saved products here before adding them to cart.') }}</div>
        </div>
        <button type="button" class="shop-cart-drawer__close" data-favorite-close aria-label="{{ __('Close favorites') }}">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="shop-cart-drawer__body" data-favorite-items></div>

    <div class="shop-cart-drawer__foot">
        <div class="shop-cart-summary">
            <div class="shop-cart-summary__row">
                <span>{{ __('Items') }}</span>
                <strong data-favorite-items-total>0</strong>
            </div>
        </div>
        <div class="shop-cart-note">{{ __('Click the heart on any product card to save or remove favorite items instantly.') }}</div>
    </div>
</aside>
