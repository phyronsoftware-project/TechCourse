@push('web_scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const results = document.querySelector('[data-shop-results]');
            const cartDrawer = document.querySelector('[data-cart-drawer]');
            const favoriteDrawer = document.querySelector('[data-favorite-drawer]');
            const cartBackdrop = document.querySelector('[data-cart-backdrop]');
            const cartItems = document.querySelector('[data-cart-items]');
            const favoriteItems = document.querySelector('[data-favorite-items]');
            const cartCount = document.querySelector('[data-cart-count]');
            const favoriteCount = document.querySelector('[data-favorite-count]');
            const cartItemsTotal = document.querySelector('[data-cart-items-total]');
            const cartTotal = document.querySelector('[data-cart-total]');
            const favoriteItemsTotal = document.querySelector('[data-favorite-items-total]');
            const detailUrlTemplate = @json(route('shop.show', '__SHOP_ITEM__'));
            const isAuthenticated = @json(auth()->check());
            const loginUrl = @json(route('web.login'));
            const csrfToken = @json(csrf_token());
            const shopStateUrl = @json(route('shop.state'));
            const shopCartToggleUrl = @json(route('shop.cart.toggle'));
            const shopCartQtyUrl = @json(route('shop.cart.qty'));
            const shopFavoriteToggleUrl = @json(route('shop.favorite.toggle'));
            let currentCart = [];
            let currentFavorites = [];

            if (!cartDrawer || !favoriteDrawer || !cartBackdrop || !cartItems || !favoriteItems || !cartCount || !favoriteCount || !cartItemsTotal || !cartTotal || !favoriteItemsTotal) {
                return;
            }

            const readCart = () => currentCart;
            const readFavorites = () => currentFavorites;

            const getProductUrl = (item) => {
                if (item.url) {
                    return item.url;
                }

                return detailUrlTemplate.replace('__SHOP_ITEM__', encodeURIComponent(String(item.id || '')));
            };

            const formatMoney = (value) => `$${Number(value || 0).toFixed(2)}`;

            const setState = (payload = {}) => {
                currentCart = Array.isArray(payload.cart) ? payload.cart : [];
                currentFavorites = Array.isArray(payload.favorites) ? payload.favorites : [];
            };

            const fetchJson = async (url, options = {}) => {
                const response = await fetch(url, {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(options.headers || {}),
                    },
                    ...options,
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw data;
                }

                return data;
            };

            const getErrorMessage = (error, fallbackMessage) => {
                if (error?.message) {
                    return error.message;
                }

                if (error?.errors) {
                    const firstError = Object.values(error.errors)[0];
                    if (Array.isArray(firstError) && firstError[0]) {
                        return firstError[0];
                    }
                }

                return fallbackMessage;
            };

            const refreshShopState = async () => {
                if (!isAuthenticated) {
                    setState({ cart: [], favorites: [] });
                    renderCart();
                    renderFavorites();
                    syncCardButtons();
                    syncFavoriteButtons();
                    return;
                }

                try {
                    const data = await fetchJson(shopStateUrl, { method: 'GET' });
                    setState(data);
                } catch (error) {
                    setState({ cart: [], favorites: [] });
                }

                renderCart();
                renderFavorites();
                syncCardButtons();
                syncFavoriteButtons();
            };

            const requireShopAuth = () => {
                if (isAuthenticated) {
                    return true;
                }

                showToast('warning', `{{ __('Login Required') }}`, `{{ __('Please login or register first before adding product to cart or favorite.') }}`);
                window.setTimeout(() => {
                    window.location.href = loginUrl;
                }, 900);
                return false;
            };

            const showToast = (type, title, message) => {
                const existing = document.querySelector('[data-shop-toast]');
                if (existing) {
                    existing.remove();
                }

                const toast = document.createElement('div');
                toast.className = `web-alert web-alert--${type}`;
                toast.setAttribute('data-shop-toast', 'true');
                toast.setAttribute('role', 'status');
                toast.setAttribute('aria-live', 'polite');
                toast.innerHTML = `
                    <div class="web-alert__icon" aria-hidden="true">
                        <i class="fa-solid ${type === 'success' ? 'fa-check' : type === 'warning' ? 'fa-triangle-exclamation' : type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-info'}"></i>
                    </div>
                    <div class="web-alert__content">
                        <div class="web-alert__title">${title}</div>
                        <div class="web-alert__message">${message}</div>
                    </div>
                    <button type="button" class="web-alert__close" aria-label="{{ __('Close alert') }}">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                `;

                const closeToast = () => {
                    toast.classList.add('is-leaving');
                    window.setTimeout(() => toast.remove(), 280);
                };

                toast.querySelector('.web-alert__close')?.addEventListener('click', closeToast);
                document.body.appendChild(toast);
                window.setTimeout(closeToast, 2600);
            };

            const syncCardButtons = () => {
                const items = readCart();
                const ids = new Set(items.map((item) => item.id));

                document.querySelectorAll('[data-cart-add]').forEach((button) => {
                    const id = Number(button.getAttribute('data-id'));
                    const isAdded = ids.has(id);

                    button.disabled = false;
                    button.classList.toggle('is-added', isAdded);
                    button.textContent = isAdded ? `{{ __('Remove from Cart') }}` : `{{ __('Add to Cart') }}`;
                });
            };

            const syncFavoriteButtons = () => {
                const items = readFavorites();
                const ids = new Set(items.map((item) => item.id));

                favoriteCount.textContent = items.length;
                favoriteCount.classList.toggle('is-hidden', items.length === 0);

                document.querySelectorAll('[data-favorite-toggle]').forEach((button) => {
                    const id = Number(button.getAttribute('data-id'));
                    const isActive = ids.has(id);
                    const icon = button.querySelector('i');

                    button.classList.toggle('is-active', isActive);
                    button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                    button.setAttribute('title', isActive ? `{{ __('Remove favorite') }}` : `{{ __('Add to favorite') }}`);
                    button.setAttribute('aria-label', isActive ? `{{ __('Remove favorite') }}` : `{{ __('Add to favorite') }}`);

                    if (icon) {
                        icon.className = isActive ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
                    }
                });
            };

            const openCart = () => {
                favoriteDrawer.classList.remove('is-open');
                favoriteDrawer.setAttribute('aria-hidden', 'true');
                cartDrawer.classList.add('is-open');
                cartDrawer.setAttribute('aria-hidden', 'false');
                cartBackdrop.classList.add('is-open');
            };

            const openFavorites = () => {
                cartDrawer.classList.remove('is-open');
                cartDrawer.setAttribute('aria-hidden', 'true');
                favoriteDrawer.classList.add('is-open');
                favoriteDrawer.setAttribute('aria-hidden', 'false');
                cartBackdrop.classList.add('is-open');
            };

            const closePanels = () => {
                cartDrawer.classList.remove('is-open');
                cartDrawer.setAttribute('aria-hidden', 'true');
                favoriteDrawer.classList.remove('is-open');
                favoriteDrawer.setAttribute('aria-hidden', 'true');
                cartBackdrop.classList.remove('is-open');
            };

            const renderCart = () => {
                const items = readCart();
                const totalQty = items.reduce((sum, item) => sum + item.qty, 0);
                const totalPrice = items.reduce((sum, item) => sum + (item.qty * item.salePrice), 0);

                cartCount.textContent = totalQty;
                cartItemsTotal.textContent = totalQty;
                cartTotal.textContent = formatMoney(totalPrice);

                if (!items.length) {
                    cartItems.innerHTML = `<div class="shop-cart-empty">{{ __('No products in cart yet. Please add item from the shop cards.') }}</div>`;
                    return;
                }

                cartItems.innerHTML = items.map((item) => `
                    <article class="shop-cart-item">
                        <div class="shop-cart-item__media">
                            ${item.image ? `<img src="${item.image}" alt="${item.name}">` : ''}
                        </div>
                        <div>
                            <h3 class="shop-cart-item__name">${item.name}</h3>
                            <div class="shop-cart-item__meta">${item.category || '-'}<br>SKU: ${item.sku || '-'}</div>
                            <div class="shop-cart-item__bottom">
                                <div>
                                    <div class="shop-cart-item__price">${formatMoney(item.salePrice)}</div>
                                    <button type="button" class="shop-cart-remove" data-cart-remove="${item.id}">{{ __('Remove') }}</button>
                                </div>
                                <div class="shop-cart-qty">
                                    <button type="button" data-cart-qty="minus" data-id="${item.id}" data-stock="${item.stock || 0}">-</button>
                                    <span>${item.qty}</span>
                                    <button type="button" data-cart-qty="plus" data-id="${item.id}" data-stock="${item.stock || 0}">+</button>
                                </div>
                            </div>
                        </div>
                    </article>
                `).join('');

                syncCardButtons();
            };

            const renderFavorites = () => {
                const items = readFavorites();
                const cartIds = new Set(readCart().map((item) => item.id));

                favoriteItemsTotal.textContent = items.length;

                if (!items.length) {
                    favoriteItems.innerHTML = `<div class="shop-cart-empty">{{ __('No favorite products yet. Click the heart icon on a shop card to save product here.') }}</div>`;
                    return;
                }

                favoriteItems.innerHTML = `
                    <div class="shop-favorite-grid">
                        ${items.map((item) => `
                            ${(() => {
                                const salePrice = Number(item.salePrice || 0);
                                const costPrice = Number(item.costPrice || item.salePrice || 0);
                                const saveAmount = Math.max(costPrice - salePrice, 0);
                                const monthlyPrice = salePrice > 0 ? Math.ceil((salePrice / 12) * 100) / 100 : 0;
                                const isInCart = cartIds.has(item.id);
                                const productUrl = getProductUrl(item);

                                return `
                                    <article class="shop-card" data-favorite-product-card data-product-url="${productUrl}">
                                        <div class="shop-card__media">
                                            <span class="shop-card__ribbon">{{ __('New') }}</span>
                                            <div class="shop-card__warranty">
                                                <img src="{{ asset('Logo-Socail/warranty.png') }}" alt="{{ __('1 Year Warranty') }}">
                                            </div>
                                            <a href="${productUrl}" class="shop-card__media-link" data-favorite-product-link aria-label="${item.name}">
                                                ${item.image ? `<img src="${item.image}" alt="${item.name}">` : ''}
                                            </a>
                                        </div>

                                        <div class="shop-card__body">
                                            <div class="shop-card__category">${item.category || '-'}</div>
                                            <h3 class="shop-card__title">
                                                <a href="${productUrl}" class="shop-card__title-link" data-favorite-product-link>${item.name}</a>
                                            </h3>

                                            <div class="shop-card__meta">
                                                <span class="shop-card__badge ${Number(item.stock || 0) > 0 ? 'is-stock' : 'is-out'}">${Number(item.stock || 0) > 0 ? `{{ __('In Stock') }}` : `{{ __('Out of Stock') }}`}</span>
                                                <span>{{ __('Qty') }}: ${item.stock || 0}</span>
                                            </div>

                                            <div class="shop-card__prices">
                                                <div class="shop-card__price-row">
                                                    <span class="shop-card__sale">${formatMoney(salePrice)}</span>
                                                    <div class="shop-card__cost-wrap">
                                                        ${saveAmount > 0 ? `<span class="shop-card__save">${formatMoney(saveAmount)} {{ __('Off') }}</span>` : ''}
                                                        <span class="shop-card__cost">${formatMoney(costPrice)}</span>
                                                    </div>
                                                </div>

                                                <div class="shop-card__bottom-row">
                                                    <div class="shop-card__installment">
                                                        <span class="shop-card__installment-line">{{ __('Or') }} <strong>${formatMoney(monthlyPrice)}</strong>/mo.</span>
                                                        <span>{{ __('for 12 mo.') }}<sup>*</sup></span>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        class="shop-card__favorite is-active"
                                                        data-favorite-toggle
                                                        data-id="${item.id}"
                                                        data-url="${productUrl}"
                                                        data-name="${item.name.replace(/"/g, '&quot;')}"
                                                        data-category="${(item.category || '-').replace(/"/g, '&quot;')}"
                                                        data-sku="${(item.sku || '-').replace(/"/g, '&quot;')}"
                                                        data-sale="${formatMoney(salePrice)}"
                                                        data-cost="${formatMoney(costPrice)}"
                                                        data-stock="${item.stock || 0}"
                                                        data-image="${item.image || ''}"
                                                        aria-label="{{ __('Remove favorite') }}"
                                                        title="{{ __('Remove favorite') }}"
                                                    >
                                                        <i class="fa-solid fa-heart"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="shop-card__actions">
                                                <button
                                                    type="button"
                                                    class="shop-card__btn shop-card__btn--primary ${isInCart ? 'is-added' : ''}"
                                                    data-cart-add
                                                    data-id="${item.id}"
                                                    data-name="${item.name.replace(/"/g, '&quot;')}"
                                                    data-category="${(item.category || '-').replace(/"/g, '&quot;')}"
                                                    data-sku="${(item.sku || '-').replace(/"/g, '&quot;')}"
                                                    data-stock="${item.stock || 0}"
                                                    data-sale="${formatMoney(salePrice)}"
                                                    data-cost="${formatMoney(costPrice)}"
                                                    data-image="${item.image || ''}"
                                                >${isInCart ? `{{ __('Remove from Cart') }}` : `{{ __('Add to Cart') }}`}</button>
                                            </div>
                                        </div>
                                    </article>
                                `;
                            })()}
                        `).join('')}
                    </div>
                `;
            };

            const addToCart = async (button) => {
                if (!requireShopAuth()) {
                    return;
                }

                const items = readCart();
                const id = Number(button.getAttribute('data-id'));
                const existing = items.find((item) => item.id === id);
                const qtySource = button.getAttribute('data-qty-source');
                const initialQty = Math.max(1, Number(qtySource ? document.getElementById(qtySource)?.value || 1 : 1));
                const stockQty = Math.max(0, Number(button.getAttribute('data-stock') || 0));

                if (stockQty < 1) {
                    showToast('warning', `{{ __('Out of Stock') }}`, `{{ __('This product is currently out of stock.') }}`);
                    return;
                }

                if (initialQty > stockQty) {
                    showToast('warning', `{{ __('Stock Limit Reached') }}`, `{{ __('You cannot add quantity over current stock.') }}`);
                    return;
                }

                if (existing) {
                    try {
                        await fetchJson(shopCartToggleUrl, {
                            method: 'POST',
                            body: JSON.stringify({
                                product_id: id,
                            }),
                        });
                    } catch (error) {
                        showToast('warning', `{{ __('Cart Update Failed') }}`, getErrorMessage(error, `{{ __('Unable to update cart right now.') }}`));
                        return;
                    }
                    await refreshShopState();
                    showToast('warning', `{{ __('Removed from cart') }}`, `{{ __('This product was removed from your shopping cart.') }}`);
                } else {
                    try {
                        await fetchJson(shopCartToggleUrl, {
                            method: 'POST',
                            body: JSON.stringify({
                                product_id: id,
                                qty: initialQty,
                            }),
                        });
                    } catch (error) {
                        showToast('warning', `{{ __('Stock Limit Reached') }}`, getErrorMessage(error, `{{ __('You cannot add quantity over current stock.') }}`));
                        return;
                    }
                    await refreshShopState();
                    showToast('success', `{{ __('Added to cart') }}`, `{{ __('This product was added to your shopping cart.') }}`);
                }
            };

            const toggleFavorite = async (button) => {
                if (!requireShopAuth()) {
                    return;
                }

                const id = Number(button.getAttribute('data-id'));
                const existing = readFavorites().find((item) => item.id === id);

                if (existing) {
                    await fetchJson(shopFavoriteToggleUrl, {
                        method: 'POST',
                        body: JSON.stringify({
                            product_id: id,
                        }),
                    });
                    await refreshShopState();
                    showToast('warning', `{{ __('Removed from favorites') }}`, `{{ __('This product was removed from your favorite list.') }}`);
                    return;
                }

                await fetchJson(shopFavoriteToggleUrl, {
                    method: 'POST',
                    body: JSON.stringify({
                        product_id: id,
                    }),
                });
                await refreshShopState();
                showToast('success', `{{ __('Added to favorites') }}`, `{{ __('This product was added to your favorite list.') }}`);
            };

            document.querySelectorAll('[data-cart-toggle]').forEach((button) => button.addEventListener('click', openCart));
            document.querySelectorAll('[data-favorite-open]').forEach((button) => button.addEventListener('click', openFavorites));
            document.querySelectorAll('[data-cart-close], [data-favorite-close]').forEach((button) => button.addEventListener('click', closePanels));
            cartBackdrop.addEventListener('click', closePanels);

            document.querySelectorAll('[data-scroll-top]').forEach((button) => {
                button.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
            });

            document.querySelectorAll('[data-copy-share]').forEach((button) => {
                button.addEventListener('click', async (event) => {
                    event.preventDefault();
                    const url = button.getAttribute('data-copy-share') || window.location.href;

                    try {
                        await navigator.clipboard.writeText(url);
                        showToast('success', `{{ __('Link Copied') }}`, `{{ __('Product link copied successfully.') }}`);
                    } catch (error) {
                        showToast('warning', `{{ __('Copy Failed') }}`, `{{ __('Please copy the link manually from the browser address bar.') }}`);
                    }
                });
            });

            document.querySelectorAll('[data-cart-checkout]').forEach((button) => {
                button.addEventListener('click', () => {
                    const items = readCart();
                    if (!items.length) {
                        return;
                    }

                    alert(`{{ __('Checkout summary') }}\n${items.map((item) => `${item.name} x ${item.qty} = ${formatMoney(item.salePrice * item.qty)}`).join('\n')}\n\n{{ __('Total') }}: ${formatMoney(items.reduce((sum, item) => sum + (item.qty * item.salePrice), 0))}`);
                });
            });

            document.querySelectorAll('[data-detail-qty]').forEach((button) => {
                button.addEventListener('click', () => {
                    const target = document.getElementById(button.getAttribute('data-target'));
                    if (!target) {
                        return;
                    }

                    const direction = button.getAttribute('data-detail-qty');
                    let next = Number(target.value || 1);
                    const maxStock = Math.max(0, Number(button.getAttribute('data-max-stock') || 0));

                    if (direction === 'plus') {
                        if (maxStock > 0 && next >= maxStock) {
                            showToast('warning', `{{ __('Stock Limit Reached') }}`, `{{ __('You cannot add quantity over current stock.') }}`);
                            return;
                        }

                        next += 1;
                    } else {
                        next = Math.max(1, next - 1);
                    }

                    target.value = String(next);
                    const label = document.querySelector(`[data-qty-label="${target.id}"]`);
                    if (label) {
                        label.textContent = String(next);
                    }
                });
            });

            refreshShopState();

            document.addEventListener('click', async (event) => {
                const addButton = event.target.closest('[data-cart-add]');
                if (addButton) {
                    addToCart(addButton);
                    return;
                }

                const favoriteButton = event.target.closest('[data-favorite-toggle]');
                if (favoriteButton) {
                    toggleFavorite(favoriteButton);
                    return;
                }

                const favoriteProductCard = event.target.closest('[data-favorite-product-card]');
                if (favoriteProductCard && !event.target.closest('button')) {
                    const url = favoriteProductCard.getAttribute('data-product-url');
                    if (url) {
                        window.location.href = url;
                    }
                    return;
                }

                const qtyButton = event.target.closest('[data-cart-qty]');
                if (qtyButton) {
                    const id = Number(qtyButton.getAttribute('data-id'));
                    const action = qtyButton.getAttribute('data-cart-qty');
                    const stockQty = Math.max(0, Number(qtyButton.getAttribute('data-stock') || 0));
                    const currentItem = readCart().find((entry) => entry.id === id);

                    if (action === 'plus' && currentItem && currentItem.qty >= stockQty) {
                        showToast('warning', `{{ __('Stock Limit Reached') }}`, `{{ __('You cannot add quantity over current stock.') }}`);
                        return;
                    }

                    try {
                        await fetchJson(shopCartQtyUrl, {
                            method: 'POST',
                            body: JSON.stringify({
                                product_id: id,
                                action,
                            }),
                        });
                    } catch (error) {
                        showToast('warning', `{{ __('Cart Update Failed') }}`, getErrorMessage(error, `{{ __('Unable to update cart right now.') }}`));
                        return;
                    }
                    await refreshShopState();
                    showToast('info', `{{ __('Cart updated') }}`, action === 'plus' ? `{{ __('Quantity increased successfully.') }}` : `{{ __('Quantity decreased successfully.') }}`);
                    return;
                }

                const removeButton = event.target.closest('[data-cart-remove]');
                if (removeButton) {
                    const id = Number(removeButton.getAttribute('data-cart-remove'));
                    await fetchJson(shopCartQtyUrl, {
                        method: 'POST',
                        body: JSON.stringify({
                            product_id: id,
                            action: 'remove',
                        }),
                    });
                    await refreshShopState();
                    showToast('warning', `{{ __('Removed from cart') }}`, `{{ __('This product was removed from your shopping cart.') }}`);
                    return;
                }

                if (results) {
                    const paginationLink = event.target.closest('[data-shop-results] .web-page-btn[href]');
                    if (paginationLink) {
                        event.preventDefault();

                        try {
                            const response = await fetch(paginationLink.href, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                            });

                            if (!response.ok) {
                                window.location.href = paginationLink.href;
                                return;
                            }

                            const html = await response.text();
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const nextResults = doc.querySelector('[data-shop-results]');

                            if (!nextResults) {
                                window.location.href = paginationLink.href;
                                return;
                            }

                            results.innerHTML = nextResults.innerHTML;
                            syncCardButtons();
                            syncFavoriteButtons();
                            renderFavorites();
                            window.scrollTo({
                                top: results.getBoundingClientRect().top + window.scrollY - 140,
                                behavior: 'smooth',
                            });
                        } catch (error) {
                            window.location.href = paginationLink.href;
                        }
                    }
                }
            });
        });
    </script>
@endpush
