@extends('web.layouts.app')

@section('title', __('Subscription Plans'))

@section('content')
    <style>
        /* Let the page background show around the plans instead of enclosing them in a box. */
        .plans-shell { width: min(1280px, calc(100% - 32px)); margin: 28px auto 58px; padding: 0 0 64px; }
        .plans-head { max-width: 760px; margin: 0 0 28px; text-align: left; }
        .plans-head h1 { margin: 0; color: #0f172a; font-size: clamp(1.9rem, 4vw, 2.9rem); line-height: 1.2; font-weight: 750; }
        .plans-head p { margin: 10px 0 0; color: #64748b; line-height: 1.65; }
        .plans-current { margin: 0 0 24px; padding: 18px; border-radius: 18px; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; }
        .plans-current strong { display: block; margin-bottom: 5px; }
        .plans-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 22px; align-items: stretch; }
        .plans-empty { grid-column: 1 / -1; min-height: 260px; padding: 32px; display: grid; place-items: center; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 24px; color: #64748b; text-align: center; }
        .plans-empty i { display: block; margin-bottom: 14px; color: #2563eb; font-size: 2rem; }
        .plans-empty h2 { margin: 0 0 8px; color: #0f172a; font-size: 1.25rem; }
        .plans-empty p { margin: 0; }
        /* Match TechCourse's white, navy, and blue pricing-card palette. */
        .plan-card {
            position: relative;
            display: flex;
            flex-direction: column;
            min-width: 0;
            padding: 29px;
            background: #ffffff;
            border: 1px solid #dbe6f1;
            border-radius: 28px;
            box-shadow: 0 14px 30px rgba(15, 23, 42, .06);
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .plan-card:hover { transform: translateY(-4px); box-shadow: 0 20px 38px rgba(15, 23, 42, .1); }
        .plan-card.is-featured { padding: 27px; background: #173f87; border: 3px solid #2563eb; box-shadow: 0 18px 38px rgba(29, 78, 216, .16); }
        .plan-card.is-light { background: #eff6ff; border-color: #cfe0f8; }
        .plan-card__featured {
            position: absolute;
            top: 19px;
            right: 19px;
            padding: 6px 13px;
            border-radius: 999px;
            background: #dbeafe;
            color: #173f87;
            font-size: .72rem;
            font-weight: 800;
        }
        .plan-card h2 { margin: 0; color: #0f172a; font-size: 1.28rem; line-height: 1.4; font-weight: 750; }
        .plan-card.is-featured h2 { padding-right: 76px; }
        .plan-card__copy { margin: 14px 0 0; color: #64748b; font-size: .9rem; line-height: 1.65; }
        .plan-card.is-featured h2 { color: #ffffff; }
        .plan-card.is-featured .plan-card__copy { color: #dbeafe; }

        /* Show real prices and duration directly on each card, as in the reference. */
        .plan-card__pricing { margin-top: 20px; }
        .plan-card__price { margin: 0 0 3px; color: #0f172a; font-size: clamp(2.1rem, 2.8vw, 2.75rem); line-height: 1.12; font-weight: 750; letter-spacing: -.035em; }
        .plan-card.is-featured .plan-card__price { color: #bfdbfe; }
        .plan-card__period { color: #64748b; font-size: .82rem; }
        .plan-card.is-featured .plan-card__period { color: #dbeafe; }
        .plan-card__courses { margin: 21px 0 28px; padding: 21px 0 0; border-top: 1px dashed #cbd5e1; list-style: none; display: grid; gap: 12px; color: #334155; font-size: .88rem; line-height: 1.55; }
        .plan-card.is-featured .plan-card__courses { border-top-color: #8bb7f1; color: #ffffff; }
        .plan-card__courses li { display: flex; align-items: flex-start; gap: 11px; }
        .plan-card__courses li::before { content: '✓'; flex: none; display: grid; place-items: center; width: 22px; height: 22px; margin-top: 1px; border-radius: 50%; background: #dbeafe; color: #1d4ed8; font-size: .72rem; font-weight: 900; }
        .plan-card__courses li.is-pending::before { content: '–'; background: #e2e8f0; color: #475569; }
        .plan-card.is-featured .plan-card__courses li.is-pending::before { background: rgba(255, 255, 255, .2); color: #ffffff; }

        /* Keep the pill layout while using the site's blue call-to-action color. */
        .plan-card__action { min-height: 48px; min-width: 190px; width: fit-content; margin: auto auto 0; padding: 0 24px; display: inline-flex; align-items: center; justify-content: center; gap: 9px; border: 1px solid transparent; border-radius: 999px; background: #1d4ed8; color: #ffffff; text-decoration: none; font-weight: 800; text-align: center; transition: background .2s ease, transform .2s ease; }
        .plan-card.is-featured .plan-card__action { background: #ffffff; color: #173f87; }
        .plan-card__action:hover { background: #1e40af; color: #ffffff; transform: translateY(-2px); }
        .plan-card.is-featured .plan-card__action:hover { background: #dbeafe; color: #173f87; }
        .plan-card__action:focus-visible { outline: 3px solid #93c5fd; outline-offset: 3px; }
        /* Keep the main section transparent and preserve blue card contrast in dark mode. */
        html[data-web-theme='dark'] .plans-head h1 { color: #fff; }
        html[data-web-theme='dark'] .plans-head p { color: #cbd5e1; }
        html[data-web-theme='dark'] body.web-shell .web-main .plan-card { background: #0e1113 !important; border-color: #26313a !important; }
        html[data-web-theme='dark'] body.web-shell .web-main .plan-card.is-featured { background: #102b59 !important; border-color: #3b82f6 !important; }
        html[data-web-theme='dark'] body.web-shell .web-main .plan-card.is-light { background: #142033 !important; border-color: #304666 !important; }
        html[data-web-theme='dark'] body.web-shell .web-main .plan-card.is-featured .plan-card__price { color: #bfdbfe !important; }
        html[data-web-theme='dark'] body.web-shell .web-main .plan-card__featured,
        html[data-web-theme='dark'] body.web-shell .web-main .plan-card.is-featured .plan-card__action { color: #173f87 !important; }
        html[data-web-theme='dark'] .plan-card__courses { border-top-color: #40536d; }
        html[data-web-theme='dark'] .plans-empty { background: #0e1113; border-color: #26313a; color: #94a3b8; }
        html[data-web-theme='dark'] .plans-empty h2 { color: #fff; }
        html[data-web-theme='dark'] .plan-card__copy,
        html[data-web-theme='dark'] .plan-card__period,
        html[data-web-theme='dark'] .plan-card__courses { color: #e2e8f0; }
        html[data-web-theme='dark'] .plans-current { background: #0e1113; border-color: #2563eb; color: #dbeafe; }
        @media (max-width: 900px) { .plans-grid { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 620px) { .plans-shell { width: min(100% - 16px, 520px); margin-top: 24px; padding: 0 0 28px; } .plans-grid { grid-template-columns: 1fr; gap: 18px; } .plan-card { padding: 23px; } .plan-card.is-featured { padding: 21px; } .plan-card__action { width: 100%; } }
        @media (prefers-reduced-motion: reduce) { .plan-card, .plan-card__action { transition: none; } .plan-card:hover, .plan-card__action:hover { transform: none; } }
    </style>

    <section class="plans-shell">
        {{-- Keep the page title inside normal document flow, separate from the fixed website header. --}}
        <div class="plans-head">
            <h1>{{ __('Subscription Plans') }}</h1>
            <p>{{ __('Choose a plan to access its included courses. Courses purchased separately remain available permanently.') }}</p>
        </div>

        @if ($subscriptions->isNotEmpty())
            <div class="plans-current">
                <strong>{{ __('My active subscription') }}</strong>
                @foreach ($subscriptions as $subscription)
                    {{-- Translate known default plan names while preserving custom admin plan names. --}}
                    <div>{{ __($subscription->plan?->name) }} — {{ __('expires') }} {{ optional($subscription->expires_at)->format('d M Y H:i') ?: __('Never') }}</div>
                @endforeach
            </div>
        @endif

        <div class="plans-grid">
            @forelse ($plans as $plan)
                {{-- Vary only the card presentation; billing and plan data remain unchanged. --}}
                <article class="plan-card {{ $plan->is_featured ? 'is-featured' : ($loop->iteration % 3 === 0 ? 'is-light' : '') }}">
                    @if ($plan->is_featured)<span class="plan-card__featured">{{ __('Popular') }}</span>@endif
                    {{-- Translate known default plan content while preserving custom admin content. --}}
                    <h2>{{ __($plan->name) }}</h2>
                    <p class="plan-card__copy">{{ $plan->description ? __($plan->description) : __('Access the courses included in this plan.') }}</p>
                    {{-- Keep the price and duration together without changing plan values. --}}
                    <div class="plan-card__pricing">
                        <div class="plan-card__price">{{ $plan->currency }} {{ number_format((float) $plan->price, 2) }}</div>
                        <div class="plan-card__period">{{ $plan->duration_days }} {{ __('days access') }}</div>
                    </div>
                    <ul class="plan-card__courses">
                        @forelse ($plan->courses->take(6) as $course)
                            <li>{{ $course->title }}</li>
                        @empty
                            <li class="is-pending">{{ __('Courses will be added soon.') }}</li>
                        @endforelse
                        @if ($plan->courses->count() > 6)<li>+{{ $plan->courses->count() - 6 }} {{ __('more courses') }}</li>@endif
                    </ul>
                    @auth
                        <a class="plan-card__action" href="{{ route('subscriptions.checkout', $plan) }}">{{ __('Choose Plan') }}</a>
                    @else
                        <a class="plan-card__action" href="{{ route('web.login', ['redirect' => route('subscriptions.checkout', $plan)]) }}">{{ __('Login to Subscribe') }}</a>
                    @endauth
                </article>
            @empty
                {{-- Show a clear state when the current database has no active subscription plans. --}}
                <div class="plans-empty">
                    <div>
                        <i class="fas fa-layer-group" aria-hidden="true"></i>
                        <h2>{{ __('No subscription plans are available yet.') }}</h2>
                        <p>{{ __('Please check again after plans are published.') }}</p>
                    </div>
                </div>
            @endforelse
        </div>
    </section>
@endsection
