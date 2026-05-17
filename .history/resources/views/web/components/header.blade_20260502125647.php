<header class="fixed inset-x-0 top-0 z-20 border-b border-slate-200/80 bg-white/80 backdrop-blur-xl lg:left-72">
    <div class="flex h-20 items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
            <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50 lg:hidden" data-sidebar-toggle aria-label="Open sidebar">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h10" />
                </svg>
            </button>

            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-600">Overview</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">@yield('page_title', 'Dashboard')</h2>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="hidden rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-500 shadow-sm sm:block">
                SQL source: <span class="font-semibold text-slate-800">system_TechCourse.sql</span>
            </div>

            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#0f172a,#0284c7)] text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M19 21a7 7 0 1 0-14 0" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </span>
                <div class="hidden sm:block">
                    <p class="text-sm font-semibold text-slate-900">TechCourse Admin</p>
                    <p class="text-xs text-slate-500">Dashboard session</p>
                </div>
            </div>
        </div>
    </div>
</header>
