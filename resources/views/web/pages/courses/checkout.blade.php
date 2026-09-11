@extends('web.layouts.app')

@section('title', __('Course Checkout'))

@php
    $courseDescription = $course->short_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $course->description), 180);
    $khqrMerchantName = config('bakong.merchant_name') ?: 'TechCourse';
    $khqrCardId = 'course-khqr-card-' . $payment->id;
    $firstLesson = $course->lessons->first();
    $successRedirectUrl = $firstLesson
        ? route('learning.show', [$course->slug ?: $course->id, $firstLesson->slug ?: $firstLesson->id])
        : route('courses.show', $course->slug ?: $course->id);
    // Keep Bakong as the active checkout QR while ABA work is paused for later.
    $paymentMethods = [
        ['name' => 'Bakong KHQR', 'copy' => __('Scan to pay with Bakong or any banking app supporting KHQR'), 'image' => asset('logo/bakong_logo.png'), 'actionable' => true],
        [
            'name' => __('Card'),
            'copy' => __('Credit/Debit Card'),
            'image' => asset('ABA_Images/card_icon.png'),
            'logos' => [
                ['type' => 'image', 'src' => asset('ABA_Images/VISA-Copy.png'), 'alt' => 'Visa'],
                ['type' => 'mastercard'],
                ['type' => 'image', 'src' => asset('ABA_Images/UPI.png'), 'alt' => 'UPI'],
                ['type' => 'image', 'src' => asset('ABA_Images/JCB.png'), 'alt' => 'JCB'],
            ],
        ],
        ['name' => 'Alipay', 'copy' => __('Scan to pay with Alipay'), 'image' => asset('ABA_Images/Alipay.png')],
        ['name' => 'WeChat', 'copy' => __('Scan to pay with WeChat'), 'image' => asset('ABA_Images/Wechat.png')],
    ];
