@extends('web.layouts.app')

@section('title', 'TechCourse')

@php
    $isKhmer = app()->getLocale() === 'km';

    $trackingSectionTitle = $isKhmer
        ? 'ស្ថិតិរហ័សរបស់ Platform'
        : 'Quick Platform Stats';

    $trackingSectionCopy = $isKhmer
        ? 'មើលចំនួនអ្នកប្រើ guest visits វគ្គសិក្សា និងផលិតផលសរុបក្នុងកន្លែងតែមួយ ដើម្បីតាមដានស្ថានភាពទូទៅរបស់ website បានលឿន។'
        : 'See total users, guest visits, courses, and products in one place for a fast overview of your website activity.';

    $trackingItems = [
        [
            'key' => 'login-users',
            'label' => __('Logged In Users'),
            'value' => number_format((int) ($trackingStats['login_users'] ?? 0)),
            'icon' => 'fa-solid fa-user-check',
            'tone' => 'is-blue',
            'description' => __('Total registered users currently stored in the system database.'),
            'source' => __('Laravel DB'),
        ],
        [
            'key' => 'guest-views',
            'label' => __('Guest Website Views'),
            'value' => number_format((int) ($trackingStats['guest_views'] ?? 0)),
            'icon' => 'fa-solid fa-eye',
            'tone' => 'is-emerald',
            'description' => __('Guest visitor total from DB tracking table if that table is available.'),
            'source' => __('Laravel DB'),
        ],
        [
            'key' => 'total-courses',
            'label' => __('Total Courses'),
            'value' => number_format((int) ($trackingStats['courses'] ?? 0)),
            'icon' => 'fa-solid fa-book-open-reader',
            'tone' => 'is-violet',
            'description' => __('Total course records currently stored in the database.'),
            'source' => __('Laravel DB'),
        ],
        [
            'key' => 'total-products',
            'label' => __('Total Products'),
            'value' => number_format((int) ($trackingStats['products'] ?? 0)),
            'icon' => 'fa-solid fa-bag-shopping',
            'tone' => 'is-rose',
            'description' => __('Total product records currently stored in the database.'),
            'source' => __('Laravel DB'),
        ],
    ];

    $serviceItems = [
        [
            'icon' => 'fa-solid fa-laptop-code',
            'tone' => 'is-blue',
            'title' => __('Web Design'),
            'description' => __('Beautiful and user-friendly websites that capture attention from the first visit to your website.'),
        ],
        [
            'icon' => 'fa-solid fa-code',
            'tone' => 'is-indigo',
            'title' => __('Web Development'),
            'description' => __('Technology-driven websites with stable performance and speed that help you work more effectively.'),
        ],
        [
            'icon' => 'fa-solid fa-mobile-screen-button',
            'tone' => 'is-violet',
            'title' => __('Mobile App Development'),
            'description' => __('We build high-quality mobile applications for iOS and Android that deliver a memorable experience for users.'),
        ],
        [
            'icon' => 'fa-solid fa-pen-ruler',
            'tone' => 'is-rose',
            'title' => __('UI/UX Design'),
            'description' => __('User interface and user experience designs that are intuitive and visually appealing, helping improve engagement and conversions.'),
        ],
        [
            'icon' => 'fa-solid fa-magnifying-glass-chart',
            'tone' => 'is-emerald',
            'title' => __('SEO Optimization'),
            'description' => __('Improve your website ranking and grow your business through better visibility in search engines.'),
        ],
        [
            'icon' => 'fa-solid fa-tablet-screen-button',
            'tone' => 'is-cyan',
            'title' => __('Responsive Website'),
            'description' => __('Websites designed to fit mobile screens and other devices, improving ease of use and user experience across platforms.'),
        ],
    ];
@endphp

