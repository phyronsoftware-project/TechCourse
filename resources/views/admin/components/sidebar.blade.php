@php
    $menuGroups = [
        [
            'title' => null,
            'links' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'home',
                    'route' => route('admin.dashboard'),
                    'active' => request()->routeIs('admin.dashboard'),
                ],
                [
                    'label' => 'Course Management',
                    'icon' => 'book',
                    'children' => [
                        [
                            'label' => 'Categories',
                            'icon' => 'grid',
                            'route' => route('admin.categories.index'),
                            'active' => request()->routeIs('admin.categories.*'),
                        ],
                        [
                            'label' => 'Courses',
                            'icon' => 'book',
                            'route' => route('admin.courses.index'),
                            'active' => request()->routeIs('admin.courses.*'),
                        ],
                    ],
                ],
                [
                    'label' => 'User Management',
                    'icon' => 'users',
                    'children' => [
                        [
                            'label' => 'Users',
                            'icon' => 'user',
                            'route' => route('admin.users.index'),
                            'active' => request()->routeIs('admin.users.*'),
                        ],
                        [
                            'label' => 'Enrollments',
                            'icon' => 'check',
                            'route' => route('admin.enrollments.index'),
                            'active' => request()->routeIs('admin.enrollments.*'),
                        ],
                    ],
                ],
                [
                    'label' => 'Shopping',
                    'icon' => 'ticket',
                    'children' => [
                        [
                            'label' => 'Shop Categories',
                            'icon' => 'grid',
                            'route' => route('admin.shop-categories.index'),
                            'active' => request()->routeIs('admin.shop-categories.*'),
                        ],
                        [
                            'label' => 'Shop Products',
                            'icon' => 'bag',
                            'route' => route('admin.shop-products.index'),
                            'active' => request()->routeIs('admin.shop-products.*'),
                        ],
                        [
                            'label' => 'Shop Orders',
                            'icon' => 'ticket',
                            'route' => route('admin.shop-orders.index'),
                            'active' => request()->routeIs('admin.shop-orders.*'),
                        ],
                        [
                            'label' => 'Shop Payments',
                            'icon' => 'wallet',
                            'route' => route('admin.shop-payments.index'),
                            'active' => request()->routeIs('admin.shop-payments.*'),
                        ],
                    ],
                ],
                [
                    'label' => 'Course Sales',
                    'icon' => 'wallet',
                    'children' => [
                        [
                            'label' => 'Course Orders',
                            'icon' => 'ticket',
                            'route' => route('admin.orders.index'),
                            'active' => request()->routeIs('admin.orders.*'),
                        ],
                        [
                            'label' => 'Course Payments',
                            'icon' => 'wallet',
                            'route' => route('admin.payments.index'),
                            'active' => request()->routeIs('admin.payments.*'),
                        ],
                    ],
                ],
                [
                    'label' => 'Content',
                    'icon' => 'star',
                    'children' => [
                        [
                            'label' => 'Reviews',
                            'icon' => 'star',
                            'route' => route('admin.reviews.index'),
                            'active' => request()->routeIs('admin.reviews.*'),
                        ],
                    ],
                ],
                [
                    'label' => 'Web Management',
                    'icon' => 'globe',
                    'children' => [
                        [
                            'label' => 'Web Banners',
                            'icon' => 'image',
                            'route' => route('admin.banners.index', ['platform' => 'web']),
                            'active' => request()->routeIs('admin.banners.*') && request('platform', 'web') === 'web',
                        ],
                        [
                            'label' => 'Social Media',
                            'icon' => 'share',
                            'route' => route('admin.social-media.index', ['platform' => 'web']),
                            'active' => request()->routeIs('admin.social-media.*'),
                        ],
                    ],
                ],
                [
                    'label' => 'App Management',
                    'icon' => 'mobile',
                    'children' => [
                        [
                            'label' => 'App Banners',
                            'icon' => 'image',
                            'route' => route('admin.banners.index', ['platform' => 'app']),
                            'active' => request()->routeIs('admin.banners.*') && request('platform') === 'app',
                        ],
                    ],
                ],
                [
                    'label' => 'System',
                    'icon' => 'shield',
                    'children' => [
                        [
                            'label' => 'Reports',
                            'icon' => 'chart',
                            'route' => route('admin.reports.index'),
                            'active' => request()->routeIs('admin.reports.*'),
                        ],
                        [
                            'label' => 'Settings',
                            'icon' => 'gear',
                            'route' => route('admin.settings.index'),
                            'active' => request()->routeIs('admin.settings.*'),
                        ],
                        [
                            'label' => 'Notifications',
                            'icon' => 'bell',
                            'route' => route('admin.notifications.index'),
                            'active' => request()->routeIs('admin.notifications.*'),
                        ],
                    ],
                ],
                [
                    'label' => 'Business Tools',
                    'icon' => 'toolbox',
                    'children' => [
                        [
                            'label' => 'Sound Tool',
                            'icon' => 'mic',
                            'route' => route('admin.tools.sound'),
                            'active' => request()->routeIs('admin.tools.sound'),
                        ],
                    ],
                ],
            ],
        ],
    ];
