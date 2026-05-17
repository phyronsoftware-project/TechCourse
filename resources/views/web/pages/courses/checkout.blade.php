@extends('web.layouts.app')

@section('title', __('Course Checkout'))

@php
    $khqrPreviewUrl = null;
    $paymentPayload = is_array($payment->response_payload) ? $payment->response_payload : [];
    $courseDescription = $course->short_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $course->description), 180);

    if (!empty($payment->qr_image_url)) {
        $khqrPreviewUrl = $payment->qr_image_url;
    } elseif (!empty($paymentPayload['qrImage'])) {
        $khqrPreviewUrl = $paymentPayload['qrImage'];
    } elseif (!empty($payment->khqr_deeplink)) {
        $khqrPreviewUrl = 'https://quickchart.io/qr?size=420&text=' . urlencode($payment->khqr_deeplink);
    }
@endphp

@section('content')
    <style>
        .checkout-shell {
            width: min(1160px, calc(100% - 40px));
            margin: 0 auto;
            padding-bottom: 52px;
        }

        .checkout-page-title {
            margin: 0 0 34px;
            color: #ffffff;
            text-align: center;
            font-family: var(--font-lato);
            font-size: clamp(1.55rem, 2.2vw, 2rem);
            line-height: 1.18;
            font-weight: 600;
        }

        .checkout-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.02fr) minmax(300px, 0.82fr);
            gap: 24px;
            align-items: start;
        }

        .checkout-course-card {
            overflow: hidden;
            border-radius: 0;
            background: #edf2f7;
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: none;
        }

        .checkout-course-media {
            position: relative;
            aspect-ratio: 16 / 9;
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
            padding: 22px 24px 24px;
            color: #0f172a;
        }

        .checkout-course-name {
            margin: 0;
            font-family: var(--font-lato);
            font-size: 1.2rem;
            line-height: 1.28;
            font-weight: 700;
        }

        .checkout-course-copy {
            margin: 12px 0 0;
            color: #475569;
            font-size: 13px;
            line-height: 1.7;
        }

        .checkout-course-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }

        .checkout-pill {
            min-height: 32px;
            padding: 0 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 999px;
            background: #ffffff;
            color: #0f2345;
            border: 1px solid #d9e3ef;
            font-size: 11px;
            font-weight: 700;
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.05);
        }

        .checkout-payment-list {
            display: grid;
            gap: 18px;
        }

        .checkout-option {
            width: 100%;
            padding: 18px 18px;
            border: 1px solid #e9eef5;
            border-radius: 20px;
            background: #ffffff;
            display: grid;
            grid-template-columns: 76px minmax(0, 1fr) 42px;
            gap: 16px;
            align-items: center;
            text-align: left;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .checkout-option.is-actionable {
            cursor: pointer;
        }

        .checkout-option.is-actionable:hover {
            transform: translateY(-1px);
            border-color: #dde5ef;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.07);
        }

        .checkout-option.is-disabled {
            cursor: default;
        }

        .checkout-option__icon {
            width: 72px;
            height: 72px;
            border-radius: 14px;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }

        .checkout-option__icon--aba {
            background: linear-gradient(180deg, #12718d 0 68%, #eb2f36 68% 100%);
        }

        .checkout-option__icon--aba::before {
            content: "ABA";
            position: absolute;
            top: 10px;
            left: 0;
            right: 0;
            color: #ffffff;
            text-align: center;
            font-family: var(--font-lato);
            font-size: 21px;
            font-weight: 800;
            letter-spacing: 0.06em;
        }

        .checkout-option__icon--aba::after {
            content: "KHQR";
            position: absolute;
            bottom: 7px;
            left: 0;
            right: 0;
            color: #ffffff;
            text-align: center;
            font-family: var(--font-lato);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.05em;
        }

        .checkout-option__icon--card,
        .checkout-option__icon--alipay,
        .checkout-option__icon--wechat {
            display: grid;
            place-items: center;
            color: #ffffff;
            font-size: 30px;
        }

        .checkout-option__icon--card {
            background: linear-gradient(135deg, #0d7b93, #06647f);
        }

        .checkout-option__icon--alipay {
            background: linear-gradient(135deg, #1d9bf0, #1085d6);
        }

        .checkout-option__icon--wechat {
            background: linear-gradient(135deg, #22c10b, #16a504);
        }

        .checkout-option__body {
            min-width: 0;
        }

        .checkout-option__title {
            margin: 0;
            color: #0f2345;
            font-family: var(--font-lato);
            font-size: 1rem;
            line-height: 1.18;
            font-weight: 700;
        }

        .checkout-option__subtitle {
            margin: 4px 0 0;
            color: #74849a;
            font-size: 11px;
            line-height: 1.45;
        }

        .checkout-option__badges,
        .checkout-card-brands {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }

        .checkout-card-brand {
            min-height: 18px;
            padding: 0 6px;
            border-radius: 3px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-family: var(--font-lato);
            font-size: 8px;
            font-weight: 800;
            letter-spacing: 0.02em;
            border: 0;
            line-height: 1;
        }

        .checkout-card-brand--visa {
            background: #1f5fbf;
        }

        .checkout-card-brand--mastercard {
            background: linear-gradient(90deg, #f59e0b 0 50%, #111827 50% 100%);
        }

        .checkout-card-brand--unionpay {
            background: linear-gradient(90deg, #d91f26 0 35%, #0f6bdc 35% 70%, #22a35a 70% 100%);
        }

        .checkout-card-brand--jcb {
            background: linear-gradient(90deg, #179c52 0 33%, #1950b6 33% 66%, #d71f34 66% 100%);
        }

        .checkout-option__arrow {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f4f6fb;
            color: #5a6779;
            font-size: 16px;
        }

        .checkout-page-note {
            margin: 14px 4px 0;
            color: rgba(255, 255, 255, 0.78);
            font-size: 12px;
            line-height: 1.6;
        }

        .khqr-modal[hidden] {
            display: none;
        }

        .khqr-modal {
            position: fixed;
            inset: 0;
            z-index: 1000;
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
            background: rgba(9, 17, 32, 0.72);
            backdrop-filter: blur(8px);
            opacity: 0;
            transition: opacity 0.28s ease;
        }

        .khqr-modal.is-open .khqr-modal__backdrop {
            opacity: 1;
        }

        .khqr-modal__dialog {
            position: relative;
            width: min(440px, 100%);
            border-radius: 28px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.28);
            padding: 24px;
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
            top: 14px;
            right: 14px;
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 999px;
            background: #f1f5f9;
            color: #0f172a;
            cursor: pointer;
        }

        .khqr-modal__top {
            padding-right: 42px;
        }

        .khqr-modal__eyebrow {
            margin: 0;
            color: #12718d;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .khqr-modal__title {
            margin: 10px 0 0;
            color: #0f172a;
            font-family: var(--font-lato);
            font-size: 1.7rem;
            line-height: 1.2;
        }

        .khqr-modal__copy {
            margin: 8px 0 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.65;
        }

        .khqr-modal__qr-wrap {
            margin-top: 22px;
            padding: 18px;
            border-radius: 24px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .khqr-modal__qr {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 280px;
            border-radius: 18px;
            background: #ffffff;
            overflow: hidden;
        }

        .khqr-modal__qr img {
            width: min(290px, 100%);
            display: block;
            object-fit: contain;
        }

        .khqr-modal__empty {
            padding: 28px 18px;
            text-align: center;
            color: #64748b;
            font-size: 13px;
            line-height: 1.7;
        }

        .khqr-modal__meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 18px;
        }

        .khqr-modal__stat {
            padding: 14px 16px;
            border-radius: 18px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .khqr-modal__label {
            margin: 0;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
        }

        .khqr-modal__value {
            margin: 8px 0 0;
            color: #0f172a;
            font-family: var(--font-lato);
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.35;
            word-break: break-word;
        }

        @media (max-width: 980px) {
            .checkout-layout {
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

            .checkout-option {
                grid-template-columns: 60px minmax(0, 1fr) 36px;
                gap: 10px;
                padding: 12px;
            }

            .checkout-option__icon {
                width: 56px;
                height: 56px;
                border-radius: 13px;
            }

            .checkout-option__icon--aba::before {
                top: 10px;
                font-size: 20px;
            }

            .checkout-option__icon--aba::after {
                bottom: 7px;
                font-size: 10px;
            }

            .checkout-option__title {
                font-size: 0.95rem;
            }

            .checkout-option__subtitle {
                font-size: 11px;
            }

            .khqr-modal__dialog {
                padding: 18px;
                border-radius: 24px;
            }

            .khqr-modal__meta {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <section class="checkout-shell">
        <h1 class="checkout-page-title">{{ $course->title }}</h1>

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

            <div>
                <div class="checkout-payment-list">
                    <button type="button" class="checkout-option is-actionable" data-khqr-open>
                        <span class="checkout-option__icon checkout-option__icon--aba" aria-hidden="true" style="width:56px;height:56px;border-radius:15px;"></span>

                        <span class="checkout-option__body">
                            <h3 class="checkout-option__title" style="font-size:0.92rem;line-height:1.2;">ABA KHQR</h3>
                            <p class="checkout-option__subtitle">{{ __('Scan to pay with any banking app') }}</p>
                        </span>

                        <span class="checkout-option__arrow" style="width:32px;height:32px;border-radius:10px;font-size:14px;">
                            <i class="fa-solid fa-chevron-right"></i>
                        </span>
                    </button>

                    <button type="button" class="checkout-option is-disabled" aria-disabled="true">
                        <span class="checkout-option__icon checkout-option__icon--card" aria-hidden="true">
                            <i class="fa-regular fa-credit-card"></i>
                        </span>

                        <span class="checkout-option__body">
                            <h3 class="checkout-option__title">Credit/Debit Card</h3>
                            <p class="checkout-option__subtitle">{{ __('Scan to pay with card') }}</p>
                            <span class="checkout-card-brands">
                                <span class="checkout-card-brand checkout-card-brand--visa">VISA</span>
                                <span class="checkout-card-brand checkout-card-brand--mastercard">MC</span>
                                <span class="checkout-card-brand checkout-card-brand--unionpay">UnionPay</span>
                                <span class="checkout-card-brand checkout-card-brand--jcb">JCB</span>
                            </span>
                        </span>

                        <span class="checkout-option__arrow">
                            <i class="fa-solid fa-chevron-right"></i>
                        </span>
                    </button>

                    <button type="button" class="checkout-option is-disabled" aria-disabled="true">
                        <span class="checkout-option__icon checkout-option__icon--alipay" aria-hidden="true">
                            <i class="fa-solid fa-wallet"></i>
                        </span>

                        <span class="checkout-option__body">
                            <h3 class="checkout-option__title">Alipay</h3>
                            <p class="checkout-option__subtitle">{{ __('Scan to pay with Alipay') }}</p>
                        </span>

                        <span class="checkout-option__arrow">
                            <i class="fa-solid fa-chevron-right"></i>
                        </span>
                    </button>

                    <button type="button" class="checkout-option is-disabled" aria-disabled="true">
                        <span class="checkout-option__icon checkout-option__icon--wechat" aria-hidden="true">
                            <i class="fa-brands fa-weixin"></i>
                        </span>

                        <span class="checkout-option__body">
                            <h3 class="checkout-option__title">WeChat</h3>
                            <p class="checkout-option__subtitle">{{ __('Scan to pay with WeChat') }}</p>
                        </span>

                        <span class="checkout-option__arrow">
                            <i class="fa-solid fa-chevron-right"></i>
                        </span>
                    </button>
                </div>

                @if (!empty($khqrError))
                    <p class="checkout-page-note">{{ $khqrError }}</p>
                @else
                    <p class="checkout-page-note">{{ __('KHQR is the only active payment UI for now. Other methods are prepared as interface only and can connect process later.') }}</p>
                @endif
            </div>
        </div>
    </section>

    <div class="khqr-modal" data-khqr-modal hidden>
        <div class="khqr-modal__backdrop" data-khqr-close></div>

        <div class="khqr-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="khqr-modal-title">
            <button type="button" class="khqr-modal__close" data-khqr-close aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="khqr-modal__top">
                <p class="khqr-modal__eyebrow">ABA KHQR</p>
                <h2 class="khqr-modal__title" id="khqr-modal-title">{{ __('Scan to complete payment') }}</h2>
                <p class="khqr-modal__copy">{{ __('Use any supported banking app to scan this KHQR for your course checkout.') }}</p>
            </div>

            <div class="khqr-modal__qr-wrap">
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

            <div class="khqr-modal__meta">
                <div class="khqr-modal__stat">
                    <p class="khqr-modal__label">{{ __('Amount') }}</p>
                    <p class="khqr-modal__value">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</p>
                </div>

                <div class="khqr-modal__stat">
                    <p class="khqr-modal__label">{{ __('Status') }}</p>
                    <p class="khqr-modal__value">{{ ucfirst($payment->status ?: 'pending') }}</p>
                </div>

                <div class="khqr-modal__stat">
                    <p class="khqr-modal__label">{{ __('Order No') }}</p>
                    <p class="khqr-modal__value">{{ $order->order_no }}</p>
                </div>

                <div class="khqr-modal__stat">
                    <p class="khqr-modal__label">{{ __('Course') }}</p>
                    <p class="khqr-modal__value">{{ $course->title }}</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const modal = document.querySelector('[data-khqr-modal]');
            const openButton = document.querySelector('[data-khqr-open]');
            const closeButtons = document.querySelectorAll('[data-khqr-close]');

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
