@extends('web.layouts.app')

@section('title', __('Subscription Checkout'))

@php
    $khqrCardId = 'subscription-khqr-' . $payment->id;
@endphp

@section('content')
    <style>
        .subscription-checkout { width: min(920px, calc(100% - 32px)); margin: 0 auto; padding: 12px 0 52px; }
        .subscription-checkout h1 { margin: 0 0 24px; text-align: center; color: #0f172a; }
        .subscription-checkout__grid { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 24px; align-items: start; }
        .subscription-summary { padding: 26px; background: #fff; border: 1px solid #dbe6f1; }
        .subscription-summary h2 { margin: 0; color: #0f172a; }
        .subscription-summary p { color: #64748b; line-height: 1.7; }
        .subscription-summary__price { margin: 20px 0; color: #1d4ed8; font-size: 2rem; font-weight: 900; }
        .subscription-pricing { margin: 20px 0; padding: 14px; display: grid; gap: 8px; background: #f8fafc; border: 1px solid #e2e8f0; color: #334155; }
        .subscription-pricing__row { display: flex; justify-content: space-between; gap: 16px; }
        .subscription-pricing__row.is-total { padding-top: 8px; border-top: 1px solid #cbd5e1; color: #0f172a; font-weight: 900; }
        .subscription-coupon { display: flex; gap: 8px; margin-top: 18px; }
        .subscription-coupon input { min-width: 0; flex: 1; height: 42px; padding: 0 12px; border: 1px solid #cbd5e1; background: #fff; color: #0f172a; text-transform: uppercase; }
        .subscription-coupon button { min-height: 42px; padding: 0 18px; border: 0; background: #1d4ed8; color: #fff; font-weight: 800; cursor: pointer; }
        .subscription-coupon-message { margin: 8px 0 0 !important; font-size: 13px; }
        .subscription-coupon-message.is-error { color: #dc2626 !important; }
        .subscription-coupon-message.is-success { color: #15803d !important; }
        .subscription-summary ul { padding-left: 20px; color: #334155; line-height: 1.8; }
        .subscription-qr { padding: 18px; background: #0e1113; }
        .subscription-status { margin-top: 14px; color: #fff; text-align: center; font-size: 13px; }
        .subscription-success[hidden] { display: none; }
        .subscription-success { position: fixed; inset: 0; z-index: 1500; display: grid; place-items: center; padding: 20px; background: rgba(0,0,0,.72); opacity: 0; transition: opacity .3s ease; }
        .subscription-success.is-open { opacity: 1; }
        .subscription-success__card { width: min(430px, calc(100vw - 36px)); padding: 34px 28px; border-radius: 20px; background: #fff; text-align: center; }
        .subscription-success__card img { width: 180px; height: 115px; object-fit: contain; }
        .subscription-success__card h2 { margin: 8px 0; color: #202020; }
        .subscription-success__card p { color: #697386; line-height: 1.6; }
        html[data-web-theme='dark'] .subscription-checkout h1,
        html[data-web-theme='dark'] .subscription-summary h2 { color: #fff; }
        html[data-web-theme='dark'] .subscription-summary { background: #0e1113; border-color: #26313a; }
        html[data-web-theme='dark'] .subscription-pricing { background: #000; border-color: #26313a; color: #cbd5e1; }
        html[data-web-theme='dark'] .subscription-pricing__row.is-total { border-color: #26313a; color: #fff; }
        html[data-web-theme='dark'] .subscription-coupon input { background: #000; border-color: #26313a; color: #fff; }
        html[data-web-theme='dark'] .subscription-summary p,
        html[data-web-theme='dark'] .subscription-summary ul { color: #cbd5e1; }
        @media (max-width: 760px) { .subscription-checkout__grid { grid-template-columns: 1fr; } }
    </style>

    <section class="subscription-checkout">
        <h1>{{ __('Subscription Checkout') }}</h1>
        <div class="subscription-checkout__grid">
            <article class="subscription-summary">
                {{-- Translate known default plan content while preserving custom admin content. --}}
                <h2>{{ __($plan->name) }}</h2>
                <p>{{ __($plan->description) }}</p>
                <p>{{ $plan->duration_days }} {{ __('days access') }}</p>

                <form class="subscription-coupon" method="GET" action="{{ route('subscriptions.checkout', $plan) }}">
                    <input name="coupon" value="{{ $couponCode }}" placeholder="{{ __('Coupon code') }}" maxlength="80">
                    <button type="submit">{{ __('Apply') }}</button>
                </form>

                @if($couponError)
                    <p class="subscription-coupon-message is-error">{{ $couponError }}</p>
                @elseif($pricing['coupon'])
                    <p class="subscription-coupon-message is-success">{{ __('Coupon applied') }}: {{ $pricing['coupon']->code }}</p>
                @endif

                <div class="subscription-pricing">
                    <div class="subscription-pricing__row"><span>{{ __('Subtotal') }}</span><strong>{{ $plan->currency }} {{ number_format($pricing['subtotal'], 2) }}</strong></div>
                    <div class="subscription-pricing__row"><span>{{ __('Discount') }}</span><strong>- {{ $plan->currency }} {{ number_format($pricing['discount'], 2) }}</strong></div>
                    <div class="subscription-pricing__row is-total"><span>{{ __('Total') }}</span><strong>{{ $plan->currency }} {{ number_format($pricing['total'], 2) }}</strong></div>
                </div>
                <ul>
                    @foreach ($plan->courses as $course)<li>{{ $course->title }}</li>@endforeach
                </ul>
            </article>

            <aside class="subscription-qr">
                {{-- Reuse the verified KHQR card and status polling used by course payments. --}}
                @include('components.khqr-card', [
                    'cardId' => $khqrCardId,
                    'merchantName' => config('bakong.merchant_name') ?: 'TechCourse',
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'khqrString' => $payment->khqr_string,
                    'qrImageUrl' => $payment->qr_image_url,
                    'expiredAt' => $payment->expired_at,
                    'status' => $payment->status,
                    'showStatusMeta' => true,
                    'showCenterBadge' => true,
                ])
                <div class="subscription-status">{{ __('Payment status') }}: <span data-subscription-status>{{ $payment->status }}</span></div>
            </aside>
        </div>
    </section>

    <div class="subscription-success" data-subscription-success hidden>
        <div class="subscription-success__card">
            <img src="{{ asset('ABA_Images/icon_payment.png') }}" alt="">
            <h2>{{ __('Payment succeeded!') }}</h2>
            <p>{{ __('Your subscription is now active and its courses are unlocked.') }}</p>
        </div>
    </div>

    <script>
        (() => {
            const statusUrl = @json(route('payments.bakong.status', $payment));
            const cardId = @json($khqrCardId);
            const statusText = document.querySelector('[data-subscription-status]');
            const successModal = document.querySelector('[data-subscription-success]');
            let attempt = 0;
            let stopped = false;

            // Poll with backoff so subscription checkout respects the Bakong quota.
            const checkStatus = async () => {
                if (stopped) return;

                try {
                    const response = await fetch(statusUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                    const result = await response.json();
                    const status = result?.data?.status;
                    if (statusText && status) statusText.textContent = status;

                    if (status === 'success') {
                        stopped = true;
                        window.TechCourseKhqrCards?.setStatus(cardId, 'success');
                        successModal.hidden = false;
                        requestAnimationFrame(() => successModal.classList.add('is-open'));
                        window.setTimeout(() => window.location.href = @json(route('subscriptions.index')), 5000);
                        return;
                    }

                    if (['expired', 'failed'].includes(status)) {
                        stopped = true;
                        window.TechCourseKhqrCards?.setStatus(cardId, status);
                        return;
                    }
                } catch (error) {
                    console.error('Subscription payment status check failed.', error);
                }

                const delay = Math.min(5000 * (2 ** Math.min(attempt++, 4)), 60000);
                window.setTimeout(checkStatus, delay);
            };

            checkStatus();
        })();
    </script>
@endsection
