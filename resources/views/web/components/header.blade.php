@php
    $locale = app()->getLocale() === 'km' ? 'km' : 'en';
    $languages = [
        [
            'code' => 'en',
            'label' => __('English'),
            'flag' => asset('flags_language/us.png'),
        ],
        [
            'code' => 'km',
            'label' => __('Khmer'),
            'flag' => asset('flags_language/khmer.png'),
        ],
    ];

    $currentLanguage = collect($languages)->firstWhere('code', $locale) ?? $languages[0];

    $navItems = [
        ['label' => __('Home'), 'href' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => __('Course'), 'href' => route('courses.index'), 'active' => request()->routeIs('courses.*')],
        ['label' => __('Shop'), 'href' => route('shop.index'), 'active' => request()->routeIs('shop.*')],
        ['label' => __('About Us'), 'href' => route('about'), 'active' => request()->routeIs('about')],
    ];

    $authUser = auth()->user();
    $userInitial = strtoupper(mb_substr($authUser?->name ?? 'U', 0, 1));
@endphp

<header>
    <div class="header-box">
        <div class="logo">
            <a href="{{ route('home') }}" class="brand-logo" aria-label="TechCourse">
                <img src="{{ asset('logo/logo.png') }}" alt="TechCourse" class="brand-logo__image">
                <span class="brand-logo__text">Tech<span>Course</span></span>
            </a>
        </div>

        <button type="button" class="mobile-menu-icon" data-web-menu-toggle aria-label="{{ __('Open menu') }}">
            <span class="mobile-menu-icon__ring">
                <span class="mobile-menu-icon__line"></span>
                <span class="mobile-menu-icon__line"></span>
                <span class="mobile-menu-icon__line"></span>
            </span>
        </button>

        <nav class="navbar offcanvas-right" aria-label="Main navigation">
            <div class="drawer__header">
                <button type="button" class="drawer__back" data-web-menu-close-button>
                    <i class="fa-solid fa-chevron-right"></i>
                    <span>{{ __('Back') }}</span>
                </button>
            </div>

            <ul class="drawer__menu">
                @foreach ($navItems as $item)
                    <li class="menu-item">
                        <a
                            href="{{ $item['href'] }}"
                            class="{{ $item['active'] ? 'active-link' : '' }}"
                            data-web-menu-close
                        >
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach

                <li>
                    <div class="lang-container" data-web-lang>
                        <button type="button" class="lang-toggle" data-web-lang-toggle aria-expanded="false">
                            <span class="lang-toggle__left">
                                <img src="{{ $currentLanguage['flag'] }}" alt="{{ $currentLanguage['label'] }}" class="lang-flag">
                                <span>{{ $currentLanguage['label'] }}</span>
                            </span>
                            <i class="fa-solid fa-caret-down dropdown-caret"></i>
                        </button>

                        <ul class="lang-dropdown-menu">
                            @foreach ($languages as $language)
                                <li>
                                    <a href="{{ route('language.switch', $language['code']) }}">
                                        <img src="{{ $language['flag'] }}" alt="{{ $language['label'] }}" class="lang-flag">
                                        <span>{{ $language['label'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </li>

                <li class="auth-item">
                    <div class="header-auth-actions">
                        @auth
                            @if (in_array($authUser?->role, ['admin', 'super_admin'], true))
                                <a href="{{ route('admin.dashboard') }}" class="header-auth-user" data-web-menu-close>
                                    <span class="header-auth-user__avatar">
                                        @if ($authUser?->avatar_url)
                                            <img src="{{ $authUser->avatar_url }}" alt="{{ $authUser?->name }}" class="header-auth-user__avatar-image">
                                        @else
                                            {{ $userInitial }}
                                        @endif
                                    </span>
                                    <span class="header-auth-user__content">
                                        <strong>{{ $authUser?->name }}</strong>
                                        <span>{{ $authUser?->email }}</span>
                                    </span>
                                </a>
                            @else
                                <a href="{{ route('profile.show') }}" class="header-auth-user" data-web-menu-close>
                                    <span class="header-auth-user__avatar">
                                        @if ($authUser?->avatar_url)
                                            <img src="{{ $authUser->avatar_url }}" alt="{{ $authUser?->name }}" class="header-auth-user__avatar-image">
                                        @else
                                            {{ $userInitial }}
                                        @endif
                                    </span>
                                    <span class="header-auth-user__content">
                                        <strong>{{ $authUser?->name }}</strong>
                                        <span>{{ $authUser?->email }}</span>
                                    </span>
                                </a>
                            @endif

                            <span class="header-auth-divider" aria-hidden="true"></span>

                            <div class="header-notification" data-web-notification>
                                <button
                                    type="button"
                                    class="header-auth-btn header-auth-btn-icon header-auth-btn-notification"
                                    data-web-notification-toggle
                                    data-web-notification-read-url="{{ route('web.notifications.read-all') }}"
                                    data-web-notification-csrf="{{ csrf_token() }}"
                                    aria-expanded="false"
                                    aria-label="{{ __('Notifications') }}"
                                    title="{{ __('Notifications') }}"
                                >
                                    <i class="fa-regular fa-bell"></i>
                                    @if (($headerNotificationUnreadCount ?? 0) > 0)
                                        <span class="header-notification__badge" data-web-notification-badge>{{ $headerNotificationUnreadCount > 99 ? '99+' : $headerNotificationUnreadCount }}</span>
                                    @endif
                                </button>

                                <div class="header-notification__panel" data-web-notification-panel hidden>
                                    <div class="header-notification__panel-head">
                                        <strong>{{ __('Notifications') }}</strong>
                                        <span>{{ __('New updates will appear here.') }}</span>
                                    </div>

                                    @if (($headerNotifications ?? collect())->isNotEmpty())
                                        <div class="header-notification__list">
                                            @foreach ($headerNotifications as $notification)
                                                @if ($notification->link_url)
                                                    <a href="{{ $notification->link_url }}" class="header-notification__item {{ !($notification->is_read ?? false) ? 'is-unread' : '' }}" data-web-menu-close>
                                                        <span class="header-notification__item-icon header-notification__item-icon--{{ $notification->style }}">
                                                            <i class="fa-solid fa-bell"></i>
                                                        </span>
                                                        <span class="header-notification__item-content">
                                                            <span class="header-notification__item-head">
                                                                <strong>{{ $notification->title }}</strong>
                                                                @if ($notification->created_at)
                                                                    <small class="header-notification__item-time">{{ $notification->created_at->diffForHumans() }}</small>
                                                                @endif
                                                            </span>
                                                            <span class="header-notification__item-message">{{ $notification->message }}</span>
                                                        </span>
                                                    </a>
                                                @else
                                                    <div class="header-notification__item {{ !($notification->is_read ?? false) ? 'is-unread' : '' }}">
                                                        <span class="header-notification__item-icon header-notification__item-icon--{{ $notification->style }}">
                                                            <i class="fa-solid fa-bell"></i>
                                                        </span>
                                                        <span class="header-notification__item-content">
                                                            <span class="header-notification__item-head">
                                                                <strong>{{ $notification->title }}</strong>
                                                                @if ($notification->created_at)
                                                                    <small class="header-notification__item-time">{{ $notification->created_at->diffForHumans() }}</small>
                                                                @endif
                                                            </span>
                                                            <span class="header-notification__item-message">{{ $notification->message }}</span>
                                                        </span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="header-notification__empty">
                                            <span class="header-notification__empty-icon">
                                                <i class="fa-regular fa-bell-slash"></i>
                                            </span>
                                            <strong>{{ __('No notifications yet') }}</strong>
                                            <p>{{ __('You do not have any notifications right now.') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <form action="{{ route('web.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="header-auth-btn header-auth-btn-logout" aria-label="{{ __('Logout') }}" title="{{ __('Logout') }}">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                    <span class="header-auth-btn__label">{{ __('Logout') }}</span>
                                </button>
                            </form>
                        @endauth

                        @guest
                            <a href="{{ route('web.login') }}" class="header-auth-btn header-auth-btn-register" data-web-menu-close aria-label="{{ __('Login / Register') }}" title="{{ __('Login / Register') }}">
                                <i class="fa-solid fa-user" aria-hidden="true"></i>
                                <span class="header-auth-btn__label">{{ __('Login') }}</span>
                            </a>
                        @endguest
                    </div>
                </li>
            </ul>
        </nav>
    </div>
</header>
