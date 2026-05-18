<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $verifyMode = $mode ?? 'login';
            $verifyTitle = $verifyMode === 'password_reset' ? 'Reset Password Verification' : __('Code Verification');
            $verifyButton = $verifyMode === 'password_reset' ? 'Verify Code' : __('Verify Account');
            $verifyCopy = $verifyMode === 'password_reset'
                ? 'We sent a 6-digit verification code to your email for password reset.'
                : __('We sent a 6-digit verification code to your email address.');
        @endphp
        <title>{{ $verifyTitle }} | TechCourse</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;800&family=Noto+Sans+Khmer:wght@400;500;700&display=swap" rel="stylesheet">
        <style>
            :root {
                --font-body: 'Noto Sans Khmer', sans-serif;
                --font-lato: 'Lato', sans-serif;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 24px;
                background:
                    radial-gradient(circle at top right, rgba(96, 165, 250, 0.18), transparent 24%),
                    radial-gradient(circle at bottom left, rgba(191, 219, 254, 0.22), transparent 20%),
                    #ffffff;
                color: #0f172a;
                font-family: var(--font-body);
                position: relative;
                overflow: hidden;
            }

            body::before {
                content: "";
                position: fixed;
                inset: 0;
                background-image: radial-gradient(rgba(59, 130, 246, 0.14) 1px, transparent 1px);
                background-size: 34px 34px;
                pointer-events: none;
                opacity: 0.34;
            }

            .verify-shell {
                width: min(100%, 560px);
                position: relative;
                z-index: 1;
                text-align: center;
            }

            .verify-orb {
                width: 88px;
                height: 88px;
                margin: 0 auto 18px;
                border-radius: 24px;
                display: grid;
                place-items: center;
                border: 1px solid #dbeafe;
                background: rgba(255, 255, 255, 0.95);
                box-shadow: 0 18px 40px rgba(37, 99, 235, 0.12);
                overflow: hidden;
            }

            .verify-orb img {
                width: 100%;
                height: 100%;
                object-fit: contain;
                display: block;
            }

            .verify-title {
                margin: 0;
                font-family: var(--font-lato);
                font-size: 30px;
                font-weight: 800;
                line-height: 1.08;
                color: #0f172a;
            }

            .verify-copy {
                margin: 10px auto 0;
                max-width: 440px;
                color: #7b8799;
                font-size: 13px;
                line-height: 1.8;
            }

            .verify-copy strong {
                color: #2563eb;
                font-weight: 700;
            }

            .verify-panel {
                width: min(100%, 430px);
                margin: 24px auto 0;
                text-align: left;
            }

            .verify-label {
                display: block;
                margin-bottom: 12px;
                color: #0f172a;
                font-family: var(--font-lato);
                font-size: 15px;
                font-weight: 700;
            }

            .verify-flash,
            .verify-errors {
                margin-bottom: 18px;
                padding: 12px 14px;
                border-radius: 14px;
                font-size: 12px;
                line-height: 1.7;
            }

            .verify-flash {
                background: #eff6ff;
                border: 1px solid #cfe0fb;
                color: #1d4ed8;
            }

            .verify-errors {
                background: #fff7ed;
                border: 1px solid #fed7aa;
                color: #9a3412;
            }

            .verify-errors div + div {
                margin-top: 6px;
            }

            .verify-form {
                display: grid;
                gap: 14px;
            }

            .verify-code-row {
                display: grid;
                grid-template-columns: repeat(6, minmax(0, 1fr));
                gap: 10px;
            }

            .verify-code-box {
                width: 100%;
                height: 56px;
                border-radius: 18px;
                border: 1px solid #dbe6f1;
                background: #ffffff;
                color: #0f172a;
                text-align: center;
                font-family: var(--font-lato);
                font-size: 22px;
                font-weight: 800;
                outline: none;
                box-shadow: 0 10px 24px rgba(148, 163, 184, 0.08);
                transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
            }

            .verify-code-box:focus {
                border-color: #86b6ff;
                box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
                transform: translateY(-1px);
            }

            .verify-submit {
                width: 100%;
                min-height: 48px;
                border: 0;
                border-radius: 999px;
                background: linear-gradient(135deg, #4569ff, #2f50f7);
                color: #ffffff;
                font-family: var(--font-lato);
                font-size: 15px;
                font-weight: 700;
                cursor: pointer;
                box-shadow: 0 18px 34px rgba(47, 80, 247, 0.26);
                transition: opacity 0.2s ease, transform 0.2s ease;
            }

            .verify-submit[disabled],
            .verify-link-button[disabled] {
                opacity: 0.72;
                cursor: wait;
            }

            .verify-footer {
                margin-top: 12px;
                text-align: center;
                color: #475569;
                font-size: 12px;
            }

            .verify-link-button,
            .verify-back-link {
                background: none;
                border: 0;
                padding: 0;
                color: #4569ff;
                font: inherit;
                font-weight: 700;
                cursor: pointer;
                text-decoration: none;
            }

            .verify-back-wrap {
                margin-top: 10px;
                text-align: center;
            }

            .verify-back-link {
                font-size: 12px;
            }

            .verify-loading {
                width: 16px;
                height: 16px;
                border-radius: 999px;
                border: 2px solid rgba(255, 255, 255, 0.34);
                border-top-color: #ffffff;
                display: inline-block;
                animation: verify-spin 0.75s linear infinite;
                vertical-align: middle;
            }

            @keyframes verify-spin {
                from {
                    transform: rotate(0deg);
                }

                to {
                    transform: rotate(360deg);
                }
            }

            .verify-hidden-input {
                position: absolute;
                opacity: 0;
                pointer-events: none;
            }

            @media (max-width: 640px) {
                body {
                    padding: 20px 16px;
                }

                .verify-title {
                    font-size: 24px;
                }

                .verify-copy {
                    font-size: 12px;
                }

                .verify-label {
                    font-size: 14px;
                }

                .verify-code-row {
                    gap: 8px;
                }

                .verify-code-box {
                    height: 50px;
                    border-radius: 16px;
                    font-size: 20px;
                }

                .verify-submit {
                    min-height: 46px;
                    font-size: 14px;
                }

                .verify-footer {
                    font-size: 12px;
                }
            }
        </style>
    </head>
    <body class="{{ app()->getLocale() === 'km' ? 'locale-km' : 'locale-en' }}">
        <section class="verify-shell">
            <div class="verify-orb">
                <img src="{{ asset('logo/logo.png') }}" alt="TechCourse logo">
            </div>

            <h1 class="verify-title">{{ $verifyTitle }}</h1>
            <p class="verify-copy">
                {{ $verifyCopy }}
                <strong>{{ $emailMask }}</strong>
            </p>

            <div class="verify-panel">
                @if (session('success'))
                    <div class="verify-flash">{{ session('success') }}</div>
                @endif

                @if (session('warning'))
                    <div class="verify-errors">{{ session('warning') }}</div>
                @endif

                @if ($errors->any())
                    <div class="verify-errors">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <label class="verify-label" for="verification_code_0">{{ __('Code Verification') }}</label>

                <form action="{{ route('web.verify.code.store') }}" method="POST" class="verify-form" data-verify-form>
                    @csrf
                    <input type="hidden" name="code" value="{{ old('code') }}" data-verify-hidden-code class="verify-hidden-input">

                    <div class="verify-code-row">
                        @for ($i = 0; $i < 6; $i++)
                            <input
                                id="verification_code_{{ $i }}"
                                type="text"
                                maxlength="1"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                autocomplete="one-time-code"
                                class="verify-code-box"
                                data-verify-digit
                            >
                        @endfor
                    </div>

                    <button type="submit" class="verify-submit" data-verify-submit>{{ $verifyButton }}</button>
                </form>

                <div class="verify-footer">
                    {{ __("Didn't receive code?") }}
                    <form action="{{ route('web.verify.code.resend') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="verify-link-button" data-resend-submit>{{ __('Resend') }}</button>
                    </form>
                </div>

                <div class="verify-back-wrap">
                    <a href="{{ route('web.login') }}" class="verify-back-link">{{ __('Back to Login') }}</a>
                </div>
            </div>
        </section>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const hiddenInput = document.querySelector('[data-verify-hidden-code]');
                const digitInputs = Array.from(document.querySelectorAll('[data-verify-digit]'));
                const verifyForm = document.querySelector('[data-verify-form]');
                const verifySubmit = document.querySelector('[data-verify-submit]');
                const resendSubmit = document.querySelector('[data-resend-submit]');
                let hasSubmitted = false;

                if (!hiddenInput || !digitInputs.length) {
                    return;
                }

                const submitIfComplete = () => {
                    if (hasSubmitted || hiddenInput.value.length !== 6 || !verifyForm) {
                        return;
                    }

                    hasSubmitted = true;
                    verifyForm.requestSubmit();
                };

                const syncHiddenValue = () => {
                    hiddenInput.value = digitInputs.map((input) => input.value.replace(/\D/g, '')).join('');
                };

                const seedInitialCode = () => {
                    const initial = (hiddenInput.value || '').replace(/\D/g, '').slice(0, 6);

                    initial.split('').forEach((digit, index) => {
                        if (digitInputs[index]) {
                            digitInputs[index].value = digit;
                        }
                    });

                    const firstEmpty = digitInputs.find((input) => !input.value);
                    (firstEmpty || digitInputs[0]).focus();
                };

                digitInputs.forEach((input, index) => {
                    input.addEventListener('input', (event) => {
                        const cleaned = event.target.value.replace(/\D/g, '');
                        event.target.value = cleaned.slice(-1);
                        syncHiddenValue();

                        if (event.target.value && digitInputs[index + 1]) {
                            digitInputs[index + 1].focus();
                        }

                        submitIfComplete();
                    });

                    input.addEventListener('keydown', (event) => {
                        if (event.key === 'Backspace' && !input.value && digitInputs[index - 1]) {
                            digitInputs[index - 1].focus();
                        }
                    });

                    input.addEventListener('paste', (event) => {
                        event.preventDefault();
                        const pasted = (event.clipboardData?.getData('text') || '').replace(/\D/g, '').slice(0, 6);

                        if (!pasted) {
                            return;
                        }

                        pasted.split('').forEach((digit, pastedIndex) => {
                            if (digitInputs[pastedIndex]) {
                                digitInputs[pastedIndex].value = digit;
                            }
                        });

                        syncHiddenValue();

                        const nextInput = digitInputs[Math.min(pasted.length, 5)];
                        if (nextInput) {
                            nextInput.focus();
                        }

                        submitIfComplete();
                    });
                });

                seedInitialCode();
                syncHiddenValue();

                verifyForm?.addEventListener('submit', () => {
                    hasSubmitted = true;

                    if (verifySubmit) {
                        verifySubmit.disabled = true;
                        verifySubmit.innerHTML = '<span class="verify-loading"></span>';
                    }
                });

                resendSubmit?.form?.addEventListener('submit', () => {
                    resendSubmit.disabled = true;
                    resendSubmit.textContent = '{{ __("Sending...") }}';
                });
            });
        </script>
    </body>
</html>
