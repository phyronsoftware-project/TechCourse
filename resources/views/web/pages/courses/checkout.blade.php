@extends('web.layouts.app')

@section('title', __('Course Checkout'))

@php
    $khqrPreviewUrl = asset('ABA_Images/KHQR_Static.png');
    $courseDescription = $course->short_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $course->description), 180);
    $paymentMethods = [
        ['name' => 'ABA KHQR', 'copy' => __('Scan to pay with any banking app'), 'image' => asset('ABA_Images/ABA-BANK.svg'), 'actionable' => true],
        [
            'name' => __('Card'),
            'copy' => __('Credit/Debit Card'),
            'image' => asset('ABA_Images/card_icon.png'),
            'logos' => [
                ['type' => 'image', 'src' => asset('ABA_Images/VISA-Copy.png'), 'alt' => 'Visa'],
                ['type' => 'mastercard'],
                ['type' => 'image', 'src' => asset('ABA_Images/JCB.png'), 'alt' => 'JCB'],
            ],
        ],
        ['name' => 'Alipay', 'copy' => __('Scan to pay with Alipay'), 'image' => asset('ABA_Images/Alipay.png')],
        ['name' => 'WeChat', 'copy' => __('Scan to pay with WeChat'), 'image' => asset('ABA_Images/Wechat.png')],
    ];
@endphp

