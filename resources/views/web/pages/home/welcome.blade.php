@extends('web.layouts.app')

@section('title', 'TechCourse')

@php
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
            font-size: clamp(2rem, 4vw, 3.15rem);
            line-height: 1.08;
            letter-spacing: -0.03em;
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
            .home-services__grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
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

    <section class="home-services">
        <div class="home-services__inner">
            <header class="home-services__head">
                <span class="home-services__badge">{{ __('Our Services') }}</span>
                <h1 class="home-services__title">{{ __('What We Offer') }}</h1>
                <p class="home-services__copy">{{ __('We provide complete web solutions that meet your business needs.') }}</p>
            </header>

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
