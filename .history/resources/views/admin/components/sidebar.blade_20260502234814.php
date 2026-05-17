@php
    $menuGroups = [
        [
            'title' => null,
            'links' => [
                [
                    'label' => 'Dashboard',
                    'route' => route('admin.dashboard'),
                    'active' => request()->routeIs('admin.dashboard'),
                ],
                [
                    'label' => 'Course Management',
                    'children' => [
                        ['label' => 'Categories', 'route' => route('admin.categories.index'), 'active' => request()->routeIs('admin.categories.*')],
                        ['label' => 'Courses', 'route' => route('admin.courses.index'), 'active' => request()->routeIs('admin.courses.*')],
                    ],
                ],
                [
                    'label' => 'User Management',
                    'children' => [
                        ['label' => 'Users', 'route' => route('admin.users.index'), 'active' => request()->routeIs('admin.users.*')],
                        ['label' => 'Enrollments', 'route' => route('admin.enrollments.index'), 'active' => request()->routeIs('admin.enrollments.*')],
                    ],
                ],
                [
                    'label' => 'Sales',
                    'children' => [
                        ['label' => 'Orders', 'route' => route('admin.orders.index'), 'active' => request()->routeIs('admin.orders.*')],
                        ['label' => 'Payments', 'route' => route('admin.payments.index'), 'active' => request()->routeIs('admin.payments.*')],
                    ],
                ],
                [
                    'label' => 'Content',
                    'children' => [
                        ['label' => 'Reviews', 'route' => route('admin.reviews.index'), 'active' => request()->routeIs('admin.reviews.*')],
                    ],
                ],
                [
                    'label' => 'Website Management',
                    'children' => [
                        ['label' => 'Banners', 'route' => route('admin.reports.index'), 'active' => request()->routeIs('admin.reports.*')],
                        ['label' => 'Settings', 'route' => route('admin.settings.index'), 'active' => request()->routeIs('admin.settings.*')],
                    ],
                ],
                [
                    'label' => 'App Management',
                    'children' => [
                        ['label' => 'Banners', 'route' => route('admin.reports.index'), 'active' => request()->routeIs('admin.reports.*')],
                        ['label' => 'Settings', 'route' => route('admin.settings.index'), 'active' => request()->routeIs('admin.settings.*')],
                    ],
                ],
            ],
        ],
    ];
@endphp

<div class="dashboard-sidebar-backdrop fixed inset-0 z-30 hidden bg-slate-950/40 backdrop-blur-sm lg:hidden" data-sidebar-backdrop></div>

<aside class="dashboard-sidebar fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-[#151515] bg-[#050505] text-white transition-transform duration-300 lg:translate-x-0" data-sidebar>
    <div class="flex items-center justify-between border-b border-[#151515] px-6 py-7">
        <a href="{{ route('admin.dashboard') }}" class="block">
            <h1 class="text-[2.1rem] font-bold leading-none text-cyan-600">TechCourse</h1>
            <p class="mt-2 text-[1.02rem] text-white/50">Admin Dashboard</p>
        </a>

        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-white/70 lg:hidden" data-sidebar-close aria-label="Close sidebar">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" d="M6 6l12 12M18 6 6 18" />
            </svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-4 py-6">
        @foreach ($menuGroups as $group)
            <div class="space-y-2">
                @foreach ($group['links'] as $link)
                    @php
                        $hasChildren = !empty($link['children']);
                        $isActive = $link['active'] ?? false;
                        $hasActiveChild = $hasChildren && collect($link['children'])->contains(fn ($child) => !empty($child['active']));
                        $isExpanded = $hasActiveChild;
                    @endphp

                    @if ($hasChildren)
                        <div>
                            <button type="button" class="sidebar-trigger flex w-full items-center justify-between rounded-lg px-4 py-3 text-left text-[1.05rem] font-medium transition-colors duration-200 {{ $isExpanded ? 'is-active bg-white/8 text-white' : 'text-white hover:bg-white/5' }}" data-submenu-toggle aria-expanded="{{ $isExpanded ? 'true' : 'false' }}">
                                <span>{{ $link['label'] }}</span>
                                <svg class="submenu-arrow h-4 w-4 text-white transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                                </svg>
                            </button>

                            <div class="submenu overflow-hidden {{ $isExpanded ? 'is-open' : '' }}" data-submenu>
                                <div class="mt-1 space-y-1">
                                    @foreach ($link['children'] as $child)
                                        <a href="{{ $child['route'] }}" class="flex items-center gap-3 rounded-lg px-4 py-3 text-[1rem] transition-colors duration-200 {{ !empty($child['active']) ? 'bg-[#03282d] text-cyan-500' : 'text-white/80 hover:bg-white/5 hover:text-white' }}">
                                            <span>{{ $child['label'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ $link['route'] }}" class="flex items-center gap-3 rounded-lg px-4 py-3 text-[1.05rem] font-medium transition-all duration-200 {{ $isActive ? 'bg-[#03282d] text-cyan-500' : 'text-white hover:bg-white/5' }}">
                            <span class="flex-1">{{ $link['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        @endforeach
    </nav>

    <div class="border-t border-[#151515] px-4 py-4">
        <p class="text-center text-xs text-white/40">© 2026 TechCourse</p>
    </div>
</aside>
