@extends('web.layouts.app')

@section('title', $technology->name)

@section('content')
    @php
        // Keep technology detail readable and simple for public users.
        $isKhmer = app()->getLocale() === 'km';
    @endphp

    <style>
        .tech-detail-shell {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
            padding: 24px 0 56px;
        }

        .tech-detail-back {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            color: #2563eb;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }

        .tech-detail-hero {
            padding: 32px;
            border: 1px solid #dbe8f6;
            border-radius: 30px;
            background: linear-gradient(135deg, #f8fbff 0%, #eef5ff 100%);
            box-shadow: 0 20px 36px rgba(15, 23, 42, 0.06);
        }

        .tech-detail-category {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.08);
            color: #2563eb;
            font-size: 13px;
            font-weight: 800;
        }

        .tech-detail-title {
            margin: 18px 0 0;
            color: #0f172a;
            font-family: var(--font-lato);
            font-size: clamp(2rem, 3vw, 3rem);
            line-height: 1.14;
        }

        .tech-detail-subtitle {
            margin: 12px 0 0;
            color: #2563eb;
            font-size: 15px;
            font-weight: 800;
        }

        .tech-detail-copy {
            margin: 18px 0 0;
            color: #475569;
            font-size: 15px;
            line-height: 1.95;
            white-space: pre-line;
        }

        .tech-detail-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 24px;
        }

        .tech-detail-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 170px;
            padding: 14px 20px;
            border-radius: 16px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 800;
        }

        .tech-detail-btn--primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
        }

        .tech-detail-btn--secondary {
            border: 1px solid #cddcf0;
            background: #ffffff;
            color: #334155;
        }

        .tech-detail-section {
            margin-top: 28px;
        }

        .tech-detail-section__title {
            margin: 0 0 18px;
            color: #0f172a;
            font-size: 24px;
            font-weight: 800;
        }

        .tech-detail-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .tech-related-card {
            padding: 20px;
            border: 1px solid #e4edf8;
            background: #ffffff;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .tech-related-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.07);
        }

        .tech-related-card__name {
            margin: 0;
            color: #0f172a;
            font-size: 17px;
            font-weight: 800;
        }

        .tech-related-card__title {
            margin: 8px 0 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.7;
        }

        @media (max-width: 860px) {
            .tech-detail-grid {
                grid-template-columns: 1fr;
            }

            .tech-detail-hero {
                padding: 24px 20px;
            }
        }
    </style>

    <section class="tech-detail-shell">
        <a href="{{ route('service', $technology->category_id ? ['category' => $technology->category_id] : []) }}" class="tech-detail-back">
            <i class="fa-solid fa-arrow-left"></i>
            {{ $isKhmer ? 'ត្រឡប់ទៅបញ្ជី Technology' : 'Back to Technology List' }}
        </a>

        <div class="tech-detail-hero">
            <div class="tech-detail-category">
                <i class="{{ $technology->category?->icon ?: 'fa-solid fa-microchip' }}"></i>
                <span>{{ $technology->category?->name ?: ($isKhmer ? 'Technology' : 'Technology') }}</span>
            </div>

            <h1 class="tech-detail-title">{{ $technology->name }}</h1>

            @if ($technology->title)
                <p class="tech-detail-subtitle">{{ $technology->title }}</p>
            @endif

            <div class="tech-detail-copy">
                {{ $technology->detail ?: ($isKhmer ? 'មិនទាន់មាន detail សម្រាប់ technology នេះនៅឡើយទេ។ អ្នកអាចបន្ថែមបន្តនៅ Admin Dashboard។' : 'No detail has been added for this technology yet. You can continue adding it from the admin dashboard.') }}
            </div>

            <div class="tech-detail-actions">
                @if ($technology->website_url)
                    <a href="{{ $technology->website_url }}" target="_blank" rel="noopener noreferrer" class="tech-detail-btn tech-detail-btn--primary">
                        {{ $isKhmer ? 'ចូលមើល Website' : 'Visit Website' }}
                    </a>
                @endif

                <a href="{{ route('service', $technology->category_id ? ['category' => $technology->category_id] : []) }}" class="tech-detail-btn tech-detail-btn--secondary">
                    {{ $isKhmer ? 'មើល Technology ផ្សេងទៀត' : 'View More Technology' }}
                </a>
            </div>
        </div>

        @if ($relatedTechnologies->isNotEmpty())
            <div class="tech-detail-section">
                <h2 class="tech-detail-section__title">{{ $isKhmer ? 'Technology ទាក់ទង' : 'Related Technology' }}</h2>

                <div class="tech-detail-grid">
                    @foreach ($relatedTechnologies as $item)
                        {{-- Show quick related cards so users can continue browsing. --}}
                        <a href="{{ route('technology.show', $item) }}" class="tech-related-card">
                            <h3 class="tech-related-card__name">{{ $item->name }}</h3>
                            <p class="tech-related-card__title">{{ $item->title ?: ($item->category?->name ?: '-') }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endsection
