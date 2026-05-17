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
        ['label' => __('About Us'), 'href' => route('about'), 'active' => request()->routeIs('about')],
        ['label' => __('Contact'), 'href' => route('contact'), 'active' => request()->routeIs('contact')],
    ];

    $authUser = auth()->user();
    $userInitial = strtoupper(mb_substr($authUser?->name ?? 'U', 0, 1));
@endphp

<header>
    <div class="header-box">
        <div class="logo">
            <div class="logo-icon-box">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>

            <h3 class="logo-title">
                <a href="{{ route('home') }}" class="brand-link">
                    Tech<span>Course</span>
                </a>
            </h3>
        </div>

        <button type="button" class="mobile-menu-icon" data-web-menu-toggle aria-label="Open menu">
            <i class="fa-solid fa-bars"></i>
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
                                    </span>
                                </a>
                            @endif

                            <form action="{{ route('web.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="header-auth-btn header-auth-btn-logout" aria-label="{{ __('Logout') }}" title="{{ __('Logout') }}">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                </button>
                            </form>
                        @endauth

                        @guest
                            <a href="{{ route('web.login') }}" class="header-auth-btn header-auth-btn-register" data-web-menu-close aria-label="{{ __('Login') }}" title="{{ __('Login') }}">
                                <i class="fa-solid fa-user-plus"></i>
                            </a>
                        @endguest
                    </div>
                </li>
            </ul>
        </nav>
    </div>
</header>
