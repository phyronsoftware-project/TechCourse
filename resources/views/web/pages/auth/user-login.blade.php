<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $activeTab === 'register' ? __('Register') : __('Login') }} | TechCourse</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Noto+Sans+Khmer:wght@400;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <style>
            :root {
                --font-body: 'Noto Sans Khmer', sans-serif;
                --font-heading: 'Lato', sans-serif;
                --page-bg: #f8fbff;
                --card-bg: #ffffff;
                --card-border: #d9e7f5;
                --text-strong: #17233c;
                --text-soft: #657894;
                --primary: #1895bc;
                --primary-dark: #147fa1;
                --line: #d6e4f3;
                --field-border: #c9ddf2;
                --danger-bg: #fff1f2;
                --danger-border: #fecdd3;
                --danger-text: #be123c;
            }

            * {
                box-sizing: border-box;
            }

            html,
            body {
                margin: 0;
                min-height: 100%;
            }

            body {
                background:
                    radial-gradient(circle at top left, rgba(24, 149, 188, 0.08), transparent 28%),
                    linear-gradient(180deg, #fafdff 0%, #f2f8ff 100%);
                color: var(--text-strong);
                font-family: var(--font-body);
            }

            .auth-page {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 28px 16px;
            }

            .auth-card {
                width: min(550px, 100%);
                padding: 0;
            }

            .auth-panel {
                width: 100%;
                min-height: 577px;
                background: var(--card-bg);
                border: 1px solid var(--card-border);
                border-radius: 24px;
                box-shadow: 0 18px 44px rgba(15, 23, 42, 0.06);
                padding: 26px 28px 24px;
            }

            .auth-title {
                margin: 0;
                font-family: var(--font-heading);
                font-size: 18px;
                line-height: 1.2;
                color: var(--text-strong);
            }

            .auth-copy {
                margin: 12px 0 0;
                max-width: 620px;
                color: var(--text-soft);
                font-size: 14px;
                line-height: 1.5;
            }

            .auth-error-box,
            .auth-flash {
                margin-top: 18px;
                border-radius: 14px;
                padding: 12px 14px;
                font-size: 13px;
            }

            .auth-error-box {
                border: 1px solid var(--danger-border);
                background: var(--danger-bg);
                color: var(--danger-text);
            }

            .auth-flash {
                border: 1px solid #bfdbfe;
                background: #eff6ff;
                color: #1d4ed8;
            }

            .auth-form {
                margin-top: 22px;
            }

            .auth-grid {
                display: grid;
                gap: 18px;
            }

            .auth-grid-2 {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 18px;
            }

            .auth-field label {
                display: block;
                margin-bottom: 8px;
                color: var(--text-strong);
                font-family: var(--font-heading);
                font-size: 16px;
                font-weight: 700;
                line-height: 1.2;
            }

            .auth-field input {
                width: 100%;
                min-height: 54px;
                padding: 0 18px;
                border-radius: 16px;
                border: 1.5px solid var(--field-border);
                background: #ffffff;
                color: var(--text-strong);
                font-size: 15px;
                outline: none;
                transition: border-color 0.2s ease, box-shadow 0.2s ease;
            }

            .auth-password-wrap {
                position: relative;
            }

            .auth-password-wrap input {
                padding-right: 52px;
            }

            .auth-password-toggle {
                position: absolute;
                top: 50%;
                right: 16px;
                transform: translateY(-50%);
                width: 28px;
                height: 28px;
                border: 0;
                background: transparent;
                color: #6b7f99;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                padding: 0;
            }

            .auth-password-toggle i {
                font-size: 16px;
                pointer-events: none;
            }

            .auth-field input::placeholder {
                color: #7b7b7b;
            }

            .auth-field input:focus {
                border-color: #9bc7eb;
                box-shadow: 0 0 0 4px rgba(24, 149, 188, 0.08);
            }

            .auth-helper-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
                margin-top: 4px;
            }

            .auth-remember {
                display: inline-flex;
                align-items: center;
                gap: 12px;
                color: var(--text-soft);
                font-size: 14px;
            }

            .auth-remember input {
                width: 18px;
                height: 18px;
                margin: 0;
                accent-color: var(--primary);
            }

            .auth-link {
                color: var(--primary);
                font-size: 14px;
                font-weight: 700;
                text-decoration: none;
            }

            .auth-submit {
                width: 100%;
                min-height: 56px;
                border: 0;
                border-radius: 18px;
                background: var(--primary);
                color: #ffffff;
                font-family: var(--font-heading);
                font-size: 16px;
                font-weight: 700;
                cursor: pointer;
                transition: background 0.2s ease, transform 0.2s ease;
            }

            .auth-submit:hover {
                background: var(--primary-dark);
                transform: translateY(-1px);
            }

            .auth-divider {
                display: flex;
                align-items: center;
                gap: 14px;
                margin: 22px 0 14px;
                color: #7485a1;
                font-family: var(--font-heading);
                font-size: 16px;
                font-weight: 700;
            }

            .auth-divider::before,
            .auth-divider::after {
                content: '';
                flex: 1;
                height: 2px;
                background: var(--line);
            }

            .auth-social-stack {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 10px;
                align-items: start;
                justify-items: center;
                max-width: 360px;
                margin: 0 auto;
            }

            .auth-social {
                width: 100%;
                padding: 0;
                border: 0;
                background: transparent;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-start;
                gap: 10px;
                color: var(--text-strong);
                font-family: var(--font-heading);
                font-size: 14px;
                font-weight: 700;
                cursor: default;
            }

            .auth-social i {
                width: 56px;
                height: 56px;
                border-radius: 999px;
                border: 1.5px solid var(--field-border);
                background: #ffffff;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
            }

            .auth-social--google i {
                color: #ea4335;
            }

            .auth-social--facebook i {
                color: #1877f2;
            }

            .auth-social--github i {
                color: #111827;
            }

            .auth-switch {
                margin-top: 12px;
                text-align: center;
                color: var(--text-soft);
                font-size: 14px;
            }

            .auth-switch a {
                color: var(--primary);
                font-weight: 700;
                text-decoration: none;
            }

            .locale-km .auth-title,
            .locale-km .auth-field label,
            .locale-km .auth-submit,
            .locale-km .auth-divider,
            .locale-km .auth-social {
                font-family: var(--font-body);
            }

            @media (max-width: 991px) {
                .auth-panel {
                    min-height: auto;
                    padding: 24px 20px 20px;
                    border-radius: 20px;
                }

                .auth-copy {
                    font-size: 14px;
                }

                .auth-field label {
                    font-size: 16px;
                }

                .auth-field input,
                .auth-social {
                    min-height: 52px;
                    font-size: 15px;
                }

                .auth-submit {
                    min-height: 54px;
                    font-size: 16px;
                }

                .auth-remember,
                .auth-link,
                .auth-switch {
                    font-size: 14px;
                }
            }

            @media (max-width: 768px) {
                .auth-page {
                    padding: 16px 10px;
                }

                .auth-panel {
                    padding: 22px 16px 20px;
                }

                .auth-title {
                    font-size: 18px;
                }

                .auth-copy {
                    font-size: 16px;
                    margin-top: 10px;
                }

                .auth-grid,
                .auth-grid-2 {
                    gap: 18px;
                }

                .auth-social-stack {
                    gap: 10px;
                }

                .auth-grid-2,
                .auth-helper-row {
                    grid-template-columns: 1fr;
                    display: grid;
                    justify-content: initial;
                }

                .auth-field label {
                    margin-bottom: 10px;
                    font-size: 16px;
                }

                .auth-field input,
                .auth-social {
                    padding: 0 18px;
                    border-radius: 18px;
                    font-size: 17px;
                }

                .auth-password-wrap input {
                    padding-right: 46px;
                }

                .auth-social i {
                    width: 50px;
                    height: 50px;
                    font-size: 22px;
                }

                .auth-submit {
                    min-height: 60px;
                    border-radius: 20px;
                    font-size: 20px;
                }

                .auth-remember,
                .auth-link,
                .auth-switch {
                    font-size: 16px;
                }

                .auth-remember input {
                    width: 24px;
                    height: 24px;
                }

                .auth-divider {
                    margin: 24px 0 14px;
                    gap: 14px;
                    font-size: 20px;
                }
            }
        </style>
    </head>
    <body class="{{ app()->getLocale() === 'km' ? 'locale-km' : 'locale-en' }}">
        <main class="auth-page">
            <section class="auth-card">
                <div class="auth-panel">
                    @if (session('success'))
                        <div class="auth-flash">{{ session('success') }}</div>
                    @endif

                    @if (session('warning'))
                        <div class="auth-flash">{{ session('warning') }}</div>
                    @endif

                    @if ($activeTab === 'register')
                        <h1 class="auth-title">{{ __('Register') }}</h1>
                        <p class="auth-copy">{{ __('Create your TechCourse account to join courses and continue learning.') }}</p>

                        @if ($errors->any())
                            <div class="auth-error-box">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form action="{{ route('web.register.store') }}" method="POST" class="auth-form">
                            @csrf
                            @if (!empty($redirectTo))
                                <input type="hidden" name="redirect" value="{{ $redirectTo }}">
                            @endif

                            <div class="auth-grid">
                                <div class="auth-grid-2">
                                    <div class="auth-field">
                                        <label for="register_name">{{ __('Full Name') }}</label>
                                        <input id="register_name" type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('Enter your full name') }}" required>
                                    </div>

                                    <div class="auth-field">
                                        <label for="register_phone">{{ __('Phone') }}</label>
                                        <input id="register_phone" type="text" name="phone" value="{{ old('phone') }}" placeholder="{{ __('Enter your phone number') }}">
                                    </div>
                                </div>

                                <div class="auth-field">
                                    <label for="register_email">{{ __('Email') }}</label>
                                    <input id="register_email" type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('Enter your email') }}" required>
                                </div>

                                <div class="auth-grid-2">
                                    <div class="auth-field">
                                        <label for="register_password">{{ __('Password') }}</label>
                                        <div class="auth-password-wrap">
                                            <input id="register_password" type="password" name="password" placeholder="{{ __('Enter your password') }}" required>
                                            <button type="button" class="auth-password-toggle" data-password-toggle aria-label="Toggle password visibility">
                                                <i class="fa-regular fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="auth-field">
                                        <label for="register_password_confirmation">{{ __('Confirm Password') }}</label>
                                        <div class="auth-password-wrap">
                                            <input id="register_password_confirmation" type="password" name="password_confirmation" placeholder="{{ __('Confirm your password') }}" required>
                                            <button type="button" class="auth-password-toggle" data-password-toggle aria-label="Toggle password visibility">
                                                <i class="fa-regular fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="auth-submit">{{ __('Register') }}</button>
                            </div>
                        </form>

                        <div class="auth-divider">{{ __('or') }}</div>

                        <div class="auth-social-stack">
                            <button type="button" class="auth-social auth-social--google">
                                <i class="fa-brands fa-google"></i>
                                <span>Google</span>
                            </button>
                            <button type="button" class="auth-social auth-social--facebook">
                                <i class="fa-brands fa-facebook-f"></i>
                                <span>Facebook</span>
                            </button>
                            <button type="button" class="auth-social auth-social--github">
                                <i class="fa-brands fa-github"></i>
                                <span>Github</span>
                            </button>
                        </div>

                        <div class="auth-switch">
                            {{ __('Already have an account?') }}
                            <a href="{{ route('web.login', array_filter(['redirect' => $redirectTo ?? null])) }}">{{ __('Login') }}</a>
                        </div>
                    @else
                        <h1 class="auth-title">{{ __('Login') }}</h1>
                        <p class="auth-copy">{{ __('Login to continue your course learning journey.') }}</p>

                        @if ($errors->any())
                            <div class="auth-error-box">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form action="{{ route('web.login.store') }}" method="POST" class="auth-form">
                            @csrf
                            @if (!empty($redirectTo))
                                <input type="hidden" name="redirect" value="{{ $redirectTo }}">
                            @endif

                            <div class="auth-grid">
                                <div class="auth-field">
                                    <label for="login_email">{{ __('Email') }}</label>
                                    <input id="login_email" type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('Enter your email') }}" required>
                                </div>

                                <div class="auth-field">
                                    <label for="login_password">{{ __('Password') }}</label>
                                    <div class="auth-password-wrap">
                                        <input id="login_password" type="password" name="password" placeholder="{{ __('Enter your password') }}" required>
                                        <button type="button" class="auth-password-toggle" data-password-toggle aria-label="Toggle password visibility">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="auth-helper-row">
                                    <label class="auth-remember">
                                        <input type="checkbox" name="remember" value="1">
                                        <span>{{ __('Remember me') }}</span>
                                    </label>

                                    <a href="#" class="auth-link">{{ __('Forgot password?') }}</a>
                                </div>

                                <button type="submit" class="auth-submit">{{ __('Login') }}</button>
                            </div>
                        </form>

                        <div class="auth-divider">{{ __('or') }}</div>

                        <div class="auth-social-stack">
                            <button type="button" class="auth-social auth-social--google">
                                <i class="fa-brands fa-google"></i>
                                <span>Google</span>
                            </button>
                            <button type="button" class="auth-social auth-social--facebook">
                                <i class="fa-brands fa-facebook-f"></i>
                                <span>Facebook</span>
                            </button>
                            <button type="button" class="auth-social auth-social--github">
                                <i class="fa-brands fa-github"></i>
                                <span>Github</span>
                            </button>
                        </div>

                        <div class="auth-switch">
                            {{ __("Don't have an account?") }}
                            <a href="{{ route('web.register', array_filter(['redirect' => $redirectTo ?? null])) }}">{{ __('Register') }}</a>
                        </div>
                    @endif
                </div>
            </section>
        </main>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
                    toggle.addEventListener('click', () => {
                        const wrap = toggle.closest('.auth-password-wrap');
                        const input = wrap ? wrap.querySelector('input') : null;
                        const icon = toggle.querySelector('i');

                        if (!input || !icon) {
                            return;
                        }

                        const showing = input.type === 'text';
                        input.type = showing ? 'password' : 'text';
                        icon.className = showing ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
                    });
                });
            });
        </script>
    </body>
</html>
