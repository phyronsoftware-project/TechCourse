@extends('web.layouts.app')

@section('title', $category->name)

@section('content')
    @php
        // Keep the category detail page clean and text-focused like the provided reference.
        $isKhmer = app()->getLocale() === 'km';
    @endphp

    <style>
        .tech-detail-shell {
            width: min(1040px, calc(100% - 32px));
            margin: 0 auto;
            padding: 28px 0 64px;
        }

        .tech-detail-back {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
            color: #2563eb;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }

        .tech-detail-heading {
            margin-bottom: 18px;
        }

        .tech-detail-category {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #2563eb;
            font-size: 14px;
            font-weight: 800;
        }

        .tech-detail-title {
            margin: 12px 0 0;
            color: #0f172a;
            font-family: var(--font-lato);
            font-size: clamp(2rem, 3vw, 3rem);
            line-height: 1.12;
        }

        .tech-detail-copy {
            margin: 14px 0 0;
            color: #64748b;
            font-size: 15px;
            line-height: 1.95;
        }

        .tech-detail-list {
            margin-top: 26px;
            border-top: 1px solid #e5e7eb;
        }

        /* Show each technology as clean text sections without cards or boxes. */
        .tech-detail-item {
            padding: 38px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .tech-detail-item__title {
            margin: 0;
            color: #111827;
            font-size: clamp(1.8rem, 2.5vw, 2.3rem);
            font-weight: 900;
            line-height: 1.25;
        }

        .tech-detail-item__subtitle {
            margin: 18px 0 0;
            color: #111827;
            font-size: 18px;
            font-weight: 800;
            line-height: 1.6;
        }

        .tech-detail-item__body {
            margin: 18px 0 0;
            color: #4b5563;
            font-size: 15px;
            line-height: 2;
            white-space: pre-line;
        }

        .tech-detail-item__link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 18px;
            color: #2563eb;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }

        .tech-detail-empty {
            margin-top: 26px;
            padding: 24px 0;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
            color: #64748b;
            font-size: 15px;
            line-height: 1.9;
        }

        @media (max-width: 640px) {
            .tech-detail-item {
                padding: 28px 0;
            }

            .tech-detail-item__title {
                font-size: 1.7rem;
            }
        }
    </style>

    <section class="tech-detail-shell">
        <a href="{{ route('service') }}" class="tech-detail-back">
            <i class="fa-solid fa-arrow-left"></i>
            {{ $isKhmer ? 'ត្រឡប់ទៅបញ្ជី Category' : 'Back to Category List' }}
        </a>

        <div class="tech-detail-heading">
            <div class="tech-detail-category">
                <i class="{{ $category->icon ?: 'fa-solid fa-microchip' }}"></i>
                <span>{{ $category->name }}</span>
            </div>

            <h1 class="tech-detail-title">{{ $category->name }}</h1>

            @if ($category->subtitle)
                <p class="tech-detail-copy">{{ $category->subtitle }}</p>
            @endif
        </div>

        @if ($category->technologies->isEmpty())
            <div class="tech-detail-empty">
                {{ $isKhmer ? 'Category នេះមិនទាន់មាន detail នៅឡើយទេ។ សូមបន្ថែម Tech Detail ក្នុង Admin Dashboard ជាមុន។' : 'This category does not have detail content yet. Please add Tech Detail rows from the admin dashboard first.' }}
            </div>
        @else
            <div class="tech-detail-list">
                @foreach ($category->technologies as $technology)
                    {{-- Render the category details as numbered content sections. --}}
                    <article class="tech-detail-item">
                        <h2 class="tech-detail-item__title">
                            {{ $loop->iteration }}. {{ $technology->name }}
                        </h2>

                        @if ($technology->title)
                            <p class="tech-detail-item__subtitle">{{ $technology->title }}</p>
                        @endif

                        <div class="tech-detail-item__body">
                            {{ $technology->detail ?: ($isKhmer ? 'មិនទាន់មានអត្ថបទលម្អិតសម្រាប់ផ្នែកនេះនៅឡើយទេ។' : 'No detailed content has been added for this section yet.') }}
                        </div>

                        @if ($technology->website_url)
                            <a href="{{ $technology->website_url }}" target="_blank" rel="noopener noreferrer" class="tech-detail-item__link">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                {{ $isKhmer ? 'ចូលមើល Website' : 'Visit Website' }}
                            </a>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
