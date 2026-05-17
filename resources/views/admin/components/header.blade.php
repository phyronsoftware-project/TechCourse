<header class="fixed inset-x-0 top-0 z-30 h-14 border-b border-[#e5edf6] bg-white/95 backdrop-blur-sm lg:left-[234px]">
    <div class="flex h-14 items-center justify-between px-4 sm:px-6 lg:px-7">
        <div class="flex items-center gap-3">
            <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-[#d9e2ef] bg-white text-[#38527f] transition hover:bg-[#f6f9fd] lg:hidden"
                data-sidebar-toggle
                aria-label="Toggle sidebar"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h10" />
                </svg>
            </button>

            <div>
                <p class="text-[0.92rem] font-semibold text-[#173f87]">{{ $pageTitle ?? 'Dashboard' }}</p>
                <p class="text-[0.72rem] text-[#7a8ca8]">TechCourse admin workspace</p>
            </div>
        </div>

        <div class="flex items-center gap-3 sm:gap-4">
            <div class="inline-flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-2xl bg-[#1a4388] text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M19 21a7 7 0 1 0-14 0" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </span>
                <span class="hidden sm:inline">
                    <strong class="block text-[0.84rem] font-medium leading-tight text-[#173f87]">{{ auth()->user()->name ?? 'Admin' }}</strong>
                    <span class="block text-[0.68rem] leading-tight text-[#7a8ca8]">{{ auth()->user()->email ?? 'admin@techcourse.test' }}</span>
                </span>
            </div>

            <div class="hidden h-9 w-px bg-[#e5edf6] sm:block"></div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl px-2 py-2 text-[0.82rem] font-medium text-[#173f87] transition hover:bg-[#f4f8fd] hover:text-[#173f87]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <path d="m16 17 5-5-5-5" />
                        <path d="M21 12H9" />
                    </svg>
                    <span class="hidden sm:inline">Log out</span>
                </button>
            </form>
        </div>
    </div>
</header>