@section('content')
    <style>
        .home-tracking {
            position: relative;
            width: 100vw;
            margin-left: calc(50% - 50vw);
            padding: 0 0 24px;
        }

        .home-tracking__inner {
            display: grid;
            align-content: start;
            gap: 18px;
            width: min(1440px, 100vw);
            min-height: 354px;
            margin: 0 auto;
            padding: 76px 28px 26px;
            border-radius: 0;
            background: #111111;
            border: 0;
            box-shadow: none;
        }

        .home-tracking__head {
            display: grid;
            gap: 8px;
            justify-items: center;
            text-align: center;
        }

        .home-tracking__badge {
            display: none;
            align-items: center;
            justify-content: center;
            min-height: 24px;
            padding: 0 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #f3f4f6;
            font-size: 0.66rem;
            font-weight: 700;
        }

        .home-tracking__title {
            margin: 0;
            max-width: 760px;
            color: #f8fafc;
            font-size: clamp(1.15rem, 2vw, 1.7rem);
            line-height: 1.12;
            letter-spacing: -0.02em;
            font-weight: 800;
            font-family: var(--font-lato);
        }

        .home-tracking__copy {
            margin: 0;
            max-width: 820px;
            color: rgba(241, 245, 249, 0.72);
            font-size: 0.96rem;
            line-height: 1.7;
            text-align: center;
            box-shadow: none;
        }

        .home-tracking__grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            width: min(1120px, calc(100% - 56px));
            margin: 0 auto;
            background: transparent;
            border: 0;
        }

        .home-tracking-card {
            position: relative;
            min-height: 154px;
            padding: 18px 14px 16px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.06) 0%, rgba(255, 255, 255, 0.03) 100%);
            box-shadow: 0 18px 34px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            overflow: hidden;
        }

        .home-tracking-card:last-child {
            border-right: 1px solid rgba(255, 255, 255, 0.12);
        }

        .home-tracking-card::before {
            content: "";
            position: absolute;
            inset: 0;
            opacity: 1;
            pointer-events: none;
        }

        .home-tracking-card.is-blue::before {
            background:
                radial-gradient(circle at top left, rgba(59, 130, 246, 0.3), transparent 56%),
                linear-gradient(180deg, rgba(37, 99, 235, 0.08), transparent 70%);
        }

        .home-tracking-card.is-emerald::before {
            background:
                radial-gradient(circle at top left, rgba(16, 185, 129, 0.28), transparent 56%),
                linear-gradient(180deg, rgba(5, 150, 105, 0.08), transparent 70%);
        }

        .home-tracking-card.is-violet::before {
            background:
                radial-gradient(circle at top left, rgba(139, 92, 246, 0.28), transparent 56%),
                linear-gradient(180deg, rgba(124, 58, 237, 0.08), transparent 70%);
        }

        .home-tracking-card.is-rose::before {
            background:
                radial-gradient(circle at top left, rgba(244, 63, 94, 0.28), transparent 56%),
                linear-gradient(180deg, rgba(225, 29, 72, 0.08), transparent 70%);
        }

        .home-tracking-card__body {
            position: relative;
            z-index: 1;
            display: grid;
            justify-items: center;
            text-align: center;
            align-content: center;
            gap: 10px;
            height: 100%;
        }

        .home-tracking-card__top {
            display: grid;
            justify-items: center;
            gap: 8px;
        }

        .home-tracking-card__icon {
            width: 42px;
            height: 42px;
            border-radius: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #e5eefb;
            font-size: 1rem;
            box-shadow: none;
        }

        .home-tracking-card.is-blue .home-tracking-card__icon {
            color: #7dd3fc;
        }

        .home-tracking-card.is-emerald .home-tracking-card__icon {
            color: #6ee7b7;
        }

        .home-tracking-card.is-violet .home-tracking-card__icon {
            color: #c4b5fd;
        }

        .home-tracking-card.is-rose .home-tracking-card__icon {
            color: #fda4af;
        }

        .home-tracking-card__source {
            display: none;
        }

        .home-tracking-card__label {
            margin: 0;
            max-width: 180px;
            color: rgba(241, 245, 249, 0.82);
            font-size: 0.96rem;
            font-weight: 600;
            line-height: 1.7;
            text-align: center;
        }

        .home-tracking-card__value {
            margin: 0;
            color: #ffffff;
            font-family: var(--font-lato);
            font-size: clamp(1.85rem, 2.5vw, 2.5rem);
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.04em;
        }

        .home-tracking-card__text {
            display: none;
        }

        .home-tracking-card__meta {
            display: none;
        }

        .home-services {
            position: relative;
            width: min(1280px, calc(100% - 32px));
            margin: 0 auto;
            padding: 40px 0 72px;
        }

        .home-services__inner {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 28px;
        }

        .home-services__head {
            position: static;
            text-align: center;
            display: grid;
            justify-items: center;
            gap: 14px;
            padding: 0;
            background: transparent;
            border: 0;
            box-shadow: none;
        }

        .home-services__badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 0 16px;
            border-radius: 999px;
            background: rgba(29, 140, 255, 0.1);
            border: 1px solid rgba(29, 140, 255, 0.18);
            color: #1d4ed8;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        .home-services__title {
            position: static;
            margin: 0;
            max-width: 760px;
            color: #0f172a;
            font-family: var(--font-lato);
            font-size: clamp(1.15rem, 2vw, 1.7rem);
            line-height: 1.12;
            letter-spacing: -0.02em;
            font-weight: 800;
        }

        .home-services__copy {
            position: static;
            margin: 0;
            max-width: 640px;
            color: #64748b;
            font-size: 1.02rem;
            line-height: 1.8;
        }

        .home-services__grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .home-service-card {
            position: relative;
            min-height: 270px;
            padding: 24px 22px;
            border-radius: 24px;
            border: 1px solid #dde8f3;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(247, 250, 255, 0.98));
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.06);
            overflow: hidden;
            transition: transform 0.24s ease, box-shadow 0.24s ease, border-color 0.24s ease;
        }

        .home-service-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(29, 140, 255, 0.05), rgba(103, 80, 255, 0.02));
            opacity: 0;
            transition: opacity 0.24s ease;
            pointer-events: none;
        }

        .home-service-card:hover {
            transform: translateY(-6px);
            border-color: #cbdcf0;
            box-shadow: 0 28px 50px rgba(15, 23, 42, 0.1);
        }

        .home-service-card:hover::before {
            opacity: 1;
        }

        .home-service-card__body {
            position: relative;
            z-index: 1;
            display: grid;
            justify-items: center;
            text-align: center;
            gap: 14px;
        }

        .home-service-card__icon {
            width: 62px;
            height: 62px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
        }

        .home-service-card.is-blue .home-service-card__icon {
            background: rgba(29, 140, 255, 0.12);
            color: #1d8cff;
        }

        .home-service-card.is-indigo .home-service-card__icon {
            background: rgba(79, 70, 229, 0.12);
            color: #4f46e5;
        }

        .home-service-card.is-violet .home-service-card__icon {
            background: rgba(124, 58, 237, 0.12);
            color: #7c3aed;
        }

        .home-service-card.is-rose .home-service-card__icon {
            background: rgba(244, 63, 94, 0.12);
            color: #e11d48;
        }

        .home-service-card.is-emerald .home-service-card__icon {
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
        }

        .home-service-card.is-cyan .home-service-card__icon {
            background: rgba(6, 182, 212, 0.12);
            color: #0891b2;
        }

        .home-service-card__title {
            margin: 0;
            color: #0f172a;
            font-size: 1.2rem;
            font-weight: 800;
            line-height: 1.35;
        }

        .home-service-card__text {
            margin: 0;
            color: #64748b;
            font-size: 0.96rem;
            line-height: 1.7;
        }

        @media (max-width: 1080px) {
            .home-tracking__grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                width: min(620px, calc(100% - 24px));
            }

            .home-services__grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .home-tracking {
                padding: 0 0 14px;
            }

            .home-tracking__head {
                text-align: center;
                justify-items: center;
            }

            .home-tracking__inner {
                gap: 18px;
                min-height: auto;
                padding: 100px 12px 18px;
            }

            .home-tracking__title {
                font-size: clamp(1rem, 4.8vw, 1.3rem);
            }

            .home-tracking__copy {
                font-size: 0.96rem;
                line-height: 1.7;
            }

            .home-tracking__grid {
                grid-template-columns: 1fr;
                width: 100%;
            }

            .home-tracking-card {
                min-height: auto;
                padding: 18px 14px 16px;
                border-right: 1px solid rgba(255, 255, 255, 0.12);
                border-bottom: 0;
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.06) 0%, rgba(255, 255, 255, 0.03) 100%);
            }

            .home-tracking-card:last-child {
                border-right: 1px solid rgba(255, 255, 255, 0.12);
            }

            .home-tracking-card__icon {
                width: 38px;
                height: 38px;
                font-size: 0.92rem;
            }

            .home-tracking-card__value {
                font-size: 1.5rem;
            }

            .home-services {
                width: min(100%, calc(100% - 20px));
                padding: 24px 0 56px;
            }

            .home-services__grid {
                grid-template-columns: 1fr;
            }

            .home-service-card {
                min-height: auto;
                padding: 22px 18px;
            }
        }
    </style>

    <section class="home-tracking" id="home-tracking-section">
        <div class="home-tracking__inner">
            <div class="home-tracking__head">
                <span class="home-tracking__badge">{{ __('Website Tracking') }}</span>
                <h1 class="home-tracking__title">{{ $trackingSectionTitle }}</h1>
                <p class="home-tracking__copy">{{ $trackingSectionCopy }}</p>
            </div>

            <div class="home-tracking__grid">
                @foreach ($trackingItems as $trackingItem)
                    <article class="home-tracking-card {{ $trackingItem['tone'] }}">
                        <div class="home-tracking-card__body">
                            <div class="home-tracking-card__top">
                                <span class="home-tracking-card__icon">
                                    <i class="{{ $trackingItem['icon'] }}"></i>
                                </span>
                                <span class="home-tracking-card__source">{{ $trackingItem['source'] }}</span>
                            </div>

                            <div>
                                <p class="home-tracking-card__label">{{ $trackingItem['label'] }}</p>
                                <h2 class="home-tracking-card__value">{{ $trackingItem['value'] }}</h2>
                            </div>

                            <p class="home-tracking-card__text">{{ $trackingItem['description'] }}</p>
                            <span class="home-tracking-card__meta">
                                <i class="fa-solid fa-signal"></i>
                                {{ __('Live total') }}
                            </span>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="home-services">
        <div class="home-services__inner">
            <div class="home-services__head">
                <span class="home-services__badge">{{ __('Our Services') }}</span>
                <h1 class="home-services__title">{{ __('What We Offer') }}</h1>
                <p class="home-services__copy">{{ __('We provide complete web solutions that meet your business needs.') }}</p>
            </div>

            <div class="home-services__grid">
                @foreach ($serviceItems as $service)
                    <article class="home-service-card {{ $service['tone'] }}">
                        <div class="home-service-card__body">
                            <span class="home-service-card__icon">
                                <i class="{{ $service['icon'] }}"></i>
                            </span>
                            <h2 class="home-service-card__title">{{ $service['title'] }}</h2>
                            <p class="home-service-card__text">{{ $service['description'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

@endsection
