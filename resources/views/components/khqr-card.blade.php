@php
    $cardId = $cardId ?? ('khqr-card-' . uniqid());
    $merchantName = $merchantName ?? config('bakong.merchant_name', 'TechCourse');
    $currencyCode = strtoupper((string) ($currency ?? 'KHR'));
    $expiredAtValue = $expiredAt ?? null;
    $formattedAmount = isset($amount) && $amount !== null
        ? number_format((float) $amount, $currencyCode === 'USD' ? 2 : 0)
        : null;
    $resolvedStatus = strtolower((string) ($status ?? 'pending'));
    $resolvedExpiredAt = $expiredAtValue instanceof \Carbon\CarbonInterface
        ? $expiredAtValue->toIso8601String()
        : (filled($expiredAtValue) ? (string) $expiredAtValue : '');
    $showMeta = !empty($showStatusMeta ?? false);
    $showCenterBadge = !empty($showCenterBadge ?? false);
    $qrCenterLabel = $qrCenterLabel ?? ($currencyCode === 'USD' ? '$' : '៛');
@endphp

@once
    <style>
        .khqr-card-shell {
            width: min(263px, 90vw);
            margin: 0 auto;
        }

        .khqr-card {
            position: relative;
            width: 263px;
            height: 383px;
            max-width: 90vw;
            background: #FFFFFF;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.18);
            font-family: Arial, Helvetica, sans-serif;
        }

        .khqr-header {
            position: relative;
            height: 58px;
            background: #E1232E;
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .khqr-logo {
            width: auto;
            height: 21px;
            max-width: calc(100% - 68px);
            display: block;
        }

        .khqr-corner {
            position: absolute;
            right: 0;
            bottom: -34px;
            width: 34px;
            height: 34px;
            background: #E1232E;
            clip-path: polygon(0 0, 100% 0, 100% 100%);
            z-index: 2;
        }

        .khqr-info {
            padding: 12px 46px 8px;
        }

        .khqr-merchant-name {
            color: #000000;
            font-size: 12px;
            font-weight: 400;
            line-height: 1.2;
            margin-bottom: 3px;
        }

        .khqr-amount-row {
            display: flex;
            align-items: baseline;
            gap: 7px;
        }

        .khqr-amount {
            color: #000000;
            font-size: 29px;
            font-weight: 800;
            line-height: 1;
        }

        .khqr-currency {
            color: #000000;
            font-size: 12px;
            font-weight: 500;
            line-height: 1;
        }

        .khqr-dashed-line {
            border-top: 1px dashed rgba(0, 0, 0, 0.35);
            width: 100%;
            height: 0;
            margin: 0;
        }

        .khqr-qr-wrapper {
            width: 210px;
            height: 210px;
            margin: 12px auto 0;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .khqr-qr-surface {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #FFFFFF;
        }

        .khqr-qr-wrapper canvas,
        .khqr-qr-wrapper img,
        .khqr-qr-wrapper svg {
            width: 210px !important;
            height: 210px !important;
            display: block;
        }

        .khqr-qr-center-icon {
            position: absolute;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #000000;
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 15px;
            border: 3px solid #FFFFFF;
            z-index: 2;
        }

        .khqr-card__meta {
            padding: 18px 26px 0;
            display: grid;
            gap: 8px;
            text-align: center;
        }

        .khqr-card__meta-top {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .khqr-card__status-pill {
            min-height: 28px;
            padding: 0 12px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #0f172a;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
        }

        .khqr-card__timer {
            color: #475569;
            font-size: 12px;
            line-height: 1.5;
        }

        .khqr-card__message {
            margin: 0;
            min-height: 18px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.5;
        }

        .khqr-card-shell[data-state="success"] .khqr-card__status-pill {
            background: #dcfce7;
            color: #166534;
        }

        .khqr-card-shell[data-state="expired"] .khqr-card__status-pill,
        .khqr-card-shell[data-state="failed"] .khqr-card__status-pill {
            background: #fee2e2;
            color: #b91c1c;
        }

        .khqr-card-shell[data-state="expired"] .khqr-card__message,
        .khqr-card-shell[data-state="failed"] .khqr-card__message {
            color: #b91c1c;
        }

        .khqr-card-shell[data-state="success"] .khqr-card__message {
            color: #166534;
        }

        .khqr-card__empty {
            width: 210px;
            height: 210px;
            margin: 12px auto 0;
            display: grid;
            place-items: center;
            text-align: center;
            color: #64748b;
            font-size: 12px;
            line-height: 1.6;
        }

        .khqr-toast-stack {
            position: fixed;
            right: 18px;
            bottom: 18px;
            z-index: 1600;
            display: grid;
            gap: 10px;
        }

        .khqr-toast {
            min-width: 220px;
            max-width: min(360px, calc(100vw - 36px));
            padding: 12px 14px;
            border-radius: 14px;
            background: #0f172a;
            color: #ffffff;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.18);
            font-size: 13px;
            line-height: 1.5;
        }

        .khqr-toast--success {
            background: #166534;
        }

        .khqr-toast--error {
            background: #b91c1c;
        }

        @media (max-width: 355px) {
            .khqr-card {
                width: 90vw;
                height: auto;
                aspect-ratio: 263 / 383;
            }
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        (() => {
            const toastStackId = 'khqr-toast-stack';
            const countdownTimers = new Map();

            function ensureToastStack() {
                let stack = document.getElementById(toastStackId);
                if (!stack) {
                    stack = document.createElement('div');
                    stack.id = toastStackId;
                    stack.className = 'khqr-toast-stack';
                    document.body.appendChild(stack);
                }

                return stack;
            }

            function showToast(message, tone = 'default') {
                const stack = ensureToastStack();
                const toast = document.createElement('div');
                toast.className = `khqr-toast${tone !== 'default' ? ` khqr-toast--${tone}` : ''}`;
                toast.textContent = message;
                stack.appendChild(toast);

                window.setTimeout(() => {
                    toast.remove();
                }, 3200);
            }

            function formatCountdown(msRemaining) {
                const totalSeconds = Math.max(0, Math.floor(msRemaining / 1000));
                const minutes = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
                const seconds = String(totalSeconds % 60).padStart(2, '0');
                return `${minutes}:${seconds}`;
            }

            function setCardStatus(card, status, message = '') {
                if (!card) {
                    return;
                }

                const normalizedStatus = String(status || 'pending').toLowerCase();
                card.dataset.state = normalizedStatus;
                const statusPill = card.querySelector('[data-khqr-status]');
                const messageNode = card.querySelector('[data-khqr-message]');
                const timerNode = card.querySelector('[data-khqr-timer]');

                if (statusPill) {
                    statusPill.textContent = normalizedStatus.charAt(0).toUpperCase() + normalizedStatus.slice(1);
                }

                if (messageNode) {
                    messageNode.textContent = message;
                }

                if ((normalizedStatus === 'success' || normalizedStatus === 'expired' || normalizedStatus === 'failed') && timerNode) {
                    timerNode.textContent = normalizedStatus === 'success'
                        ? 'Payment completed.'
                        : 'QR is no longer active.';
                }
            }

            function startCountdown(card) {
                const expiredAt = card.dataset.expiredAt;
                const timerNode = card.querySelector('[data-khqr-timer]');
                if (!expiredAt || !timerNode) {
                    return;
                }

                const expireMs = Date.parse(expiredAt);
                if (Number.isNaN(expireMs)) {
                    timerNode.textContent = '';
                    return;
                }

                const cardId = card.dataset.cardId;
                const existingTimer = countdownTimers.get(cardId);
                if (existingTimer) {
                    window.clearInterval(existingTimer);
                }

                const tick = () => {
                    const state = String(card.dataset.state || 'pending').toLowerCase();
                    if (['success', 'expired', 'failed'].includes(state)) {
                        return;
                    }

                    const remaining = expireMs - Date.now();
                    if (remaining <= 0) {
                        timerNode.textContent = 'QR expired. Please create new payment.';
                        setCardStatus(card, 'expired', 'QR expired. Please create new payment.');
                        document.dispatchEvent(new CustomEvent('khqr:expired', { detail: { cardId } }));
                        const intervalId = countdownTimers.get(cardId);
                        if (intervalId) {
                            window.clearInterval(intervalId);
                        }
                        return;
                    }

                    timerNode.textContent = `QR will expire in ${formatCountdown(remaining)}`;
                };

                tick();
                const intervalId = window.setInterval(tick, 1000);
                countdownTimers.set(cardId, intervalId);
            }

            function renderQr(card) {
                const qrSurface = card.querySelector('[data-khqr-qr]');
                const qrString = String(card.dataset.khqrString || '');
                const fallbackSrc = String(card.dataset.fallbackSrc || '');
                if (!qrSurface) {
                    return;
                }

                qrSurface.innerHTML = '';
                // Keep the generated QR at the official card size, including while the modal is hidden.
                const qrSize = Math.min(210, Math.round(qrSurface.getBoundingClientRect().width || 210));

                if (qrString !== '' && typeof window.QRCode !== 'undefined') {
                    new window.QRCode(qrSurface, {
                        text: qrString,
                        width: qrSize,
                        height: qrSize,
                        colorDark: '#000000',
                        colorLight: '#ffffff',
                        correctLevel: window.QRCode.CorrectLevel.M,
                    });
                    return;
                }

                if (fallbackSrc !== '') {
                    const img = document.createElement('img');
                    img.src = fallbackSrc;
                    img.alt = 'KHQR';
                    qrSurface.appendChild(img);
                }
            }

            function initCards() {
                document.querySelectorAll('[data-khqr-card]').forEach((card) => {
                    renderQr(card);
                    startCountdown(card);
                });
            }

            document.addEventListener('DOMContentLoaded', initCards);
            window.addEventListener('resize', () => {
                window.clearTimeout(window.__khqrResizeTimer);
                window.__khqrResizeTimer = window.setTimeout(initCards, 120);
            });

            window.TechCourseKhqrCards = {
                initCards,
                showToast,
                setStatus(cardId, status, message = '') {
                    const card = document.querySelector(`[data-khqr-card][data-card-id="${cardId}"]`);
                    setCardStatus(card, status, message);
                },
            };
        })();
    </script>
@endonce

<div
    class="khqr-card-shell"
    data-khqr-card
    data-card-id="{{ $cardId }}"
    data-khqr-string="{{ $khqrString ?? '' }}"
    data-fallback-src="{{ $qrImageUrl ?? '' }}"
    data-expired-at="{{ $resolvedExpiredAt }}"
    data-state="{{ $resolvedStatus }}"
>
    <div class="khqr-card">
        <div class="khqr-header">
            <img src="{{ asset('khqr/khqr-logo-white.svg') }}" alt="KHQR" class="khqr-logo">
            <div class="khqr-corner" aria-hidden="true"></div>
        </div>

        <div class="khqr-info">
            <div class="khqr-merchant-name">{{ $merchantName }}</div>

            @if ($formattedAmount !== null)
                <div class="khqr-amount-row">
                    <span class="khqr-amount">{{ $formattedAmount }}</span>
                    <span class="khqr-currency">{{ $currencyCode }}</span>
                </div>
            @else
                <div class="khqr-amount-row">
                    <span class="khqr-currency">{{ $currencyCode }}</span>
                </div>
            @endif
        </div>

        <div class="khqr-dashed-line"></div>

        @if (filled($khqrString ?? null) || filled($qrImageUrl ?? null))
            <div class="khqr-qr-wrapper">
                <div class="khqr-qr-surface" data-khqr-qr></div>
                @if ($showCenterBadge)
                    <div class="khqr-qr-center-icon" aria-hidden="true">
                        {{ $qrCenterLabel }}
                    </div>
                @endif
            </div>
        @else
            <div class="khqr-card__empty">
                {{ $emptyMessage ?? __('KHQR preview is not ready yet. Please generate a new payment QR.') }}
            </div>
        @endif

    </div>

    @if ($showMeta)
        <div class="khqr-card__meta">
            <div class="khqr-card__meta-top">
                <span class="khqr-card__status-pill" data-khqr-status>{{ ucfirst($resolvedStatus) }}</span>
                @if ($resolvedExpiredAt !== '')
                    <span class="khqr-card__timer" data-khqr-timer></span>
                @endif
            </div>
            <p class="khqr-card__message" data-khqr-message>{{ $statusMessage ?? '' }}</p>
        </div>
    @endif
</div>
