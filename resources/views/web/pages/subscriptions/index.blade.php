@extends('web.layouts.app')

@section('title', __('Subscription Plans'))

@section('content')
    <style>
        .plans-shell { width: min(1180px, calc(100% - 32px)); margin: 0 auto; padding: 12px 0 52px; }
        .plans-head { max-width: 720px; margin: 0 auto 30px; text-align: center; }
        .plans-head h1 { margin: 0; color: #0f172a; font-size: clamp(1.8rem, 4vw, 2.7rem); }
        .plans-head p { margin: 10px 0 0; color: #64748b; line-height: 1.7; }
        .plans-current { margin: 0 0 24px; padding: 18px; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; }
        .plans-current strong { display: block; margin-bottom: 5px; }
        .plans-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
        .plans-empty { grid-column: 1 / -1; min-height: 260px; padding: 32px; display: grid; place-items: center; background: #f8fafc; border: 1px dashed #cbd5e1; color: #64748b; text-align: center; }
        .plans-empty i { display: block; margin-bottom: 14px; color: #2563eb; font-size: 2rem; }
        .plans-empty h2 { margin: 0 0 8px; color: #0f172a; font-size: 1.25rem; }
        .plans-empty p { margin: 0; }
        .plan-card { position: relative; display: flex; flex-direction: column; min-height: 420px; padding: 26px; background: #fff; border: 1px solid #dbe6f1; box-shadow: 0 14px 30px rgba(15, 23, 42, .06); }
        .plan-card.is-featured { border: 2px solid #2563eb; }
        .plan-card__featured { position: absolute; top: 0; right: 0; padding: 6px 10px; background: #2563eb; color: #fff; font-size: 11px; font-weight: 800; }
        .plan-card h2 { margin: 0; color: #0f172a; font-size: 1.35rem; }
        .plan-card__copy { min-height: 52px; margin: 10px 0 0; color: #64748b; line-height: 1.65; }
        .plan-card__price { margin: 20px 0 4px; color: #0f172a; font-size: 2rem; font-weight: 900; }
        .plan-card__period { color: #64748b; font-size: 13px; }
        .plan-card__courses { margin: 22px 0; padding: 0; list-style: none; display: grid; gap: 9px; color: #334155; font-size: 13px; }
        .plan-card__courses li::before { content: '✓'; margin-right: 8px; color: #16a34a; font-weight: 900; }
        .plan-card__action { margin-top: auto; min-height: 44px; display: inline-flex; align-items: center; justify-content: center; background: #1d4ed8; color: #fff; text-decoration: none; font-weight: 800; }
        html[data-web-theme='dark'] .plans-head h1,
        html[data-web-theme='dark'] .plan-card h2,
        html[data-web-theme='dark'] .plan-card__price { color: #fff; }
        html[data-web-theme='dark'] .plan-card { background: #0e1113; border-color: #26313a; box-shadow: none; }
        html[data-web-theme='dark'] .plans-empty { background: #0e1113; border-color: #26313a; color: #94a3b8; }
        html[data-web-theme='dark'] .plans-empty h2 { color: #fff; }
        html[data-web-theme='dark'] .plan-card__copy,
        html[data-web-theme='dark'] .plan-card__period,
        html[data-web-theme='dark'] .plan-card__courses { color: #cbd5e1; }
        html[data-web-theme='dark'] .plans-current { background: #0e1113; border-color: #2563eb; color: #dbeafe; }
        @media (max-width: 900px) { .plans-grid { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 620px) { .plans-grid { grid-template-columns: 1fr; } }
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
                <article class="plan-card {{ $plan->is_featured ? 'is-featured' : '' }}">
                    @if ($plan->is_featured)<span class="plan-card__featured">{{ __('Popular') }}</span>@endif
                    {{-- Translate known default plan content while preserving custom admin content. --}}
                    <h2>{{ __($plan->name) }}</h2>
                    <p class="plan-card__copy">{{ $plan->description ? __($plan->description) : __('Access the courses included in this plan.') }}</p>
                    <div class="plan-card__price">{{ $plan->currency }} {{ number_format((float) $plan->price, 2) }}</div>
                    <div class="plan-card__period">{{ $plan->duration_days }} {{ __('days access') }}</div>
                    <ul class="plan-card__courses">
                        @forelse ($plan->courses->take(6) as $course)
                            <li>{{ $course->title }}</li>
                        @empty
                            <li>{{ __('Courses will be added soon.') }}</li>
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
