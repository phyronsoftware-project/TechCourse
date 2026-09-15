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

        <style>
            /* Match the full admin login canvas to the TechCourse logo palette. */
            :root {
                color-scheme: dark;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
            }

            .admin-login-page {
                position: relative;
                display: flex;
                min-height: 100vh;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                padding: 40px 24px;
                background:
                    radial-gradient(circle at 18% 18%, rgba(0, 190, 255, 0.28), transparent 31%),
                    radial-gradient(circle at 82% 82%, rgba(26, 86, 255, 0.34), transparent 35%),
                    linear-gradient(145deg, #03132f 0%, #063c9d 52%, #087bea 100%);
            }

            .admin-login-page::before {
                position: absolute;
                width: 420px;
                height: 420px;
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 50%;
                content: '';
                transform: translate(-46vw, -34vh);
            }

            .admin-login {
                position: relative;
                z-index: 1;
                width: min(100%, 390px);
            }

            .admin-login__header {
                margin-bottom: 30px;
                text-align: center;
            }

            .admin-login__logo {
                display: block;
                width: 66px;
                height: 66px;
                margin: 0 auto 18px;
                object-fit: contain;
                filter: drop-shadow(0 12px 24px rgba(1, 12, 43, 0.28));
            }

            .admin-login__title {
                margin: 0;
                color: #ffffff;
                font-size: clamp(1.65rem, 5vw, 2rem);
                font-weight: 700;
                letter-spacing: -0.025em;
            }

            .admin-login__subtitle {
                max-width: 340px;
                margin: 9px auto 0;
                color: rgba(231, 241, 255, 0.78);
                font-size: 0.88rem;
                line-height: 1.55;
            }

            .admin-login__form {
                display: grid;
                gap: 18px;
            }

            .admin-login__label {
                display: block;
                margin-bottom: 7px;
                color: #f8fbff;
                font-size: 0.84rem;
                font-weight: 600;
            }

            .admin-login__input {
                width: 100%;
                height: 44px;
                border: 1px solid rgba(255, 255, 255, 0.32);
                border-radius: 10px;
                outline: none;
                background: rgba(3, 23, 63, 0.28);
                padding: 0 14px;
                color: #ffffff;
                font: inherit;
                font-size: 0.9rem;
                transition: border-color 180ms ease, background 180ms ease, box-shadow 180ms ease;
            }

            .admin-login__input:-webkit-autofill,
            .admin-login__input:-webkit-autofill:hover,
            .admin-login__input:-webkit-autofill:focus {
                -webkit-text-fill-color: #ffffff;
                box-shadow: 0 0 0 1000px #073785 inset;
                caret-color: #ffffff;
            }

            .admin-login__input:focus {
                border-color: rgba(255, 255, 255, 0.9);
                background: rgba(3, 23, 63, 0.4);
                box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.12);
            }

            .admin-login__alert {
                margin: 0;
                border-left: 3px solid #fda4af;
                padding: 9px 12px;
                color: #fff1f2;
                font-size: 0.82rem;
                line-height: 1.5;
            }

            .admin-login__button {
                display: flex;
                width: 100%;
                height: 44px;
                align-items: center;
                justify-content: center;
                margin-top: 2px;
                border: 0;
                border-radius: 10px;
                background: #ffffff;
                color: #0756c7;
                cursor: pointer;
                font: inherit;
                font-size: 0.92rem;
                font-weight: 700;
                transition: transform 180ms ease, box-shadow 180ms ease, background 180ms ease;
            }

            .admin-login__button:hover {
                background: #eef6ff;
                box-shadow: 0 12px 28px rgba(0, 20, 67, 0.22);
                transform: translateY(-1px);
            }

            .admin-login__button:focus-visible {
                outline: 3px solid rgba(255, 255, 255, 0.35);
                outline-offset: 3px;
            }

            .admin-login__demo {
                margin: 20px 0 0;
                color: rgba(225, 237, 255, 0.68);
                font-size: 0.75rem;
                line-height: 1.6;
                text-align: center;
            }

            .admin-login__demo strong {
                color: rgba(255, 255, 255, 0.9);
                font-weight: 600;
            }

            @media (max-width: 480px) {
                .admin-login-page {
                    align-items: flex-start;
                    padding: 15vh 20px 36px;
                }

                .admin-login__header {
                    margin-bottom: 25px;
                }

                .admin-login__logo {
                    width: 58px;
                    height: 58px;
                }
            }
        </style>
    </head>
    <body class="font-sans antialiased {{ app()->getLocale() === 'km' ? 'font-[Noto_Sans_Khmer]' : '' }}">
        <!-- Present a compact admin login without a surrounding card. -->
        <main class="admin-login-page">
            <section class="admin-login" aria-labelledby="admin-login-title">
                <header class="admin-login__header">
                    <img src="{{ asset('logo/logo copy.png') }}" alt="TechCourse" class="admin-login__logo">
                    <h1 id="admin-login-title" class="admin-login__title">{{ __('Admin sign in') }}</h1>
                    <p class="admin-login__subtitle">{{ __('Sign in to open the TechCourse dashboard and admin tools.') }}</p>
                </header>

                <form action="{{ route('login.store') }}" method="POST" class="admin-login__form">
                    @csrf

                    <div>
                        <label for="email" class="admin-login__label">{{ __('Email') }}</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="username"
                            value="{{ old('email') }}"
                            class="admin-login__input"
                            required
                        >
                    </div>

                    <div>
                        <label for="password" class="admin-login__label">{{ __('Password') }}</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            class="admin-login__input"
                            required
                        >
                    </div>

                    @if (session('error'))
                        <p class="admin-login__alert" role="alert">{{ session('error') }}</p>
                    @endif

                    @if ($errors->any())
                        <div class="admin-login__alert" role="alert">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <button type="submit" class="admin-login__button">
                        {{ __('Sign in') }}
                    </button>
                </form>

                <p class="admin-login__demo">
                    {{ __('Demo admin login from your SQL file:') }}
                    <strong>admin@techcourse.test</strong>
                    /
                    <strong>password</strong>
                </p>
            </section>
        </main>
    </body>
</html>
