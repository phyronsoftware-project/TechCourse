<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('Forgot password?') }} | TechCourse</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Noto+Sans+Khmer:wght@400;500;700&display=swap" rel="stylesheet">

        <style>
            :root {
                --font-body: 'Noto Sans Khmer', sans-serif;
                --font-heading: 'Lato', sans-serif;
            }

            * { box-sizing: border-box; }
            body {
                margin: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                background: linear-gradient(180deg, #f8fbff 0%, #eff6ff 100%);
                color: #17233c;
                font-family: var(--font-body);
            }
            .auth-box {
                width: min(100%, 520px);
                background: #fff;
                border: 1px solid #d9e7f5;
                border-radius: 24px;
                box-shadow: 0 18px 44px rgba(15, 23, 42, 0.06);
                padding: 28px;
            }
            .auth-title {
                margin: 0;
                font-family: var(--font-heading);
                font-size: 28px;
            }
            .auth-copy {
                margin: 12px 0 0;
                color: #657894;
                font-size: 14px;
                line-height: 1.7;
            }
            .auth-flash,
            .auth-error-box {
                margin-top: 18px;
                border-radius: 14px;
                padding: 12px 14px;
                font-size: 13px;
            }
            .auth-flash {
                border: 1px solid #bfdbfe;
                background: #eff6ff;
                color: #1d4ed8;
            }
            .auth-error-box {
                border: 1px solid #fecdd3;
                background: #fff1f2;
                color: #be123c;
            }
            .auth-form {
                margin-top: 22px;
                display: grid;
                gap: 18px;
            }
            .auth-field label {
                display: block;
                margin-bottom: 8px;
                font-family: var(--font-heading);
                font-size: 16px;
                font-weight: 700;
            }
            .auth-field input {
                width: 100%;
                min-height: 54px;
                padding: 0 18px;
                border-radius: 16px;
                border: 1.5px solid #c9ddf2;
                font-size: 15px;
                outline: none;
            }
            .auth-submit,
            .auth-back {
                min-height: 54px;
                border-radius: 18px;
                font-family: var(--font-heading);
                font-size: 16px;
                font-weight: 700;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            .auth-submit {
                border: 0;
                background: #1895bc;
                color: #fff;
                cursor: pointer;
            }
            .auth-back {
                border: 1px solid #d6e4f3;
                color: #1895bc;
                background: #fff;
            }
            .auth-actions {
                display: grid;
                gap: 12px;
            }
        </style>
    </head>
    <body>
        <main class="auth-box">
            <h1 class="auth-title">{{ __('Forgot password?') }}</h1>
            <p class="auth-copy">Enter your email address and we will send you a 6-digit verification code for password reset.</p>

            @if (session('success'))
                <div class="auth-flash">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="auth-error-box">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="auth-form">
                @csrf
                <div class="auth-field">
                    <label for="forgot_email">{{ __('Email') }}</label>
                    <input id="forgot_email" type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('Enter your email') }}" required>
                </div>

                <div class="auth-actions">
                    <button type="submit" class="auth-submit">Send Verification Code</button>
                    <a href="{{ route('web.login') }}" class="auth-back">{{ __('Back to Login') }}</a>
                </div>
            </form>
        </main>
    </body>
</html>
