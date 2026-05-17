@extends('web.layouts.app')

@section('title', 'TechCourse')

@php
    $popularCourses = [
        [
            'title' => 'PHP Full Stack',
            'detail' => 'Learn PHP, MySQL, and practical website building with simple projects.',
            'lessons' => '#16 Lessons',
            'tag' => '#PHP',
            'icon' => 'fa-brands fa-php',
            'symbol' => 'php',
            'tone' => 'tone-blue',
        ],
        [
            'title' => 'ReactJS Starter',
            'detail' => 'Understand component structure, UI flow, and real frontend foundations.',
            'lessons' => '#16 Lessons',
            'tag' => '#ReactJS',
            'icon' => 'fa-brands fa-react',
            'symbol' => 'react',
            'tone' => 'tone-dark',
        ],
        [
            'title' => 'Python Basic',
            'detail' => 'Start Python syntax, logic building, and beginner-friendly coding practice.',
            'lessons' => '#16 Lessons',
            'tag' => '#Python',
            'icon' => 'fa-brands fa-python',
            'symbol' => 'python',
            'tone' => 'tone-sky',
        ],
        [
            'title' => 'CSS Advance',
            'detail' => 'Practice advanced styling, layouts, and visual polish for modern interfaces.',
            'lessons' => '#13 Lessons',
            'tag' => '#CSS',
            'icon' => 'fa-brands fa-css3-alt',
            'symbol' => 'css',
            'tone' => 'tone-light-sky',
        ],
    ];

    $newCourses = [
        [
            'title' => 'HTML5 + CSS3',
            'detail' => 'Build clean page layouts and styling foundations for modern web pages.',
            'lessons' => '#11 Lessons',
            'tag' => '#HTML5',
            'icon' => 'fa-brands fa-html5',
            'symbol' => 'html',
            'tone' => 'tone-orange',
        ],
        [
            'title' => 'NodeJS Intro',
            'detail' => 'Begin server-side JavaScript and understand API-ready application flow.',
            'lessons' => '#14 Lessons',
            'tag' => '#NodeJS',
            'icon' => 'fa-brands fa-node-js',
            'symbol' => 'node',
            'tone' => 'tone-indigo',
        ],
        [
            'title' => 'UI/UX Design',
            'detail' => 'Explore interface thinking, wireframes, and better user experience basics.',
            'lessons' => '#18 Lessons',
            'tag' => '#UXUI',
            'icon' => 'fa-solid fa-object-group',
            'symbol' => 'ux',
            'tone' => 'tone-cyan',
        ],
        [
            'title' => 'Laravel Starter',
            'detail' => 'Get started with Laravel structure, routing, controllers, and clean CRUD flow.',
            'lessons' => '#16 Lessons',
            'tag' => '#Laravel',
            'icon' => 'fa-brands fa-laravel',
            'symbol' => 'laravel',
            'tone' => 'tone-charcoal',
        ],
    ];
@endphp