@endphp

<div class="dashboard-sidebar-backdrop fixed inset-0 z-30 hidden bg-slate-950/40 backdrop-blur-sm lg:hidden"
    data-sidebar-backdrop></div>

<aside
    class="dashboard-sidebar fixed inset-y-0 left-0 z-40 flex w-[234px] -translate-x-full flex-col border-r border-[#d9e2ef] bg-white text-[#183b78] transition-transform duration-300 lg:translate-x-0"
    data-sidebar>
    <div class="flex items-center justify-between border-b border-[#e5edf6] px-5 py-5">
        <a href="{{ route('admin.dashboard') }}" class="block">
            <h1 class="text-[1.35rem] font-bold leading-none text-[#163f86]">Business System</h1>
            <p class="mt-1 text-[0.74rem] tracking-[0.08em] text-[#6b7c98]">ADMIN DASHBOARD</p>
        </a>

        <button type="button"
            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-[#d9e2ef] bg-white text-[#37507b] lg:hidden"
            data-sidebar-close aria-label="Close sidebar">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" d="M6 6l12 12M18 6 6 18" />
            </svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-0 py-2">
        @foreach ($menuGroups as $group)
            <div class="space-y-0">
                @foreach ($group['links'] as $link)
                    @php
                        $hasChildren = !empty($link['children']);
                        $isActive = $link['active'] ?? false;
                        $hasActiveChild =
                            $hasChildren &&
                            collect($link['children'])->contains(fn($child) => !empty($child['active']));
                        $isExpanded = $hasActiveChild;
                    @endphp

                    @if ($hasChildren)
                        <div>
                            <button type="button"
                                class="sidebar-trigger flex min-h-[46px] w-full items-center justify-between border-b border-[#edf2f8] px-5 py-2.5 text-left text-[14px] font-medium transition-colors duration-200 {{ $isExpanded ? 'is-active bg-[#f4f8fd] text-[#173f87]' : 'bg-white text-[#173f87] hover:bg-[#f8fbff]' }}"
                                data-submenu-toggle aria-expanded="{{ $isExpanded ? 'true' : 'false' }}">
                                <span class="flex items-center gap-3">
                                    <span class="inline-flex h-6 w-6 items-center justify-center text-[#173f87]">
                                        @switch($link['icon'] ?? 'home')
                                            @case('book')
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M4 5.75A2.75 2.75 0 0 1 6.75 3H20v15.25A2.75 2.75 0 0 0 17.25 21H7a3 3 0 0 1-3-3V5.75Zm3.25-1.25A1.25 1.25 0 0 0 6 5.75V18a1.5 1.5 0 0 0 1.5 1.5h9.75a1.25 1.25 0 0 1 1.25-1.25H7.75A2.75 2.75 0 0 1 5 15.5V5.75c0-.45.11-.88.3-1.25h1.95Z"/></svg>
                                                @break
                                            @case('users')
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM8 12a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm8 1c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5ZM8 13c-2.67 0-8 1.34-8 4v2h7v-2.5c0-.95.36-1.82 1.02-2.56.13-.14.27-.28.42-.4-.48-.03-.95-.04-1.44-.04Z"/></svg>
                                                @break
                                            @case('ticket')
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M3 7.5A2.5 2.5 0 0 1 5.5 5h13A2.5 2.5 0 0 1 21 7.5V10a2 2 0 1 0 0 4v2.5a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 16.5V14a2 2 0 1 0 0-4V7.5Z"/></svg>
                                                @break
                                            @case('star')
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="m12 17.27 6.18 3.73-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                                @break
                                            @case('globe')
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm6.92 9h-3.08a15.7 15.7 0 0 0-1.3-5.01A8.03 8.03 0 0 1 18.92 11ZM12 4.04c.93 1.13 1.9 3.3 2.26 6.96H9.74C10.1 7.34 11.07 5.17 12 4.04ZM4.26 13h3.1c.14 1.8.54 3.56 1.19 5.01A8.03 8.03 0 0 1 4.26 13Zm3.1-2h-3.1a8.03 8.03 0 0 1 4.29-5.01A15.74 15.74 0 0 0 7.36 11ZM12 19.96c-.93-1.13-1.9-3.3-2.26-6.96h4.52c-.36 3.66-1.33 5.83-2.26 6.96ZM15.64 13h3.08a8.03 8.03 0 0 1-4.28 5.01c.65-1.45 1.05-3.21 1.2-5.01Z"/></svg>
                                                @break
                                            @case('mobile')
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M8 2.75A2.75 2.75 0 0 0 5.25 5.5v13A2.75 2.75 0 0 0 8 21.25h8A2.75 2.75 0 0 0 18.75 18.5v-13A2.75 2.75 0 0 0 16 2.75H8Zm4 15.5a1.25 1.25 0 1 1 0-2.5 1.25 1.25 0 0 1 0 2.5Z"/></svg>
                                                @break
                                            @case('shield')
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 4 5v6c0 5.55 3.84 10.74 8 12 4.16-1.26 8-6.45 8-12V5l-8-3Zm0 10.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5ZM8 17c.32-2.3 1.94-3.5 4-3.5s3.68 1.2 4 3.5H8Z"/></svg>
                                                @break
                                            @case('toolbox')
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M9 3a2 2 0 0 0-2 2v2H5.5A2.5 2.5 0 0 0 3 9.5v8A2.5 2.5 0 0 0 5.5 20h13a2.5 2.5 0 0 0 2.5-2.5v-8A2.5 2.5 0 0 0 18.5 7H17V5a2 2 0 0 0-2-2H9Zm6 4H9V5h6v2Zm-5 5H8v2h2v2h2v-2h2v-2h-2v-2h-2v2Z"/></svg>
                                                @break
                                            @default
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3.17 4 9v11h5v-6h6v6h5V9l-8-5.83Z"/></svg>
                                        @endswitch
                                    </span>
                                    <span>{{ $link['label'] }}</span>
                                </span>
                                <svg class="submenu-arrow h-4 w-4 text-[#8ca0c0] transition-transform duration-200"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                                </svg>
                            </button>

                            <div class="submenu overflow-hidden {{ $isExpanded ? 'is-open' : '' }}" data-submenu>
                                <div class="space-y-0 border-b border-[#edf2f8] bg-[#fbfdff]">
                                    @foreach ($link['children'] as $child)
                                        <a href="{{ $child['route'] }}"
                                            class="flex min-h-[46px] items-center gap-3 px-5 py-2.5 text-[14px] transition-colors duration-200 {{ !empty($child['active']) ? 'bg-[#edf4ff] font-semibold text-[#173f87]' : 'text-[#5f7291] hover:bg-[#f4f8fd] hover:text-[#173f87]' }}">
                                            <span class="inline-flex h-6 w-6 items-center justify-center">
                                                @switch($child['icon'] ?? 'grid')
                                                    @case('book')
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M4 5.75A2.75 2.75 0 0 1 6.75 3H20v15.25A2.75 2.75 0 0 0 17.25 21H7a3 3 0 0 1-3-3V5.75Zm3.25-1.25A1.25 1.25 0 0 0 6 5.75V18a1.5 1.5 0 0 0 1.5 1.5h9.75a1.25 1.25 0 0 1 1.25-1.25H7.75A2.75 2.75 0 0 1 5 15.5V5.75c0-.45.11-.88.3-1.25h1.95Z"/></svg>
                                                        @break
                                                    @case('user')
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"/></svg>
                                                        @break
                                                    @case('check')
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="m9.55 18.2-5.3-5.3 1.4-1.4 3.9 3.9 8.8-8.8 1.4 1.4-10.2 10.2Z"/></svg>
                                                        @break
                                                    @case('ticket')
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M3 7.5A2.5 2.5 0 0 1 5.5 5h13A2.5 2.5 0 0 1 21 7.5V10a2 2 0 1 0 0 4v2.5a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 16.5V14a2 2 0 1 0 0-4V7.5Z"/></svg>
                                                        @break
                                                    @case('wallet')
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M3 7a3 3 0 0 1 3-3h11v2H6a1 1 0 0 0 0 2h13a2 2 0 0 1 2 2v7a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7Zm13 6a1.5 1.5 0 1 0 0 3h3v-3h-3Z"/></svg>
                                                        @break
                                                    @case('bag')
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 7V6a6 6 0 1 1 12 0v1h1.5A1.5 1.5 0 0 1 21 8.5v10A2.5 2.5 0 0 1 18.5 21h-13A2.5 2.5 0 0 1 3 18.5v-10A1.5 1.5 0 0 1 4.5 7H6Zm2 0h8V6a4 4 0 1 0-8 0v1Z"/></svg>
                                                        @break
                                                    @case('grid')
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M4 4h7v7H4V4Zm9 0h7v7h-7V4ZM4 13h7v7H4v-7Zm9 0h7v7h-7v-7Z"/></svg>
                                                        @break
                                                    @case('star')
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="m12 17.27 6.18 3.73-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                                        @break
                                                    @case('image')
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M5 4a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H5Zm2 3a2 2 0 1 1 0 4 2 2 0 0 1 0-4Zm12 10H5l4.5-5.5 3.2 3.8 2.3-2.8L19 17Z"/></svg>
                                                        @break
                                                    @case('share')
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M18 16a3 3 0 0 0-2.39 1.2l-6.9-3.45a3.26 3.26 0 0 0 0-1.5l6.9-3.45A3 3 0 1 0 15 7a2.9 2.9 0 0 0 .08.67l-6.92 3.46a3 3 0 1 0 0 5.74l6.92 3.46A2.9 2.9 0 0 0 15 20a3 3 0 1 0 3-4Z"/></svg>
                                                        @break
                                                    @case('gear')
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="m19.14 12.94.04-.94-.04-.94 2.03-1.58a.5.5 0 0 0 .12-.64l-1.92-3.32a.5.5 0 0 0-.6-.22l-2.39.96a7.03 7.03 0 0 0-1.63-.94L14.4 2.8a.5.5 0 0 0-.49-.4h-3.82a.5.5 0 0 0-.49.4L9.25 5.32c-.57.23-1.12.54-1.63.94l-2.39-.96a.5.5 0 0 0-.6.22L2.71 8.84a.5.5 0 0 0 .12.64l2.03 1.58-.04.94.04.94-2.03 1.58a.5.5 0 0 0-.12.64l1.92 3.32a.5.5 0 0 0 .6.22l2.39-.96c.5.4 1.05.71 1.63.94l.35 2.52a.5.5 0 0 0 .49.4h3.82a.5.5 0 0 0 .49-.4l.35-2.52c.57-.23 1.12-.54 1.63-.94l2.39.96a.5.5 0 0 0 .6-.22l1.92-3.32a.5.5 0 0 0-.12-.64l-2.03-1.58ZM12 15.5A3.5 3.5 0 1 1 12 8a3.5 3.5 0 0 1 0 7.5Z"/></svg>
                                                        @break
                                                    @case('bell')
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a6 6 0 0 0-6 6v2.16c0 .54-.14 1.08-.4 1.55L4.1 14.6A1.5 1.5 0 0 0 5.4 17h13.2a1.5 1.5 0 0 0 1.3-2.25l-1.5-2.89c-.26-.47-.4-1.01-.4-1.55V8a6 6 0 0 0-6-6Zm0 20a3 3 0 0 0 2.83-2H9.17A3 3 0 0 0 12 22Z"/></svg>
                                                        @break
                                                    @case('chart')
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M5 3h2v18H5V3Zm6 6h2v12h-2V9Zm6-4h2v16h-2V5Z"/></svg>
                                                        @break
                                                    @case('mic')
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 14a3 3 0 0 0 3-3V7a3 3 0 1 0-6 0v4a3 3 0 0 0 3 3Zm5-3a1 1 0 1 1 2 0 7 7 0 0 1-6 6.92V21h3a1 1 0 1 1 0 2H8a1 1 0 1 1 0-2h3v-3.08A7 7 0 0 1 5 11a1 1 0 1 1 2 0 5 5 0 1 0 10 0Z"/></svg>
                                                        @break
                                                    @default
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M4 4h7v7H4V4Zm9 0h7v7h-7V4ZM4 13h7v7H4v-7Zm9 0h7v7h-7v-7Z"/></svg>
                                                @endswitch
                                            </span>
                                            <span>{{ $child['label'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ $link['route'] }}"
                            class="flex min-h-[46px] items-center gap-3 border-b border-[#edf2f8] px-5 py-2.5 text-[14px] font-medium transition-all duration-200 {{ $isActive ? 'bg-[#f4f8fd] text-[#173f87]' : 'bg-white text-[#173f87] hover:bg-[#f8fbff]' }}">
                            <span class="inline-flex h-6 w-6 items-center justify-center text-[#173f87]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3.17 4 9v11h5v-6h6v6h5V9l-8-5.83Z"/></svg>
                            </span>
                            <span class="flex-1">{{ $link['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        @endforeach
    </nav>

    <div class="border-t border-[#e5edf6] px-4 py-4">
        <p class="text-center text-[0.72rem] text-[#8ea1be]">© 2026 TechCourse</p>
    </div>
</aside>
