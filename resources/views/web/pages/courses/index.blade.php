@extends('web.layouts.app')

@section('title', __('Courses'))

@php
    $featuredCourse = $courses->first();
    $activeCategory = request('category') ?: ($featuredCourse?->category?->name ?: __('All Categories'));
    $sortLabel = match (request('sort')) {
        'title' => __('Title'),
        'price_low' => __('Price Low'),
        'price_high' => __('Price High'),
        default => __('Latest'),
    };
    $currentCategoryLabel = $categories->firstWhere('slug', request('category'))?->name
        ?? $categories->firstWhere('name', request('category'))?->name
        ?? __('All Categories');
@endphp

@section('content')
    <style>
        .course-index {
            width: min(1320px, calc(100% - 32px));
            margin: 0 auto;
            display: grid;
            gap: 16px;
            padding-bottom: 40px;
        }

        .course-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .course-toolbar-menu {
            position: relative;
        }

        .course-toolbar-trigger {
            min-height: 38px;
            padding: 0 12px;
            border-radius: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            background: #f8fafc;
            border: 1px solid #d9e4ef;
            color: #111827;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: 0.2s ease;
            cursor: pointer;
        }

        .course-toolbar-trigger:hover,
        .course-toolbar-menu[open] .course-toolbar-trigger {
            background: #dfe9f5;
            color: #0f2f57;
        }

        .course-toolbar-menu--sort {
            margin-left: auto;
        }

        .course-toolbar-trigger::marker,
        .course-toolbar-trigger::-webkit-details-marker {
            display: none;
        }

        .course-toolbar-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            min-width: 210px;
            padding: 8px;
            border-radius: 14px;
            background: #ffffff;
            border: 1px solid #dfe7f0;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.1);
            display: grid;
            gap: 4px;
            z-index: 15;
        }

        .course-toolbar-menu--sort .course-toolbar-dropdown {
            left: auto;
            right: 0;
            min-width: 180px;
        }

        .course-toolbar-option {
            min-height: 36px;
            padding: 0 11px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            color: #0f172a;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
        }

        .course-toolbar-option:hover,
        .course-toolbar-option.is-active {
            background: #eff4fa;
            color: #0f2f57;
        }

        .course-grid-shell {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .course-page-intro {
            text-align: center;
            padding: 10px 0 26px;
        }

        .course-page-heading {
            margin: 0;
            color: #0f172a;
            font-family: var(--font-lato);
            font-size: 2rem;
            line-height: 1.2;
        }

        .course-page-subtitle {
            margin: 12px 0 0;
            color: #64748b;
            font-size: 16px;
            line-height: 1.7;
        }

        .locale-km .course-page-heading {
            font-size: 27px;
            text-align: center;
            font-family: 'Noto Sans Khmer', sans-serif;
        }

        .course-index .course-card {
            display: flex;
            flex-direction: column;
            min-height: 100%;
            border-radius: 18px;
            overflow: hidden;
            text-decoration: none;
            background: #ffffff;
            border: 1px solid #dbe6f1;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .course-index .course-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 28px rgba(15, 23, 42, 0.1);
        }

        .course-index .course-card__media {
            position: relative;
            height: 178px;
            min-height: 178px;
            background: #f8fafc;
            border-bottom: 1px solid #e7eef5;
        }

        .course-index .course-card__media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .course-index .course-card__media--fallback {
            display: grid;
            place-items: center;
            width: 100%;
            height: 100%;
            color: #5b708a;
            font-size: 48px;
        }

        .course-index .course-card__badges {
            top: 9px;
            right: 9px;
            gap: 5px;
        }

        .course-index .course-card__badge {
            min-height: 20px;
            padding: 0 7px;
            border-radius: 7px;
            background: rgba(21, 31, 48, 0.9);
            color: #fff;
            font-size: 9px;
            font-weight: 700;
        }

        .course-index .course-card__body {
            display: grid;
            gap: 6px;
            padding: 12px 12px 12px;
        }

        .course-index .course-card__meta {
            margin-bottom: 2px;
            color: #64748b;
            font-size: 10px;
        }

        .course-index .course-card__title {
            margin: 0;
            color: #0f172a;
            font-size: 0.92rem;
            line-height: 1.36;
            font-family: var(--font-lato);
        }

        .course-index .course-card__copy {
            margin: 0 0 2px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.52;
            min-height: 44px;
        }

        .course-card__info-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            color: #64748b;
            font-size: 12px;
        }

        .course-card__info-row strong {
            color: #0f172a;
            font-size: 12px;
        }

        .course-price-badge {
            min-height: 22px;
            padding: 0 9px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
        }

        .course-price-badge.is-free {
            background: #e6f8ee;
            color: #157347;
        }

        .course-price-badge.is-paid {
            background: #eef4ff;
            color: #1d4ed8;
        }

        .course-empty {
            min-height: 220px;
            display: grid;
            place-items: center;
            text-align: center;
            color: #64748b;
        }

        .web-pagination-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 18px;
        }

        .web-pagination-pages {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .web-page-btn {
            min-width: 36px;
            height: 36px;
            padding: 0 10px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            border: 1px solid #dbe6f1;
            color: #0f172a;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
        }

        .web-page-btn.is-active {
            background: #1d4ed8;
            border-color: #1d4ed8;
            color: #ffffff;
        }

        .web-page-btn.is-muted,
        .web-page-btn.is-disabled {
            color: #94a3b8;
            background: #f8fafc;
        }

        @media (max-width: 1180px) {
            .course-grid-shell {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 820px) {
            .course-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .course-toolbar-menu--sort {
                margin-left: 0;
                width: fit-content;
            }

            .course-grid-shell {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 560px) {
            .course-index {
                width: min(100%, calc(100% - 20px));
            }

            .course-grid-shell {
                grid-template-columns: 1fr;
                gap: 18px;
            }
        }
    </style>

    <section class="course-index">
        <div class="course-page-intro">
            <h1 class="course-page-heading">{{ __('Courses') }}</h1>
            <p class="course-page-subtitle">{{ __('This is my course learning program.') }}</p>
        </div>

        <section class="course-toolbar" aria-label="Course tools">
            <details class="course-toolbar-menu">
                <summary class="course-toolbar-trigger">
                    <i class="fa-solid fa-filter"></i>
                    {{ request()->filled('category') ? $currentCategoryLabel : __('All Categories') }}
                    <i class="fa-solid fa-chevron-down"></i>
                </summary>

                <div class="course-toolbar-dropdown">
                    <a href="{{ route('courses.index', array_filter(['sort' => request('sort')])) }}" class="course-toolbar-option {{ request()->filled('category') ? '' : 'is-active' }}">
                        <span>{{ __('All Categories') }}</span>
                        @unless (request()->filled('category'))
                            <i class="fa-solid fa-check"></i>
                        @endunless
                    </a>
                    @foreach ($categories as $category)
                        @php
                            $categoryValue = $category->slug ?: $category->name;
                            $isActiveCategory = request('category') == $categoryValue;
                        @endphp
                        <a href="{{ route('courses.index', array_filter(['category' => $categoryValue, 'sort' => request('sort')])) }}" class="course-toolbar-option {{ $isActiveCategory ? 'is-active' : '' }}">
                            <span>{{ $category->name }}</span>
                            @if ($isActiveCategory)
                                <i class="fa-solid fa-check"></i>
                            @endif
                        </a>
                    @endforeach
                </div>
            </details>

            <details class="course-toolbar-menu course-toolbar-menu--sort">
                <summary class="course-toolbar-trigger">
                    <i class="fa-solid fa-arrow-down-wide-short"></i>
                    {{ __('Sort') }}: {{ $sortLabel }}
                    <i class="fa-solid fa-chevron-down"></i>
                </summary>

                <div class="course-toolbar-dropdown">
                    <a href="{{ route('courses.index', array_filter(['category' => request('category')])) }}" class="course-toolbar-option {{ ! request('sort') ? 'is-active' : '' }}">
                        <span>{{ __('Latest') }}</span>
                        @unless (request('sort'))
                            <i class="fa-solid fa-check"></i>
                        @endunless
                    </a>
                    <a href="{{ route('courses.index', array_filter(['category' => request('category'), 'sort' => 'title'])) }}" class="course-toolbar-option {{ request('sort') === 'title' ? 'is-active' : '' }}">
                        <span>{{ __('Title') }}</span>
                        @if (request('sort') === 'title')
                            <i class="fa-solid fa-check"></i>
                        @endif
                    </a>
                    <a href="{{ route('courses.index', array_filter(['category' => request('category'), 'sort' => 'price_low'])) }}" class="course-toolbar-option {{ request('sort') === 'price_low' ? 'is-active' : '' }}">
                        <span>{{ __('Price Low') }}</span>
                        @if (request('sort') === 'price_low')
                            <i class="fa-solid fa-check"></i>
                        @endif
                    </a>
                    <a href="{{ route('courses.index', array_filter(['category' => request('category'), 'sort' => 'price_high'])) }}" class="course-toolbar-option {{ request('sort') === 'price_high' ? 'is-active' : '' }}">
                        <span>{{ __('Price High') }}</span>
                        @if (request('sort') === 'price_high')
                            <i class="fa-solid fa-check"></i>
                        @endif
                    </a>
                </div>
            </details>
        </section>

        <section aria-labelledby="course-list-title">
            @if ($courses->count())
                <div class="course-grid-shell">
                    @foreach ($courses as $course)
                        @php
                            $lessonCount = $course->lessons_count ?? $course->total_lessons ?? 0;
                            $resourceCount = $course->resources_count ?? 0;
                            $priceText = $course->is_free ? __('Free') : (($course->currency ?: '$') . number_format((float) $course->price, 2));
                        @endphp

                        <a href="{{ route('courses.show', $course->slug ?: $course->id) }}" class="course-card">
                            <div class="course-card__media">
                                <div class="course-card__badges">
                                    <span class="course-card__badge">{{ $lessonCount }} {{ __('Lessons') }}</span>
                                    <span class="course-card__badge">{{ __(\Illuminate\Support\Str::headline($course->level ?: 'Beginner')) }}</span>
                                </div>

                                @if ($course->thumbnail_url)
                                    <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}">
                                @else
                                    <div class="course-card__media--fallback">
                                        <i class="fa-solid fa-laptop-code" aria-hidden="true"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="course-card__body">
                                <div class="course-card__meta">
                                    <span>{{ $course->category?->name ?: __('General') }}</span>
                                    <span>{{ $course->language ?: __('Khmer') }}</span>
                                </div>

                                <h3 class="course-card__title course-card__title--small">{{ $course->title }}</h3>
                                <p class="course-card__copy">
                                    {{ $course->short_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $course->description), 120) }}
                                </p>

                                <div class="course-card__info-row course-card__info-row--first">
                                    <span>{{ __('Total Resource') }}</span>
                                    <strong>{{ $resourceCount }}</strong>
                                </div>

                                <div class="course-card__info-row">
                                    <span>{{ __('Price') }}</span>
                                    <span class="course-price-badge {{ $course->is_free ? 'is-free' : 'is-paid' }}">
                                        {{ $course->is_free ? __('Free') : $priceText }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if ($courses->hasPages())
                    <div style="position:relative;z-index:1;display:flex;justify-content:center;margin-top:18px;">
                        {{ $courses->links('vendor.pagination.web') }}
                    </div>
                @endif
            @else
                <div class="course-empty">
                    <div>
                        <h3 style="margin:0 0 10px;color:#0f172a;">{{ __('No course available yet') }}</h3>
                        <p style="margin:0;">{{ __('Once you add published courses, they will appear here in this course layout.') }}</p>
                    </div>
                </div>
            @endif
        </section>
    </section>
@endsection