@push('web_styles')
    <style>
        .home-static {
            padding: 18px 0 48px;
        }

        .home-stack {
            display: grid;
            gap: 28px;
        }

        .home-banner {
            width: 100vw;
            margin-left: calc(50% - 50vw);
            margin-right: calc(50% - 50vw);
            min-height: 270px;
            border-radius: 0;
            border: 2px solid rgba(122, 92, 255, 0.9);
            background:
                linear-gradient(135deg, rgba(203, 215, 226, 0.96), rgba(183, 199, 214, 0.96)),
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.6), transparent 28%);
            color: #101826;
            display: grid;
            place-items: center;
            text-align: center;
            padding: 32px;
        }

        .home-banner__inner {
            display: grid;
            gap: 10px;
            max-width: 760px;
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
        }

        .home-banner__eyebrow {
            font-size: 12px;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #5f48ff;
            font-weight: 700;
        }

        .home-banner__title {
            margin: 0;
            font-family: var(--font-lato);
            font-size: clamp(28px, 4vw, 52px);
            line-height: 1;
            font-weight: 700;
        }

        .home-banner__text {
            margin: 0;
            font-size: 15px;
            color: #344256;
        }

        .home-section {
            display: grid;
            gap: 14px;
        }

        .home-section__title {
            margin: 0;
            color: #fff;
            font-family: var(--font-lato);
            font-size: clamp(20px, 2.2vw, 30px);
            font-weight: 700;
        }

        .course-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .course-card {
            overflow: hidden;
            border-radius: 18px;
            background: linear-gradient(180deg, rgba(25, 39, 62, 0.96), rgba(17, 27, 44, 0.98));
            border: 1px solid rgba(126, 163, 214, 0.12);
            box-shadow: 0 18px 34px rgba(0, 0, 0, 0.28);
            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        }

        .course-card:hover {
            transform: translateY(-6px);
            border-color: rgba(82, 146, 255, 0.4);
            box-shadow: 0 24px 38px rgba(0, 0, 0, 0.34);
        }

        .course-card__media {
            min-height: 190px;
            position: relative;
            display: grid;
            place-items: center;
            padding: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .course-card__media::before {
            content: '';
            position: absolute;
            inset: 14px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .course-card__badges {
            position: absolute;
            top: 12px;
            right: 12px;
            z-index: 1;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
            max-width: calc(100% - 24px);
        }

        .course-card__tag {
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(16, 24, 38, 0.76);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        .course-card__visual {
            position: relative;
            z-index: 1;
            display: grid;
            justify-items: center;
            gap: 10px;
            text-align: center;
        }

        .course-card__icon {
            color: #f8fbff;
            font-size: 76px;
            line-height: 1;
            text-shadow: 0 12px 22px rgba(0, 0, 0, 0.16);
        }

        .course-card__image-label {
            margin: 0;
            color: rgba(248, 251, 255, 0.96);
            font-family: var(--font-lato);
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.08em;
            line-height: 1;
            text-transform: uppercase;
        }

        .course-card__body {
            padding: 14px 14px 16px;
            display: grid;
            gap: 6px;
            color: #eef5ff;
        }

        .course-card__title {
            margin: 0;
            font-family: var(--font-lato);
            font-size: 20px;
            font-weight: 700;
        }

        .course-card__detail {
            margin: 0;
            font-size: 13px;
            line-height: 1.5;
            color: #aebed3;
        }

        .tone-blue {
            background: linear-gradient(135deg, #4f8bd0 0%, #315f9e 100%);
        }

        .tone-dark {
            background: linear-gradient(135deg, #31353f 0%, #232c39 100%);
        }

        .tone-sky {
            background: linear-gradient(135deg, #5a9bd0 0%, #407db6 100%);
        }

        .tone-orange {
            background: linear-gradient(135deg, #f0712f 0%, #d55618 100%);
        }

        .tone-indigo {
            background: linear-gradient(135deg, #223f88 0%, #1a2f63 100%);
        }

        .tone-cyan {
            background: linear-gradient(135deg, #1f6288 0%, #174b68 100%);
        }

        .tone-light-sky {
            background: linear-gradient(135deg, #8fc1eb 0%, #6ea7d7 100%);
        }

        .tone-charcoal {
            background: linear-gradient(135deg, #2f2f34 0%, #22242a 100%);
        }

        @media (max-width: 980px) {
            .course-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            .home-static {
                padding: 12px 0 36px;
            }

            .home-banner {
                min-height: 210px;
                padding: 20px 0;
            }

            .course-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .course-card__media {
                min-height: 135px;
            }

            .course-card__icon {
                font-size: 56px;
            }
        }
    </style>
@endpush

@section('content')
    <section class="home-static">
        <div class="web-container home-stack">
            <section class="home-banner" aria-label="Home banner">
                <div class="home-banner__inner">
                    <span class="home-banner__eyebrow">TechCourse Home</span>
                    <h1 class="home-banner__title">Banner</h1>
                    <p class="home-banner__text">
                        Static home preview for your frontend. Later you can connect banner content and course data with backend.
                    </p>
                </div>
            </section>

            <section class="home-section" aria-labelledby="popular-course-title">
                <h2 class="home-section__title" id="popular-course-title">Popular Course</h2>
                <div class="course-grid">
                    @foreach ($popularCourses as $course)
                        <article class="course-card">
                            <div class="course-card__media {{ $course['tone'] }}">
                                <div class="course-card__badges">
                                    <span class="course-card__tag">{{ $course['lessons'] }}</span>
                                    <span class="course-card__tag">{{ $course['tag'] }}</span>
                                </div>
                                <div class="course-card__visual">
                                    <i class="course-card__icon {{ $course['icon'] }}"></i>
                                    <p class="course-card__image-label">{{ $course['symbol'] }}</p>
                                </div>
                            </div>
                            <div class="course-card__body">
                                <h3 class="course-card__title">{{ $course['title'] }}</h3>
                                <p class="course-card__detail">{{ $course['detail'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="home-section" aria-labelledby="new-course-title">
                <h2 class="home-section__title" id="new-course-title">New Course</h2>
                <div class="course-grid">
                    @foreach ($newCourses as $course)
                        <article class="course-card">
                            <div class="course-card__media {{ $course['tone'] }}">
                                <div class="course-card__badges">
                                    <span class="course-card__tag">{{ $course['lessons'] }}</span>
                                    <span class="course-card__tag">{{ $course['tag'] }}</span>
                                </div>
                                <div class="course-card__visual">
                                    <i class="course-card__icon {{ $course['icon'] }}"></i>
                                    <p class="course-card__image-label">{{ $course['symbol'] }}</p>
                                </div>
                            </div>
                            <div class="course-card__body">
                                <h3 class="course-card__title">{{ $course['title'] }}</h3>
                                <p class="course-card__detail">{{ $course['detail'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>
    </section>
@endsection
