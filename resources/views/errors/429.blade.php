<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('Too Many Requests') }} | TechCourse</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;800&family=Noto+Sans+Khmer:wght@400;500;700&display=swap" rel="stylesheet">
        <style>
            :root {
                --font-body: 'Noto Sans Khmer', sans-serif;
                --font-heading: 'Lato', sans-serif;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                display: grid;
                place-items: center;
                padding: 20px;
                background:
                    radial-gradient(circle at top left, rgba(24, 149, 188, 0.08), transparent 28%),
                    linear-gradient(180deg, #fafdff 0%, #f2f8ff 100%);
                color: #17233c;
                font-family: var(--font-body);
            }

            .rate-limit-card {
                width: min(520px, 100%);
                background: #ffffff;
                border: 1px solid #d9e7f5;
                border-radius: 24px;
                padding: 28px 26px;
                box-shadow: 0 18px 44px rgba(15, 23, 42, 0.06);
                text-align: center;
            }

            .rate-limit-code {
                margin: 0;
                color: #1895bc;
                font-family: var(--font-heading);
                font-size: clamp(2.5rem, 8vw, 4rem);
                font-weight: 800;
                line-height: 1;
            }

            .rate-limit-title {
                margin: 10px 0 0;
                font-family: var(--font-heading);
                font-size: 1.4rem;
                font-weight: 800;
            }

            .rate-limit-copy {
                margin: 12px auto 0;
                max-width: 360px;
                color: #667892;
                font-size: 0.95rem;
                line-height: 1.7;
            }

            .rate-limit-note {
                margin-top: 18px;
                padding: 12px 14px;
                border-radius: 16px;
                background: #f5f9ff;
                border: 1px solid #dbe8f6;
                color: #27548d;
                font-size: 0.88rem;
                font-weight: 700;
            }

            .rate-limit-actions {
                margin-top: 22px;
                display: flex;
                justify-content: center;
                gap: 12px;
                flex-wrap: wrap;
            }

            .rate-limit-btn {
                min-width: 148px;
                min-height: 46px;
                padding: 0 18px;
                border-radius: 16px;
                border: 1px solid #cfe0f2;
                background: #ffffff;
                color: #173f88;
                font-family: var(--font-heading);
                font-size: 0.95rem;
                font-weight: 700;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .rate-limit-btn--primary {
                background: linear-gradient(135deg, #173f88, #2f8fff);
                border-color: transparent;
                color: #ffffff;
            }
        </style>
    </head>
    <body>
        <section class="rate-limit-card">
            <p class="rate-limit-code">429</p>
            <h1 class="rate-limit-title">{{ __('Too Many Requests') }}</h1>
            <p class="rate-limit-copy">{{ __('Too many requests. Please wait a moment and try again.') }}</p>
            <div class="rate-limit-note">{{ __('Please try again after :seconds seconds.', ['seconds' => $retryAfter]) }}</div>
            <div class="rate-limit-actions">
                <a href="{{ url()->previous() }}" class="rate-limit-btn">{{ __('Go Back') }}</a>
                <a href="{{ route('home') }}" class="rate-limit-btn rate-limit-btn--primary">{{ __('Back to Home') }}</a>
            </div>
        </section>
    </body>
</html>
