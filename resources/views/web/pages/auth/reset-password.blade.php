<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Reset Password | TechCourse</title>

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
            .auth-error-box {
                margin-top: 18px;
                border-radius: 14px;
                padding: 12px 14px;
                font-size: 13px;
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
            .auth-submit {
                min-height: 54px;
                border-radius: 18px;
                border: 0;
                background: #1895bc;
                color: #fff;
                font-family: var(--font-heading);
                font-size: 16px;
                font-weight: 700;
                cursor: pointer;
            }
        </style>
    </head>
    <body>
        <main class="auth-box">
            <h1 class="auth-title">Reset Password</h1>
            <p class="auth-copy">Verification completed. Set your new password to continue using your TechCourse account.</p>

            @if ($errors->any())
                <div class="auth-error-box">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST" class="auth-form">
                @csrf
                <div class="auth-field">
                    <label for="reset_email">{{ __('Email') }}</label>
                    <input id="reset_email" type="email" name="email" value="{{ old('email', $email) }}" placeholder="{{ __('Enter your email') }}" required readonly>
                </div>

                <div class="auth-field">
                    <label for="reset_password">{{ __('Password') }}</label>
                    <input id="reset_password" type="password" name="password" placeholder="{{ __('Enter your password') }}" required>
                </div>

                <div class="auth-field">
                    <label for="reset_password_confirmation">{{ __('Confirm Password') }}</label>
                    <input id="reset_password_confirmation" type="password" name="password_confirmation" placeholder="{{ __('Confirm your password') }}" required>
                </div>

                <button type="submit" class="auth-submit">Update Password</button>
            </form>
        </main>
    </body>
</html>
