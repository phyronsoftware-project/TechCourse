<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('Admin Sign In') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @php
            $hasViteAssets = file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'));
        @endphp

        @if ($hasViteAssets)
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
            <script>
                tailwind.config = {
                    theme: {
                        extend: {
                            fontFamily: {
                                sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                            },
                        },
                    },
                };
            </script>
        @endif
    </head>
    <body class="min-h-screen bg-[#f7f7f8] font-sans text-slate-900 antialiased {{ app()->getLocale() === 'km' ? 'font-[Noto_Sans_Khmer]' : '' }}">
        <main class="relative flex min-h-screen items-center justify-center overflow-hidden px-5 py-8">
            <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_left,rgba(99,102,241,0.10),transparent_25%),radial-gradient(circle_at_bottom_right,rgba(14,165,233,0.10),transparent_25%),linear-gradient(180deg,#f8fafc_0%,#eef4ff_100%)]"></div>

            <div class="w-full max-w-[500px]">
                <div class="rounded-[24px] border border-slate-200 bg-white px-6 py-8 shadow-[0_16px_40px_rgba(15,23,42,0.08)] sm:px-10 sm:py-9">
                    <div class="mb-6 flex flex-col items-center text-center">
                        <div class="mb-4 flex h-[76px] w-[76px] items-center justify-center rounded-full bg-indigo-50 text-indigo-700">
                            <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="4" y="10" width="16" height="10" rx="2" />
                                <path d="M8 10V7a4 4 0 1 1 8 0v3" />
                            </svg>
                        </div>
                        <h1 class="text-[1.8rem] font-bold tracking-[-0.02em] text-slate-950 sm:text-[1.95rem]">{{ __('Admin sign in') }}</h1>
                        <p class="mt-2.5 text-[14px] text-slate-500 sm:text-[15px]">{{ __('Sign in to open the TechCourse dashboard and admin tools.') }}</p>
                    </div>

                    <form action="{{ route('login.store') }}" method="POST" class="space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="mb-2 block text-[14px] font-semibold text-slate-900 sm:text-[15px]">{{ __('Email') }}</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                autocomplete="username"
                                value="{{ old('email') }}"
                                class="h-12 w-full rounded-[14px] border border-slate-200 bg-[#fafafa] px-4 text-[15px] text-slate-900 outline-none transition focus:border-indigo-400 focus:bg-white"
                                required
                            >
                        </div>

                        <div>
                            <label for="password" class="mb-2 block text-[14px] font-semibold text-slate-900 sm:text-[15px]">{{ __('Password') }}</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="current-password"
                                class="h-12 w-full rounded-[14px] border border-slate-200 bg-[#fafafa] px-4 text-[15px] text-slate-900 outline-none transition focus:border-indigo-400 focus:bg-white"
                                required
                            >
                        </div>

                        @if (session('error'))
                            <p class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600">{{ session('error') }}</p>
                        @endif

                        @if ($errors->any())
                            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        <button
                            type="submit"
                            class="flex h-12 w-full items-center justify-center rounded-[14px] bg-indigo-700 text-base font-medium text-white transition hover:bg-indigo-800"
                        >
                            {{ __('Sign in') }}
                        </button>
                    </form>

                    <div class="mt-6 rounded-[14px] bg-[#f3f3f4] px-4 py-3.5 text-center text-[13px] text-slate-500">
                        {{ __('Demo admin login from your SQL file:') }}
                        <span class="font-semibold text-slate-700">admin@techcourse.test</span>
                        /
                        <span class="font-semibold text-slate-700">password</span>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