@section('content')
    <style>
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
            grid-template-columns: minmax(0, 1.1fr) minmax(280px, 0.52fr);
            gap: 22px;
            align-items: start;
        }

        .checkout-test-alert {
            position: fixed;
            top: 50%;
            left: 50%;
            z-index: 1201;
            width: min(460px, calc(100vw - 28px));
            padding: 14px 16px;
            border-radius: 20px;
            border: 1px solid #fde68a;
            background: linear-gradient(180deg, #fff8db, #fff2b8);
            color: #854d0e;
            box-shadow: 0 10px 22px rgba(146, 64, 14, 0.08);
            transform: translate(-50%, -50%);
            transition: opacity 0.28s ease, transform 0.28s ease;
        }

        .checkout-test-alert-backdrop {
            position: fixed;
            inset: 0;
            z-index: 1200;
            background: rgba(15, 23, 42, 0.36);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            transition: opacity 0.28s ease;
        }

        .checkout-test-alert.is-hidden {
            opacity: 0;
            transform: translate(-50%, calc(-50% - 10px));
            pointer-events: none;
        }

        .checkout-test-alert-backdrop.is-hidden {
            opacity: 0;
            pointer-events: none;
        }

        .checkout-test-alert__title {
            margin: 0 0 6px;
            font-family: var(--font-lato);
            font-size: 0.94rem;
            font-weight: 800;
        }

        .checkout-test-alert__copy {
            margin: 0;
            font-size: 0.82rem;
            line-height: 1.65;
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
            transition: opacity 0.28s ease;
        }

        .khqr-modal.is-open {
            opacity: 1;
        }

        .khqr-modal__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(9, 17, 32, 0.52);
            backdrop-filter: blur(5px);
        }

        .khqr-modal__dialog {
            position: relative;
            width: min(355px, 100%);
            background: #ffffff;
            border-radius: 22px;
            border: 1px solid rgba(226, 232, 240, 0.95);
            box-shadow: 0 26px 70px rgba(15, 23, 42, 0.26);
            padding: 8px 0 8px;
            overflow: hidden;
            transform: translateY(20px) scale(0.96);
            opacity: 0;
            transition: transform 0.32s cubic-bezier(0.2, 0.7, 0.2, 1), opacity 0.32s ease;
        }

        .khqr-modal.is-open .khqr-modal__dialog {
            transform: translateY(0) scale(1);
            opacity: 1;
        }

        .khqr-modal__close {
            position: absolute;
            top: 12px;
            right: 14px;
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: #22c7ee;
            cursor: pointer;
            font-size: 1.2rem;
        }

        .khqr-modal__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 0 46px 2px 18px;
        }

        .khqr-modal__brand-image {
            width: 170px;
            display: block;
            object-fit: contain;
        }

        .khqr-modal__title {
            margin: 0;
            color: #0f2a52;
            font-family: var(--font-lato);
            font-size: 1.2rem;
            line-height: 1.2;
            font-weight: 700;
        }

        .khqr-modal__card {
            width: 100%;
            margin: 0;
            border-radius: 0;
            overflow: hidden;
            background: #ffffff;
            box-shadow: none;
            border: 0;
        }

        .khqr-modal__card-body {
            padding: 0;
            background: #ffffff;
        }

        .khqr-modal__qr {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100%;
            overflow: hidden;
            background: #ffffff;
        }

        .khqr-modal__qr img {
            width: 100%;
            max-height: 500px;
            display: block;
            object-fit: contain;
        }

        .khqr-modal__empty {
            padding: 24px 18px;
            text-align: center;
            color: #64748b;
            font-size: 12px;
            line-height: 1.7;
        }

        .khqr-modal__caption {
            width: 100%;
            margin: 4px 0 0;
            padding: 0 12px;
            text-align: center;
            color: #8a94a6;
            font-size: 11px;
            line-height: 1.65;
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
                width: min(355px, 100%);
            }

            .khqr-modal__top {
                padding: 0 42px 2px 12px;
            }

            .khqr-modal__brand-image {
                width: 138px;
            }

            .khqr-modal__title {
                font-size: 1.02rem;
            }

        }
    </style>

    <section class="checkout-shell">
        <h1 class="checkout-page-title">{{ $course->title }}</h1>

        {{-- Show a clear warning popup before any test payment action starts. --}}
        <div class="checkout-test-alert-backdrop" data-checkout-test-alert-backdrop></div>
        <div class="checkout-test-alert" data-checkout-test-alert>
            <p class="checkout-test-alert__title">{{ __('Test Website Notice') }}</p>
            <p class="checkout-test-alert__copy">{{ __('This website is for testing only. If you make a payment here, we may not accept issues or complaints related to test transactions.') }}</p>
        </div>

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

            <div class="khqr-modal__top">
                <h2 class="khqr-modal__title" id="khqr-modal-title">ABA KHQR</h2>
                <img
                    src="https://payway.ababank.com/kh/assets/img/ABA_PAYWAY_logo.svg"
                    alt="ABA PayWay"
                    class="khqr-modal__brand-image"
                >
            </div>

            <div class="khqr-modal__card">
                <div class="khqr-modal__card-body">
                    <div class="khqr-modal__qr">
                        @if ($khqrPreviewUrl)
                            <img src="{{ $khqrPreviewUrl }}" alt="ABA KHQR">
                        @else
                            <div class="khqr-modal__empty">
                                <strong>{{ __('KHQR preview is not ready yet') }}</strong><br>
                                {{ __('The checkout record is prepared. Later you can connect the real payment process and show live QR here.') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <p class="khqr-modal__caption">{{ __('Scan with ABA Mobile, or other Mobile Banking App supporting KHQR') }}</p>
        </div>
    </div>

    <script>
        (() => {
            const testAlert = document.querySelector('[data-checkout-test-alert]');
            const testAlertBackdrop = document.querySelector('[data-checkout-test-alert-backdrop]');
            const modal = document.querySelector('[data-khqr-modal]');
            const openButton = document.querySelector('[data-khqr-open]');
            const closeButtons = document.querySelectorAll('[data-khqr-close]');

            // Auto-hide the checkout test popup after 3 seconds.
            if (testAlert) {
                window.setTimeout(() => {
                    testAlert.classList.add('is-hidden');
                    testAlertBackdrop?.classList.add('is-hidden');

                    window.setTimeout(() => {
                        testAlert.hidden = true;
                        if (testAlertBackdrop) {
                            testAlertBackdrop.hidden = true;
                        }
                    }, 300);
                }, 3000);
            }

            if (!modal || !openButton) {
                return;
            }

            const openModal = () => {
                modal.hidden = false;
                document.body.style.overflow = 'hidden';

                requestAnimationFrame(() => {
                    modal.classList.add('is-open');
                });
            };

            const closeModal = () => {
                modal.classList.remove('is-open');

                window.setTimeout(() => {
                    modal.hidden = true;
                    document.body.style.overflow = '';
                }, 280);
            };

            openButton.addEventListener('click', openModal);
            closeButtons.forEach((button) => button.addEventListener('click', closeModal));

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.hidden) {
                    closeModal();
                }
            });
        })();
    </script>
@endsection