@endphp

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@600;700;800;900&display=swap');

        .checkout-shell {
            width: min(1120px, calc(100% - 36px));
            margin: 0 auto;
            padding-bottom: 48px;
        }

        .checkout-page-title {
            margin: 0 0 28px;
            color: #000000;
            text-align: center;
            font-family: 'Gagalin', var(--font-lato);
            font-size: clamp(1.18rem, 1.8vw, 1.45rem);
            line-height: 1.25;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        .checkout-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.02fr) minmax(320px, 0.64fr);
            gap: 22px;
            align-items: start;
        }

        .checkout-course-card {
            overflow: hidden;
            display: grid;
            grid-template-columns: 350px minmax(0, 1fr);
            background: #ffffff;
            border: 1px solid #e5edf5;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
        }

        .checkout-course-media {
            position: relative;
            min-height: 270px;
            background: linear-gradient(135deg, #19496d, #0d3556);
            overflow: hidden;
        }

        .checkout-course-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .checkout-course-media::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(8, 19, 36, 0.06), rgba(8, 19, 36, 0.22));
            pointer-events: none;
        }

        .checkout-course-fallback {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            color: rgba(255, 255, 255, 0.92);
            font-family: var(--font-lato);
            font-size: clamp(2.8rem, 7vw, 5rem);
            letter-spacing: 0.18em;
        }

        .checkout-course-body {
            padding: 24px 26px;
            color: #0f172a;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .checkout-course-name {
            margin: 0;
            font-family: var(--font-lato);
            font-size: 1.05rem;
            line-height: 1.32;
            font-weight: 800;
        }

        .checkout-course-copy {
            margin: 12px 0 0;
            color: #475569;
            font-size: 12px;
            line-height: 1.72;
        }

        .checkout-course-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 16px;
        }

        .checkout-pill {
            min-height: 28px;
            padding: 0 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border-radius: 999px;
            background: #ffffff;
            color: #0f2345;
            border: 1px solid #d9e3ef;
            font-size: 10px;
            font-weight: 700;
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.05);
        }

        .checkout-side {
            display: grid;
            gap: 16px;
        }

        .checkout-payments {
            padding: 16px;
            display: grid;
            gap: 12px;
            align-content: start;
            border-radius: 0;
            background: #ffffff;
            border: 1px solid #e5edf5;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .checkout-payments::before {
            color: #0f172a;
            font-size: 0.92rem;
            font-weight: 850;
            font-family: var(--font-lato);
            margin-bottom: 2px;
        }

        .checkout-payments::before {
            content: "{{ __('Select Payment Method') }}";
        }

        .checkout-pay-card {
            width: 100%;
            box-sizing: border-box;
            display: grid;
            grid-template-columns: 52px minmax(0, 1fr) auto;
            gap: 12px;
            align-items: center;
            padding: 10px 14px;
            border-radius: 18px;
            border: 1px solid #ebf1f7;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
        }

        .checkout-pay-card--button {
            width: 100%;
            background: #ffffff;
            cursor: pointer;
            text-align: left;
        }

        .checkout-pay-card--button:hover {
            border-color: #dbe6f1;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.07);
        }

        .checkout-pay-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #ffffff;
        }

        .checkout-pay-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .checkout-pay-name {
            color: #10203c;
            font-size: 0.82rem;
            font-weight: 800;
            font-family: var(--font-lato);
            line-height: 1.25;
        }

        .checkout-pay-copy {
            color: #6c7d93;
            font-size: 0.67rem;
            line-height: 1.45;
            margin-top: 1px;
            word-break: break-word;
        }

        .checkout-pay-status {
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid #dbe6f1;
            background: #f8fbff;
            color: #28405f;
            font-size: 0.74rem;
            line-height: 1.6;
        }

        .checkout-pay-logos {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
            flex-wrap: wrap;
        }

        .checkout-pay-logo {
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .checkout-pay-logo img {
            height: 18px;
            width: auto;
            display: block;
        }

        .checkout-pay-logo--mastercard {
            width: 34px;
            height: 18px;
            border-radius: 3px;
            background: #000000;
            gap: 0;
            padding: 0 4px;
        }

        .checkout-pay-logo--mastercard span {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            display: block;
        }

        .checkout-pay-logo--mastercard span:first-child {
            background: #eb001b;
            margin-right: -4px;
        }

        .checkout-pay-logo--mastercard span:last-child {
            background: #f79e1b;
        }

        .checkout-pay-arrow {
            width: 30px;
            height: 30px;
            border-radius: 10px;
            background: #f3f7fc;
            border: 1px solid #e1eaf3;
            color: #66768d;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.76rem;
            justify-self: end;
            margin-right: 2px;
            flex-shrink: 0;
        }

        .khqr-modal[hidden] {
            display: none;
        }

        .khqr-modal {
            position: fixed;
            inset: 0;
            z-index: 1400;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            transition: opacity 1.5s ease;
        }

        .khqr-modal.is-open {
            opacity: 1;
        }

        .khqr-modal__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.72);
        }

        .khqr-modal__dialog {
            position: relative;
            width: min(360px, calc(100vw - 28px));
            background: transparent;
            border-radius: 0;
            border: 0;
            box-shadow: none;
            padding: 0;
            overflow: visible;
            transform: translateY(20px);
            opacity: 0;
            transition: transform 1.5s cubic-bezier(0.2, 0.7, 0.2, 1), opacity 1.5s ease;
        }

        .khqr-modal .khqr-card__timer {
            color: #FFFFFF;
        }

        .course-payment-success[hidden] {
            display: none;
        }

        .course-payment-success {
            position: fixed;
            inset: 0;
            z-index: 1500;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(0, 0, 0, 0.72);
            opacity: 0;
            transition: opacity 1.5s ease;
        }

        .course-payment-success.is-open {
            opacity: 1;
        }

        .course-payment-success__card {
            width: min(360px, calc(100vw - 36px));
            padding: 34px 26px 28px;
            border-radius: 20px;
            background: #FFFFFF;
            color: #1f2937;
            text-align: center;
            box-shadow: 0 20px 55px rgba(0, 0, 0, 0.22);
            transform: translateY(14px) scale(0.98);
            transition: transform 1.5s ease;
        }

        .course-payment-success.is-open .course-payment-success__card {
            transform: translateY(0) scale(1);
        }

        .course-payment-success__icon {
            /* Enlarge the success artwork while preserving its transparent spacing. */
            width: 180px;
            height: 115px;
            margin: 0 auto 18px;
            display: block;
            object-fit: contain;
        }

        .course-payment-success__title {
            margin: 0;
            color: #202020;
            font-size: 25px;
            font-weight: 800;
            line-height: 1.2;
        }

        .course-payment-success__text {
            margin: 12px auto 0;
            max-width: 290px;
            color: #697386;
            font-size: 14px;
            line-height: 1.55;
        }

        .khqr-modal.is-open .khqr-modal__dialog {
            transform: translateY(0);
            opacity: 1;
        }

        .khqr-modal__close {
            display: none;
        }

        /* Keep the KHQR card aligned with the visual sample the user provided. */
        .khqr-modal__card {
            width: 100%;
            margin: 0;
            border-radius: 10px;
            overflow: hidden;
            background: #e1232d;
            border: 0;
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.2);
            font-family: 'Nunito Sans', sans-serif;
        }

        .khqr-official-card__header {
            position: relative;
            min-height: 80px;
            background: #e1232d;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 20px 14px;
        }

        .khqr-official-card__header::after {
            content: "";
            position: absolute;
            right: 0;
            bottom: -1px;
            width: 0;
            height: 0;
            border-top: 30px solid transparent;
            border-left: 30px solid #ffffff;
        }

        .khqr-official-card__logo {
            width: 98px;
            max-width: 100%;
            display: block;
        }

        .khqr-official-card__body {
            background: #ffffff;
            padding: 14px 18px 14px;
        }

        .khqr-official-card__merchant {
            margin: 0;
            color: #383c42;
            font-size: 18px;
            font-weight: 800;
            line-height: 1.25;
        }

        .khqr-official-card__currency {
            margin-top: 12px;
            color: #383c42;
            font-size: 17px;
            font-weight: 800;
            line-height: 1.1;
        }

        .khqr-official-card__qr-wrap {
            width: 100%;
            margin: 18px auto 0;
        }

        .khqr-official-card__qr {
            width: 100%;
            max-width: 304px;
            max-height: 304px;
            display: block;
            margin: 0 auto;
            object-fit: contain;
        }

        .khqr-official-card__footer {
            background: #e1232d;
            padding: 8px 8px 8px;
        }

        .khqr-modal__empty {
            padding: 24px 18px;
            text-align: center;
            color: #64748b;
            font-size: 12px;
            line-height: 1.7;
            min-height: 240px;
            display: grid;
            place-items: center;
        }

        .khqr-modal__caption {
            width: 100%;
            margin: 0;
            padding: 14px 18px 4px;
            text-align: center;
            color: #8a94a6;
            font-size: 11px;
            line-height: 1.65;
        }

        .khqr-modal__download {
            width: 100%;
            min-height: 54px;
            border: 0;
            border-radius: 0;
            background: #ffffff;
            color: #202020;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
        }

        .khqr-reference {
            margin: 0;
            color: #64748b;
            font-size: 0.72rem;
            line-height: 1.7;
            text-align: center;
            word-break: break-word;
        }

        .checkout-khqr-notice {
            width: min(320px, calc(100vw - 40px));
            margin: 10px auto 0;
            color: #FFFFFF;
            text-align: center;
            font-size: 11px;
            line-height: 1.5;
        }

        @media (max-width: 980px) {
            .checkout-layout {
                grid-template-columns: 1fr;
            }

            .checkout-course-card {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .checkout-shell {
                width: min(100%, calc(100% - 20px));
            }

            .checkout-page-title {
                margin-bottom: 24px;
            }

            .checkout-course-body {
                padding: 18px 16px 18px;
            }

            .khqr-modal__dialog {
                width: min(330px, calc(100vw - 18px));
            }

            .khqr-official-card__header {
                min-height: 74px;
                padding: 18px 18px 12px;
            }

            .khqr-official-card__header::after {
                border-top-width: 26px;
                border-left-width: 26px;
            }

            .khqr-official-card__logo {
                width: 92px;
            }

            .khqr-official-card__body {
                padding: 14px 16px 14px;
            }

            .khqr-official-card__merchant {
                font-size: 17px;
            }

            .khqr-official-card__currency {
                font-size: 16px;
            }

            .khqr-official-card__qr {
                max-width: 286px;
                max-height: 286px;
            }

            .khqr-modal__download {
                min-height: 50px;
                font-size: 15px;
            }
        }
    </style>

    <section class="checkout-shell">
        <h1 class="checkout-page-title">{{ $course->title }}</h1>

        {{-- Show a clear warning popup before any test payment action starts. --}}
        <div class="checkout-layout">
            <article class="checkout-course-card">
                <div class="checkout-course-media">
                    @if ($course->thumbnail_url)
                        <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}">
                    @else
                        <div class="checkout-course-fallback">UI UX</div>
                    @endif
                </div>

                <div class="checkout-course-body">
                    <h2 class="checkout-course-name">{{ $course->title }}</h2>
                    <p class="checkout-course-copy">{{ $courseDescription }}</p>

                    <div class="checkout-course-meta">
                        <span class="checkout-pill">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</span>
                        <span class="checkout-pill">{{ $course->lessons->count() }} {{ __('Lessons') }}</span>
                        <span class="checkout-pill">{{ __('Order') }}: {{ $order->order_no }}</span>
                    </div>
                </div>
            </article>

            <div class="checkout-side">
                <div class="checkout-payments">
                    @foreach ($paymentMethods as $method)
                        @if (!empty($method['actionable']))
                            <button type="button" class="checkout-pay-card checkout-pay-card--button" data-khqr-open>
                                <span class="checkout-pay-icon">
                                    <img src="{{ $method['image'] }}" alt="{{ $method['name'] }}">
                                </span>
                                <span>
                                    <div class="checkout-pay-name">{{ $method['name'] }}</div>
                                    <div class="checkout-pay-copy">{{ $method['copy'] }}</div>
                                </span>
                                <span class="checkout-pay-arrow">
                                    <i class="fa-solid fa-angle-right"></i>
                                </span>
                            </button>
                        @else
                            <div class="checkout-pay-card">
                                <span class="checkout-pay-icon">
                                    <img src="{{ $method['image'] }}" alt="{{ $method['name'] }}">
                                </span>
                                <span>
                                    <div class="checkout-pay-name">{{ $method['name'] }}</div>
                                    <div class="checkout-pay-copy">{{ $method['copy'] }}</div>
                                    @if (!empty($method['logos']))
                                        <div class="checkout-pay-logos">
                                            @foreach ($method['logos'] as $logo)
                                                @if (($logo['type'] ?? '') === 'mastercard')
                                                    <span class="checkout-pay-logo checkout-pay-logo--mastercard" aria-label="Mastercard">
                                                        <span></span>
                                                        <span></span>
                                                    </span>
                                                @else
                                                    <span class="checkout-pay-logo">
                                                        <img src="{{ $logo['src'] }}" alt="{{ $logo['alt'] }}">
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </span>
                                <span class="checkout-pay-arrow">
                                    <i class="fa-solid fa-angle-right"></i>
                                </span>
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="checkout-pay-status" data-payment-status-box>
                    {{ __('Payment status') }}: <span data-payment-status-text>{{ ucfirst($payment->status) }}</span>
                </div>

                @if (!empty($khqrError))
                    <div class="checkout-pay-copy">{{ $khqrError }}</div>
                @endif
            </div>
        </div>
    </section>

    <div class="khqr-modal" data-khqr-modal hidden>
        <div class="khqr-modal__backdrop" data-khqr-close></div>

        <div class="khqr-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="khqr-modal-title">
            <button type="button" class="khqr-modal__close" data-khqr-close aria-label="{{ __('Close') }}">
                <i class="fa-solid fa-xmark"></i>
            </button>

            @include('components.khqr-card', [
                'cardId' => $khqrCardId,
                'merchantName' => $khqrMerchantName,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'khqrString' => $payment->khqr_string,
                'qrImageUrl' => $khqrPreviewUrl,
                'expiredAt' => $payment->expired_at,
                'status' => $payment->status,
                'showStatusMeta' => true,
                'showCenterBadge' => true,
                'statusMessage' => $khqrError ?: '',
                'emptyMessage' => __('Please check your Bakong account config and generate the KHQR again.'),
            ])

            <p class="checkout-khqr-notice">
                {{ __('Note: This website is for testing only. If you make a payment, I will not be responsible for any loss.') }}
            </p>

            {{--
                Hide the payment deeplink button in the course checkout KHQR modal for now.
            @if (!empty($khqrDeepLink))
                <div class="khqr-actions">
                    @if (!empty($khqrDeepLink))
                        <a href="{{ $khqrDeepLink }}" target="_blank" rel="noopener noreferrer" class="khqr-actions__link">
                            {{ ($checkoutQrProvider ?? 'bakong') === 'aba' ? __('Open ABA Deeplink') : __('Open Bakong Deeplink') }}
                        </a>
                    @endif
                </div>
            @endif
            --}}
        </div>
    </div>

    <div class="course-payment-success" data-course-payment-success hidden>
        <div class="course-payment-success__card" role="dialog" aria-modal="true" aria-labelledby="course-payment-success-title">
            {{-- Show the requested payment success icon after course payment confirmation. --}}
            <img src="{{ asset('ABA_Images/icon_payment.png') }}" alt="" class="course-payment-success__icon" aria-hidden="true">
            <h2 class="course-payment-success__title" id="course-payment-success-title">{{ __('Payment succeeded!') }}</h2>
            <p class="course-payment-success__text">{{ __('Your transaction was completed successfully. Your course is now unlocked.') }}</p>
        </div>
    </div>

    <script>
        (() => {
            const modal = document.querySelector('[data-khqr-modal]');
            const successModal = document.querySelector('[data-course-payment-success]');
            const openButton = document.querySelector('[data-khqr-open]');
            const closeButtons = document.querySelectorAll('[data-khqr-close]');
            const jsKhqrEmpty = document.querySelector('[data-js-khqr-empty]');
            const khqrCardId = @json($khqrCardId);
            const paymentId = @json($payment->id);
            const paymentStatusUrl = @json(route('payments.bakong.status', $payment));
            const successRedirectUrl = @json($successRedirectUrl);
            const statusText = document.querySelector('[data-payment-status-text]');
            let pollTimer = null;
            let statusLocked = false;
            let successTimer = null;
            // Reuse the shared layout helper so course checkout popup fully freezes page scroll.
            const lockPageScroll = () => window.TechCourseScrollLock?.lock?.() ?? (document.body.style.overflow = 'hidden');
            const unlockPageScroll = () => window.TechCourseScrollLock?.unlock?.() ?? (document.body.style.overflow = '');

            const showCourseSuccess = () => {
                if (!successModal) {
                    window.location.href = successRedirectUrl;
                    return;
                }

                window.clearTimeout(successTimer);
                successModal.hidden = false;
                lockPageScroll();
                requestAnimationFrame(() => successModal.classList.add('is-open'));

                // Keep the success message visible before opening the unlocked course.
                successTimer = window.setTimeout(() => {
                    successModal.classList.remove('is-open');
                    window.setTimeout(() => {
                        successModal.hidden = true;
                        unlockPageScroll();
                        window.location.href = successRedirectUrl;
                    }, 1500);
                }, 6000);
            };

            // Track when a learner reaches the course checkout flow.
            window.trackEvent('begin_checkout', {
                currency: @json($payment->currency),
                value: {{ (float) $payment->amount }},
                items: [{
                    item_id: @json('course_' . $course->id),
                    item_name: @json($course->title),
                    item_category: 'course',
                    price: {{ (float) $payment->amount }},
                    quantity: 1,
                }],
            });

            if (!modal || !openButton) {
                return;
            }

            const openModal = () => {
                modal.hidden = false;
                lockPageScroll();

                requestAnimationFrame(() => {
                    modal.classList.add('is-open');
                });

                startStatusPolling();
            };

            const closeModal = (immediate = false) => {
                modal.classList.remove('is-open');

                if (immediate) {
                    modal.hidden = true;
                    unlockPageScroll();
                    return;
                }

                window.setTimeout(() => {
                    modal.hidden = true;
                    unlockPageScroll();
                }, 280);
            };

            openButton.addEventListener('click', openModal);
            closeButtons.forEach((button) => button.addEventListener('click', closeModal));

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.hidden) {
                    closeModal();
                }
            });

            document.addEventListener('khqr:expired', (event) => {
                if (event.detail?.cardId !== khqrCardId || statusLocked) {
                    return;
                }

                // Let the backend perform one final Bakong check before confirming expiry.
                checkPaymentStatus();
            });

            // Poll backend-confirmed Bakong payment status so success is shown only after real API verification.
            function startStatusPolling() {
                if (pollTimer || !paymentId || statusLocked) {
                    return;
                }

                checkPaymentStatus();
                pollTimer = window.setInterval(checkPaymentStatus, 3000);
            }

            function stopStatusPolling() {
                if (!pollTimer) {
                    return;
                }

                window.clearInterval(pollTimer);
                pollTimer = null;
            }

            async function checkPaymentStatus() {
                if (statusLocked) {
                    return;
                }

                try {
                    const response = await window.fetch(paymentStatusUrl, {
                        headers: {
                            Accept: 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    const result = await response.json();
                    const status = result?.data?.status;

                    if (statusText && status) {
                        statusText.textContent = status;
                    }

                    if (status === 'success') {
                        statusLocked = true;
                        stopStatusPolling();
                        window.TechCourseKhqrCards?.setStatus(khqrCardId, 'success', 'Payment confirmed successfully.');
                        closeModal(true);
                        showCourseSuccess();
                        return;
                    }

                    if (status === 'expired') {
                        statusLocked = true;
                        stopStatusPolling();
                        window.TechCourseKhqrCards?.setStatus(khqrCardId, 'expired', 'QR expired. Please create new payment.');
                        window.TechCourseKhqrCards?.showToast('QR expired. Please create new payment.', 'error');
                        return;
                    }

                    if (status === 'failed') {
                        statusLocked = true;
                        stopStatusPolling();
                        window.TechCourseKhqrCards?.setStatus(khqrCardId, 'failed', 'Payment verification failed. Please contact support or try again.');
                        window.TechCourseKhqrCards?.showToast('Payment verification failed.', 'error');
                    }
                } catch (error) {
                    console.error('Bakong payment status polling failed.', error);
                    if (statusText) {
                        statusText.textContent = 'check_error';
                    }
                    window.TechCourseKhqrCards?.setStatus(khqrCardId, 'pending', 'Unable to check payment status right now.');
                }
            }

            // Keep backend payment polling active on the checkout page even after the modal is closed.
            startStatusPolling();

            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) {
                    checkPaymentStatus();
                }
            });
        })();
    </script>
@endsection
