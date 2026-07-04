@extends('web.layouts.app')

@section('title', $category->name)

@section('content')
    @php
        // Keep category detail page focused on technologies inside the selected category.
        $isKhmer = app()->getLocale() === 'km';
    @endphp

    <style>
        .tech-category-shell {
            width: min(1200px, calc(100% - 32px));
            margin: 0 auto;
            padding: 24px 0 56px;
        }

        .tech-category-back {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            color: #2563eb;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }

        .tech-category-hero {
            padding: 30px;
            border: 1px solid #dbe8f6;
            border-radius: 30px;
            background: linear-gradient(135deg, #f8fbff 0%, #eef5ff 100%);
            box-shadow: 0 18px 34px rgba(15, 23, 42, 0.06);
        }

        .tech-category-badge {
            width: 84px;
            height: 84px;
            border-radius: 24px;
            background: linear-gradient(180deg, #edf4ff 0%, #dbeafe 100%);
            color: #2563eb;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
        }

        .tech-category-title {
            margin: 18px 0 0;
            color: #0f172a;
            font-family: var(--font-lato);
            font-size: clamp(2rem, 3vw, 3rem);
            line-height: 1.12;
        }

        .tech-category-copy {
            margin: 12px 0 0;
            color: #64748b;
            font-size: 15px;
            line-height: 1.95;
        }

        .tech-category-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 22px;
            margin-top: 28px;
        }

        .tech-item-card {
            display: block;
            padding: 26px 18px 20px;
            border: 1px dashed #dde7f4;
            background: #ffffff;
            text-align: center;
            text-decoration: none;
            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        }

        .tech-item-card:hover {
            transform: translateY(-5px);
            border-color: #bfd3f6;
            box-shadow: 0 16px 30px rgba(15, 23, 42, 0.07);
        }

        .tech-item-card__icon {
            width: 82px;
            height: 82px;
            margin: 0 auto;
            border-radius: 22px;
            background: linear-gradient(180deg, #edf4ff 0%, #dbeafe 100%);
            color: #2563eb;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }

        .tech-item-card__name {
            margin: 22px 0 0;
            color: #111827;
            font-size: 17px;
            font-weight: 800;
            line-height: 1.45;
        }

        .tech-item-card__title {
            margin: 10px 0 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.8;
            min-height: 44px;
        }

        .tech-item-card__action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 142px;
            margin-top: 20px;
            padding: 12px 18px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            font-size: 14px;
            font-weight: 800;
            line-height: 1;
        }

        .tech-item-card__hint {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 16px;
            color: #2563eb;
            font-size: 13px;
            font-weight: 700;
        }

        .tech-category-empty {
            margin-top: 28px;
            padding: 32px 24px;
            border: 1px dashed #cfdcf0;
            background: #ffffff;
            color: #64748b;
            text-align: center;
            line-height: 1.9;
        }

        .tech-category-section {
            margin-top: 34px;
        }

        .tech-category-section__title {
            margin: 0 0 16px;
            color: #0f172a;
            font-size: 22px;
            font-weight: 800;
        }

        .tech-other-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .tech-other-card {
            padding: 18px;
            border: 1px solid #e4edf8;
            background: #ffffff;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .tech-other-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.07);
        }

        .tech-other-card__name {
            margin: 0;
            color: #0f172a;
            font-size: 16px;
            font-weight: 800;
        }

        .tech-other-card__subtitle {
            margin: 8px 0 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.75;
        }

        @media (max-width: 1120px) {
            .tech-category-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 820px) {
            .tech-category-grid,
            .tech-other-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 520px) {
            .tech-category-grid,
            .tech-other-grid {
                grid-template-columns: 1fr;
            }

            .tech-category-hero {
                padding: 24px 18px;
            }
        }
    </style>

    <section class="tech-category-shell">
        <a href="{{ route('service') }}" class="tech-category-back">
            <i class="fa-solid fa-arrow-left"></i>
            {{ $isKhmer ? 'ត្រឡប់ទៅបញ្ជី Category' : 'Back to Category List' }}
        </a>

        <div class="tech-category-hero">
            <span class="tech-category-badge">
                <i class="{{ $category->icon ?: 'fa-solid fa-microchip' }}"></i>
            </span>

            <h1 class="tech-category-title">{{ $category->name }}</h1>
            <p class="tech-category-copy">
                {{ $category->subtitle ?: ($isKhmer ? 'ជ្រើសរើស technology នៅក្នុង category នេះ ដើម្បីមើលព័ត៌មានលម្អិតបន្ថែម។' : 'Choose a technology inside this category to see more detailed information.') }}
            </p>
        </div>

        @if ($category->technologies->isEmpty())
            <div class="tech-category-empty">
                {{ $isKhmer ? 'Category នេះមិនទាន់មាន technology detail នៅឡើយទេ។ ដូច្នេះនៅ list page អ្នកឃើញ category 2 តែ content ខាងក្នុងមិនទាន់មាន។' : 'This category does not have technology details yet. That is why you could see the categories, but there was no inside content to show before.' }}
            </div>
        @else
            <div class="tech-category-grid">
                @foreach ($category->technologies as $technology)
                    {{-- Each technology in this category opens its own detail page. --}}
                    <a href="{{ route('technology.show', $technology) }}" class="tech-item-card">
                        <span class="tech-item-card__icon">
                            <i class="{{ $category->icon ?: 'fa-solid fa-microchip' }}"></i>
                        </span>

                        <h2 class="tech-item-card__name">{{ $technology->name }}</h2>
                        <p class="tech-item-card__title">{{ $technology->title ?: ($isKhmer ? 'Technology Detail' : 'Technology Detail') }}</p>

                        <span class="tech-item-card__action">
                            {{ $isKhmer ? 'មើលលម្អិត' : 'View Detail' }}
                        </span>

                        <span class="tech-item-card__hint">
                            {{ $isKhmer ? 'ចូលទៅកាន់ page បន្ទាប់' : 'Go to detail page' }}
                        </span>
                    </a>
                @endforeach
            </div>
        @endif

        @if ($otherCategories->isNotEmpty())
            <div class="tech-category-section">
                <h2 class="tech-category-section__title">{{ $isKhmer ? 'Category ផ្សេងទៀត' : 'Other Categories' }}</h2>

                <div class="tech-other-grid">
                    @foreach ($otherCategories as $item)
                        {{-- Keep quick links to other categories from the detail page. --}}
                        <a href="{{ route('technology.category.show', $item) }}" class="tech-other-card">
                            <h3 class="tech-other-card__name">{{ $item->name }}</h3>
                            <p class="tech-other-card__subtitle">{{ $item->subtitle ?: '-' }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endsection
