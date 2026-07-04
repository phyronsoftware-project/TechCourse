@extends('web.layouts.app')

@section('title', app()->getLocale() === 'km' ? 'បច្ចេកវិទ្យា' : 'Technology')

@section('content')
    @php
        // Render category boxes first because the user wants category-based navigation.
        $isKhmer = app()->getLocale() === 'km';
    @endphp

    <style>
        .technology-shell {
            width: min(1280px, calc(100% - 32px));
            margin: 0 auto;
            padding: 24px 0 56px;
        }

        .technology-head {
            text-align: center;
            margin-bottom: 30px;
        }

        .technology-title {
            margin: 0;
            color: #0f172a;
            font-family: var(--font-lato);
            font-size: clamp(2rem, 3vw, 2.9rem);
            line-height: 1.12;
        }

        .technology-copy {
            max-width: 760px;
            margin: 12px auto 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.9;
        }

        .technology-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 18px;
        }

        /* Show category cards in the same box direction as the user reference. */
        .technology-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100%;
            padding: 24px 14px 18px;
            border: 1px dashed #dde7f4;
            background: #ffffff;
            text-align: center;
            text-decoration: none;
            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        }

        .technology-card:hover {
            transform: translateY(-6px);
            border-color: #b8cff7;
            box-shadow: 0 20px 34px rgba(15, 23, 42, 0.08);
        }

        .technology-card__icon {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: linear-gradient(180deg, #edf4ff 0%, #dbeafe 100%);
            color: #2563eb;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }

        .technology-card__name {
            margin: 18px 0 0;
            color: #111827;
            font-size: 16px;
            font-weight: 800;
            line-height: 1.45;
        }

        .technology-card__subtitle {
            margin: 8px 0 0;
            color: #64748b;
            font-size: 12px;
            line-height: 1.8;
            min-height: 40px;
        }

        .technology-card__action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 120px;
            margin-top: 16px;
            padding: 10px 14px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            font-size: 13px;
            font-weight: 800;
            line-height: 1;
        }

        .technology-card__hint {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
            color: #2563eb;
            font-size: 12px;
            font-weight: 700;
        }

        .technology-empty {
            padding: 36px 24px;
            border: 1px dashed #cfdcf0;
            background: #ffffff;
            color: #64748b;
            text-align: center;
            line-height: 1.9;
        }

        @media (max-width: 1280px) {
            .technology-grid {
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }
        }

        @media (max-width: 1120px) {
            .technology-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        @media (max-width: 760px) {
            .technology-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 16px;
            }

            .technology-card {
                padding: 20px 12px 16px;
            }

            .technology-card__icon {
                width: 62px;
                height: 62px;
                font-size: 25px;
            }
        }

        @media (max-width: 480px) {
            .technology-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>

    <section class="technology-shell">
        <div class="technology-head">
            <h1 class="technology-title">{{ $isKhmer ? 'បច្ចេកវិទ្យា' : 'Technology' }}</h1>
            <p class="technology-copy">
                {{ $isKhmer ? 'ចុចលើ category box នីមួយៗ ដើម្បីចូលទៅមើល technology detail នៅក្នុង page បន្ទាប់។' : 'Click each category box to open the next page and view the technology details inside it.' }}
            </p>
        </div>

        @if (($techCategories ?? collect())->isEmpty())
            <div class="technology-empty">
                {{ $isKhmer ? 'មិនទាន់មាន technology category នៅក្នុង database នៅឡើយទេ។ សូមបន្ថែមតាម Admin Dashboard ជាមុន។' : 'There are no technology categories in the database yet. Please add them from the admin dashboard first.' }}
            </div>
        @else
            <div class="technology-grid">
                @foreach ($techCategories as $category)
                    {{-- Each box is one public category card that opens its own detail page. --}}
                    <a href="{{ route('technology.category.show', $category) }}" class="technology-card">
                        <span class="technology-card__icon">
                            <i class="{{ $category->icon ?: 'fa-solid fa-microchip' }}"></i>
                        </span>

                        <h2 class="technology-card__name">{{ $category->name }}</h2>

                        <p class="technology-card__subtitle">
                            {{ $category->subtitle ?: ($isKhmer ? 'ចូលមើលព័ត៌មាន technology នៅក្នុង category នេះ។' : 'Open the technologies inside this category.') }}
                        </p>

                        <span class="technology-card__action">
                            {{ $isKhmer ? 'មើលលម្អិត' : 'View Detail' }}
                        </span>

                        <span class="technology-card__hint">
                            {{ $isKhmer ? 'ចូលទៅកាន់ page បន្ទាប់' : 'Go to detail page' }}
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
@endsection
