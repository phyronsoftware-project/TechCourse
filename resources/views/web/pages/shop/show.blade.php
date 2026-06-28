@extends('web.layouts.app')

@section('title', $product->name)

@php
    $authUser = auth()->user();
    $shareUrl = request()->fullUrl();
    $shareTitle = $product->name . ' - TechCourse Shop';
    $shareDescription = trim((string) ($product->description ?: 'Useful IT product for app development, web development, learning, and daily productivity setup.'));
    $salePrice = (float) $product->sale_price;
    $costPrice = (float) $product->cost_price;
    $saveAmount = max($costPrice - $salePrice, 0);
    $monthlyPrice = $salePrice > 0 ? ceil(($salePrice / 12) * 100) / 100 : 0;
    $galleryItems = $gallery->values();
    $shareImage = $product->image_url ?: ($galleryItems->first() ?: asset('logo/logo1.png'));
    $shopKhqrPreviewUrl = asset('ABA_Images/KHQR_Static.png');
    $clientAddress = collect([$authUser?->address, $authUser?->city, $authUser?->province])
        ->filter(fn ($value) => filled($value))
        ->implode(', ');
    $clientInfoItems = [
        ['label' => __('Name'), 'value' => $authUser?->name],
        ['label' => __('Telephone'), 'value' => $authUser?->phone],
        ['label' => __('Gmail'), 'value' => $authUser?->email],
        ['label' => __('Address'), 'value' => $clientAddress],
    ];
    $paymentMethods = [
        ['name' => 'ABA KHQR', 'copy' => __('Scan to pay with any banking app'), 'image' => asset('ABA_Images/ABA-BANK.svg')],
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

@section('meta_description', $shareDescription)
@section('meta_og_type', 'product')

@push('meta')
    <meta property="og:title" content="{{ $shareTitle }}">
    <meta property="og:description" content="{{ $shareDescription }}">
    <meta property="og:url" content="{{ $shareUrl }}">
    <meta property="og:image" content="{{ $shareImage }}">
    <meta property="og:image:alt" content="{{ $product->name }}">
    <meta property="product:price:amount" content="{{ number_format($salePrice, 2, '.', '') }}">
    <meta property="product:price:currency" content="USD">
    <meta property="product:availability" content="{{ $product->stock_qty > 0 ? 'in stock' : 'out of stock' }}">
    <meta name="twitter:title" content="{{ $shareTitle }}">
    <meta name="twitter:description" content="{{ $shareDescription }}">
    <meta name="twitter:image" content="{{ $shareImage }}">
@endpush

@section('content')
    <style>
        .shop-detail-page {
            width: min(1320px, calc(100% - 32px));
            margin: 0 auto;
            display: grid;
            gap: 26px;
            padding-bottom: 44px;
        }

        .shop-detail-shell {
            display: grid;
            grid-template-columns: minmax(0, 1.9fr) minmax(240px, 0.88fr);
            gap: 22px;
            align-items: start;
        }

        .shop-detail-primary,
        .shop-detail-payments,
        .shop-detail-pay-card,
        .shop-detail-client-card {
            background: #ffffff;
            border: 1px solid #e1eaf3;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.05);
        }

        .shop-detail-primary {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1.02fr) minmax(320px, 0.92fr);
            gap: 0;
            overflow: hidden;
            border: 1px solid #e1eaf3;
            isolation: isolate;
        }

        .shop-detail-primary::before {
            content: "";
            position: absolute;
            inset: 0;
            padding: 1px;
            background: conic-gradient(
                from 0deg,
                transparent 0deg 316deg,
                #173f88 316deg 324deg,
                #0038e2 324deg 332deg,
                #015aff 332deg 340deg,
                #08a4ff 340deg 350deg,
                #02b5ff 350deg 360deg
            );
            animation: shopDetailBorderFlow 3.6s linear infinite;
            pointer-events: none;
            z-index: 0;
            -webkit-mask:
                linear-gradient(#fff 0 0) content-box,
                linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
        }

        .shop-detail-primary > * {
            position: relative;
            z-index: 1;
        }

        .shop-detail-gallery {
            padding: 16px;
            display: grid;
            gap: 12px;
        }

        @keyframes shopDetailBorderFlow {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .shop-detail-breadcrumbs {
            color: #6a7a90;
            font-size: 0.72rem;
            line-height: 1.5;
        }

        .shop-detail-breadcrumbs a {
            color: #4f6684;
            text-decoration: none;
        }

        .shop-detail-main {
            position: relative;
            min-height: 360px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 22px;
            background:
                radial-gradient(circle at top, rgba(8, 164, 255, 0.08), transparent 55%),
                linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }

        .shop-detail-main img {
            width: 100%;
            max-width: 360px;
            max-height: 320px;
            object-fit: contain;
        }

        .shop-detail-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
            border: 0;
            border-radius: 999px;
            background: #f4f8fd;
            color: #4b5d76;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .shop-detail-nav--prev {
            left: 14px;
        }

        .shop-detail-nav--next {
            right: 14px;
        }

        .shop-detail-dots {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .shop-detail-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #d7e1ed;
            border: 0;
            padding: 0;
            cursor: pointer;
        }

        .shop-detail-dot.is-active {
            background: #173f88;
        }

        .shop-detail-thumbs {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .shop-detail-thumb {
            height: 72px;
            border: 1px solid #dce6f2;
            background: #fbfdff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            cursor: pointer;
        }

        .shop-detail-thumb.is-active {
            border-color: #173f88;
            background: #f6f9ff;
        }

        .shop-detail-thumb img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .shop-detail-info {
            padding: 18px;
            display: grid;
            align-content: start;
            gap: 14px;
        }

        .shop-detail-heading {
            display: grid;
            gap: 4px;
        }

        .shop-detail-title {
            margin: 0;
            color: #0f172a;
            font-size: 1.7rem;
            line-height: 1.14;
            font-weight: 850;
            font-family: var(--font-lato);
        }

        .shop-detail-category {
            color: #71839a;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .shop-detail-status-row {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            flex-wrap: wrap;
        }

        .shop-detail-badge {
            min-height: 28px;
            padding: 0 12px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            font-weight: 800;
        }

        .shop-detail-badge.is-stock {
            background: #e9f9ef;
            color: #167c3a;
        }

        .shop-detail-badge.is-out {
            background: #fff0f3;
            color: #d6284a;
        }

        .shop-detail-prices {
            display: grid;
            gap: 4px;
        }

        .shop-detail-cost {
            color: #a4afbf;
            font-size: 0.84rem;
            font-weight: 700;
            text-decoration: line-through;
        }

        .shop-detail-sale {
            color: #173f88;
            font-size: 2.1rem;
            line-height: 1;
            font-weight: 850;
            font-family: var(--font-lato);
        }

        .shop-detail-save {
            color: #1e40af;
            font-size: 0.76rem;
            font-weight: 700;
        }

        .shop-detail-copy {
            margin: 0;
            color: #62758e;
            font-size: 0.84rem;
            line-height: 1.8;
        }

        .shop-detail-meta {
            display: grid;
            gap: 6px;
            color: #44556c;
            font-size: 0.78rem;
        }

        .shop-detail-controls {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 180px;
            gap: 12px;
        }

        .shop-detail-select,
        .shop-detail-qty {
            min-height: 42px;
            border: 1px solid #e0e8f2;
            background: #fbfdff;
        }

        .shop-detail-select {
            width: 100%;
            padding: 0 14px;
            color: #475569;
            font-size: 0.78rem;
            outline: none;
        }

        .shop-detail-qty {
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 8px;
        }

        .shop-detail-qty button {
            width: 28px;
            height: 28px;
            border: 0;
            background: transparent;
            color: #334155;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
        }

        .shop-detail-qty span {
            color: #334155;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .shop-detail-add {
            width: 230px;
            min-width: 230px;
            min-height: 47px;
            border: 1px solid #173f88;
            background: #173f88;
            color: #ffffff;
            font-size: 0.88rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
        }

        .shop-detail-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: nowrap;
            width: fit-content;
            max-width: 100%;
        }

        .shop-detail-download {
            width: 108px;
            min-width: 108px;
            min-height: 47px;
            border: 1px solid #d7e3f1;
            background: #ffffff;
            color: #173f88;
            font-size: 0.78rem;
            font-weight: 800;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
        }

        .shop-detail-download:hover {
            background: #f8fbff;
            border-color: #c9d8ec;
        }

        .shop-detail-share {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            color: #8090a5;
            font-size: 0.68rem;
        }

        .shop-detail-share a {
            width: 31px;
            height: 31px;
            border: 1px solid #d9e5f3;
            border-radius: 999px;
            background: #f8fbff;
            color: #69809d;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
            padding: 0;
            transition: transform 0.2s ease, border-color 0.2s ease, color 0.2s ease, background 0.2s ease;
        }

        .shop-detail-share a:hover {
            transform: translateY(-1px);
        }

        .shop-detail-share a.shop-detail-share__facebook {
            background: #4267b2;
            border-color: #4267b2;
            color: #ffffff;
        }

        .shop-detail-share a.shop-detail-share__telegram {
            background: #229ed9;
            border-color: #229ed9;
            color: #ffffff;
        }

        .shop-detail-share a.shop-detail-share__x {
            background: #60748f;
            border-color: #60748f;
            color: #ffffff;
        }

        .shop-detail-share a.shop-detail-share__email {
            background: #6d4aff;
            border-color: #6d4aff;
            color: #ffffff;
        }

        .shop-detail-share a.shop-detail-share__copy {
            background: #0f7ccf;
            border-color: #0f7ccf;
            color: #ffffff;
        }

        .shop-detail-share a.shop-detail-share__facebook:hover {
            border-color: #35589b;
            background: #35589b;
        }

        .shop-detail-share a.shop-detail-share__telegram:hover {
            border-color: #1b8cc3;
            background: #1b8cc3;
        }

        .shop-detail-share a.shop-detail-share__x:hover {
            border-color: #52657d;
            background: #52657d;
        }

        .shop-detail-share a.shop-detail-share__email:hover {
            border-color: #5d3fe0;
            background: #5d3fe0;
        }

        .shop-detail-share a.shop-detail-share__copy:hover {
            border-color: #0b6fb9;
            background: #0b6fb9;
        }

        .shop-detail-share strong {
            color: #1e293b;
            letter-spacing: 0.03em;
            margin-right: 2px;
            font-size: 0.72rem;
        }

        .shop-detail-payments {
            padding: 16px;
            display: grid;
            gap: 12px;
            align-content: start;
            height: auto;
        }

        .shop-detail-side {
            display: grid;
            gap: 16px;
            align-content: start;
        }

        .shop-detail-client {
            padding: 16px;
            display: grid;
            gap: 12px;
            align-content: start;
        }

        .shop-detail-payments::before {
            content: "{{ __('Select Payment Method') }}";
            color: #0f172a;
            font-size: 0.92rem;
            font-weight: 850;
            font-family: var(--font-lato);
            margin-bottom: 2px;
        }

        .shop-detail-client::before {
            content: "{{ __('Client Information') }}";
            color: #0f172a;
            font-size: 0.92rem;
            font-weight: 850;
            font-family: var(--font-lato);
            margin-bottom: 2px;
        }

        .shop-detail-pay-card {
            display: grid;
            grid-template-columns: 52px minmax(0, 1fr) auto;
            gap: 12px;
            align-items: center;
            padding: 10px 14px;
            border-radius: 18px;
            border: 1px solid #ebf1f7;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
        }

        .shop-detail-pay-card--button {
            width: 100%;
            background: #ffffff;
            cursor: pointer;
            text-align: left;
        }

        .shop-detail-pay-card--button:hover {
            border-color: #dbe6f1;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.07);
        }

        .shop-detail-pay-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #ffffff;
        }

        .shop-detail-pay-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .shop-detail-pay-name {
            color: #10203c;
            font-size: 0.82rem;
            font-weight: 800;
            font-family: var(--font-lato);
            line-height: 1.25;
        }

        .shop-detail-pay-copy {
            color: #6c7d93;
            font-size: 0.67rem;
            line-height: 1.45;
            margin-top: 1px;
        }

        .shop-detail-pay-logos {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
            flex-wrap: wrap;
        }

        .shop-detail-pay-logo {
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .shop-detail-pay-logo img {
            height: 18px;
            width: auto;
            display: block;
        }

        .shop-detail-pay-logo--mastercard {
            width: 34px;
            height: 18px;
            border-radius: 3px;
            background: #000000;
            gap: 0;
            padding: 0 4px;
        }

        .shop-detail-pay-logo--mastercard span {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            display: block;
        }

        .shop-detail-pay-logo--mastercard span:first-child {
            background: #eb001b;
            margin-right: -4px;
        }

        .shop-detail-pay-logo--mastercard span:last-child {
            background: #f79e1b;
        }

        .shop-detail-pay-arrow {
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

        .shop-detail-client-card {
            border: 1px solid #ebf1f7;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
            padding: 12px 14px;
            display: grid;
            gap: 10px;
        }

        .shop-detail-client-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px 18px;
        }

        .shop-detail-client-item {
            display: flex;
            align-items: baseline;
            gap: 8px;
            min-width: 0;
        }

        .shop-detail-client-label {
            color: #5d6f88;
            font-size: 0.68rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .shop-detail-client-value {
            color: #10203c;
            font-size: 0.7rem;
            line-height: 1.5;
            font-weight: 700;
            word-break: break-word;
        }

        .shop-detail-client-note {
            color: #6c7d93;
            font-size: 0.64rem;
            line-height: 1.5;
            padding-top: 2px;
            border-top: 1px solid #eef3f8;
        }

        .shop-khqr-modal[hidden] {
            display: none;
        }

        .shop-khqr-modal {
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

        .shop-khqr-modal.is-open {
            opacity: 1;
        }

        .shop-khqr-modal__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(9, 17, 32, 0.52);
            backdrop-filter: blur(5px);
        }

        .shop-khqr-modal__dialog {
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

        .shop-khqr-modal.is-open .shop-khqr-modal__dialog {
            transform: translateY(0) scale(1);
            opacity: 1;
        }

        .shop-khqr-modal__close {
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

        .shop-khqr-modal__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 0 46px 2px 18px;
        }

        .shop-khqr-modal__title {
            margin: 0;
            color: #0f2a52;
            font-family: var(--font-lato);
            font-size: 1.2rem;
            line-height: 1.2;
            font-weight: 700;
        }

        .shop-khqr-modal__brand-image {
            width: 170px;
            display: block;
            object-fit: contain;
        }

        .shop-khqr-modal__card {
            width: 100%;
            margin: 0;
            border-radius: 0;
            overflow: hidden;
            background: #ffffff;
            box-shadow: none;
            border: 0;
        }

        .shop-khqr-modal__qr img {
            width: 100%;
            max-height: 500px;
            display: block;
            object-fit: contain;
        }

        .shop-khqr-modal__caption {
            width: 100%;
            margin: 4px 0 0;
            padding: 0 12px;
            text-align: center;
            color: #8a94a6;
            font-size: 11px;
            line-height: 1.65;
        }

        .shop-related {
            display: grid;
            gap: 14px;
        }

        .shop-related__head h2 {
            margin: 0;
            color: #0f172a;
            font-size: 1.3rem;
            font-weight: 850;
            font-family: var(--font-lato);
        }

        .shop-related__head p {
            margin: 4px 0 0;
            color: #6b7c92;
            font-size: 0.82rem;
        }

        .shop-grid {
            position: relative;
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 14px;
            align-items: stretch;
            overflow: visible;
        }

        .shop-card {
            position: relative;
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 336px;
            overflow: visible;
            border: 1px solid #dde4ee;
            background: #ffffff;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
            z-index: 1;
            transition: transform 0.32s ease, box-shadow 0.32s ease, border-color 0.32s ease;
        }

        .shop-card:hover {
            transform: translateY(-8px);
            border-color: #cfd9e5;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.10);
            z-index: 100;
        }

        .shop-card__media {
            position: relative;
            height: 160px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px 12px 8px;
            overflow: hidden;
            background:
                radial-gradient(circle at top, rgba(8, 164, 255, 0.08), transparent 52%),
                linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }

        .shop-card__media-link {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .shop-card__media img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .shop-card__ribbon {
            position: absolute;
            top: 8px;
            left: 0;
            min-width: 72px;
            height: 26px;
            padding: 0 14px 0 9px;
            clip-path: polygon(0 0, 100% 0, 88% 50%, 100% 100%, 0 100%);
            background: linear-gradient(135deg, #fe1707 0%, #df190c 100%);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            font-size: 0.56rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            z-index: 2;
        }

        .shop-card__warranty {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 52px;
            height: 52px;
            z-index: 2;
        }

        .shop-card__warranty img {
            width: 100%;
            height: 100%;
            display: block;
        }

        .shop-card__body {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            min-height: 164px;
            padding: 14px 12px 10px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.92) 0%, #ffffff 10%, #ffffff 100%);
            z-index: 5;
            transition: transform 0.32s ease, bottom 0.32s ease;
        }

        .shop-card:hover .shop-card__body {
            bottom: -34px;
            transform: translateY(-34px);
        }

        .shop-card__category {
            min-height: 13px;
            color: #6a7a90;
            font-size: 0.66rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            text-align: center;
        }

        .shop-card__title {
            margin: 0 0 8px;
            min-height: 38px;
            color: #364152;
            font-size: 0.84rem;
            line-height: 1.15;
            font-weight: 800;
            text-align: center;
            font-family: var(--font-lato);
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .shop-card__title-link {
            color: inherit;
            text-decoration: none;
        }

        .shop-card__copy {
            display: none;
        }

        .shop-card__meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            min-height: 22px;
            margin-bottom: 6px;
            color: #60738c;
            font-size: 0.68rem;
        }

        .shop-card__badge {
            display: inline-flex;
            align-items: center;
            min-height: 20px;
            padding: 0 8px;
            border-radius: 999px;
            font-size: 0.62rem;
            font-weight: 700;
        }

        .shop-card__badge.is-stock {
            background: #e9f9ef;
            color: #167c3a;
        }

        .shop-card__badge.is-out {
            background: #fff0f3;
            color: #d6284a;
        }

        .shop-card__prices {
            display: grid;
            gap: 6px;
            margin-top: auto;
        }

        .shop-card__price-row,
        .shop-card__bottom-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .shop-card__sale {
            color: #ea4b72;
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: 0.01em;
            font-family: var(--font-lato);
        }

        .shop-card__cost-wrap {
            display: grid;
            justify-items: end;
            gap: 6px;
        }

        .shop-card__save {
            min-height: 24px;
            padding: 0 8px;
            border: 1px solid #214f99;
            color: #214f99;
            background: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.62rem;
            font-weight: 700;
        }

        .shop-card__cost {
            color: #3f4a5a;
            font-size: 0.8rem;
            font-weight: 700;
            text-decoration: line-through;
        }

        .shop-card__favorite {
            flex: 0 0 auto;
            width: 38px;
            height: 38px;
            border: 1px solid #d8e2ef;
            background: #ffffff;
            color: #334155;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .shop-card__favorite.is-active {
            border-color: #173f88;
            background: #173f88;
            color: #ffffff;
        }

        .shop-card__installment {
            color: #374151;
            font-size: 0.72rem;
            line-height: 1.16;
            display: grid;
            gap: 2px;
            flex: 1 1 auto;
        }

        .shop-card__installment-line {
            white-space: nowrap;
        }

        .shop-card__installment strong {
            color: #214f99;
            font-weight: 400;
        }

        .shop-card__installment sup {
            top: -0.15em;
            font-size: 0.5rem;
        }

        .shop-card__actions {
            display: flex;
            max-height: 0;
            margin-top: 0;
            padding-top: 0;
            transform: translateY(8px);
            opacity: 0;
            transition: max-height 0.3s ease, margin-top 0.3s ease, padding-top 0.3s ease, transform 0.3s ease, opacity 0.3s ease;
            overflow: hidden;
        }

        .shop-card:hover .shop-card__actions {
            max-height: 38px;
            padding-top: 2px;
            transform: translateY(0);
            opacity: 1;
        }

        .shop-card__btn {
            width: 100%;
            min-height: 32px;
            border: 1px solid #173f88;
            background: #173f88;
            color: #ffffff;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
        }

        .shop-card__btn.is-added,
        .shop-card__btn:disabled {
            background: #e8eef8;
            border-color: #d4deeb;
            color: #173f88;
            cursor: pointer;
        }

        .shop-cart-rail {
            position: fixed;
            top: 46%;
            right: 20px;
            z-index: 1080;
            display: grid;
            gap: 8px;
            transform: translateY(-50%);
        }

        .shop-cart-rail__btn {
            position: relative;
            width: 46px;
            height: 46px;
            border: 1px solid #dce6f2;
            background: #ffffff;
            color: #334155;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        }

        .shop-cart-rail__count {
            position: absolute;
            top: -6px;
            right: -6px;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            background: #173f88;
            color: #ffffff;
            font-size: 0.68rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
        }

        .shop-cart-rail__count.is-hidden {
            display: none;
        }

        .shop-cart-rail__label {
            font-size: 0.56rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .shop-cart-backdrop {
            position: fixed;
            inset: 0;
            z-index: 1190;
            background: rgba(15, 23, 42, 0.35);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }

        .shop-cart-backdrop.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        .shop-cart-drawer {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            z-index: 1200;
            width: min(420px, 100vw);
            background: #ffffff;
            border-left: 1px solid #dbe6f1;
            box-shadow: -16px 0 36px rgba(15, 23, 42, 0.12);
            transform: translateX(100%);
            transition: transform 0.28s ease;
            display: grid;
            grid-template-rows: auto 1fr auto;
        }

        .shop-cart-drawer.is-open {
            transform: translateX(0);
        }

        .shop-favorite-drawer {
            top: 0;
            left: 0;
            right: auto;
            bottom: 0;
            width: min(1070px, 100vw);
            border-left: 0;
            border-right: 1px solid #dbe6f1;
            transform: translateX(-105%);
            box-shadow: 18px 0 40px rgba(15, 23, 42, 0.12);
        }

        .shop-favorite-drawer.is-open {
            transform: translateX(0);
        }

        .shop-cart-drawer__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 18px;
            border-bottom: 1px solid #e9eff7;
        }

        .shop-cart-drawer__title {
            margin: 0;
            color: #0f172a;
            font-size: 1.1rem;
            font-weight: 800;
        }

        .shop-cart-drawer__close {
            width: 38px;
            height: 38px;
            border: 1px solid #dce6f2;
            background: #ffffff;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .shop-cart-drawer__body {
            overflow-y: auto;
            padding: 14px 18px 16px;
            display: grid;
            align-content: start;
            gap: 12px;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .shop-cart-drawer__body::-webkit-scrollbar {
            width: 0;
            height: 0;
            display: none;
        }

        .shop-favorite-drawer .shop-cart-drawer__body {
            padding: 14px 14px 18px;
            display: block;
        }

        .shop-favorite-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .shop-cart-empty {
            min-height: 220px;
            display: grid;
            place-items: center;
            text-align: center;
            color: #64748b;
            font-size: 0.92rem;
            line-height: 1.8;
            border: 1px dashed #d8e3ef;
            background: #fbfdff;
            padding: 18px;
        }

        .shop-cart-item {
            display: grid;
            grid-template-columns: 78px minmax(0, 1fr);
            gap: 12px;
            padding: 12px;
            border: 1px solid #e3ebf4;
            background: #ffffff;
        }

        .shop-cart-item__media {
            width: 78px;
            height: 78px;
            border: 1px solid #edf2f8;
            background: #fbfdff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .shop-cart-item__media img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .shop-cart-item__name {
            margin: 0 0 6px;
            color: #1e293b;
            font-size: 0.92rem;
            font-weight: 800;
            line-height: 1.35;
        }

        .shop-cart-item__meta {
            color: #64748b;
            font-size: 0.72rem;
            line-height: 1.5;
        }

        .shop-cart-item__bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 10px;
        }

        .shop-cart-item__price {
            color: #ea4b72;
            font-size: 0.94rem;
            font-weight: 800;
        }

        .shop-cart-qty {
            display: inline-flex;
            align-items: center;
            border: 1px solid #dce6f2;
            background: #ffffff;
        }

        .shop-cart-qty button {
            width: 28px;
            height: 28px;
            border: 0;
            background: transparent;
            color: #334155;
            cursor: pointer;
            font-weight: 700;
        }

        .shop-cart-qty span {
            min-width: 34px;
            text-align: center;
            color: #17324d;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .shop-cart-remove {
            border: 0;
            background: transparent;
            color: #ef4444;
            cursor: pointer;
            font-size: 0.74rem;
            font-weight: 700;
            padding: 0;
        }

        .shop-cart-drawer__foot {
            border-top: 1px solid #e9eff7;
            padding: 16px 18px 18px;
            display: grid;
            gap: 10px;
            background: #ffffff;
        }

        .shop-cart-summary {
            display: grid;
            gap: 8px;
        }

        .shop-cart-summary__row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            color: #475569;
            font-size: 0.9rem;
        }

        .shop-cart-summary__row strong {
            color: #0f172a;
        }

        .shop-cart-checkout {
            width: 100%;
            min-height: 44px;
            border: 1px solid #173f88;
            background: #173f88;
            color: #ffffff;
            font-size: 0.92rem;
            font-weight: 800;
            cursor: pointer;
        }

        .shop-cart-note {
            color: #64748b;
            font-size: 0.72rem;
            line-height: 1.6;
            text-align: center;
        }

        @media (max-width: 1180px) {
            .shop-detail-shell {
                grid-template-columns: 1fr;
            }

            .shop-detail-primary {
                grid-template-columns: 1fr;
            }

            .shop-detail-gallery {
                border-right: 0;
                border-bottom: 1px solid #e9eff7;
            }

            .shop-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .shop-favorite-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 900px) {
            .shop-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .shop-favorite-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .shop-detail-page {
                width: min(100%, calc(100% - 18px));
            }

            .shop-detail-main {
                min-height: 280px;
            }

            .shop-detail-controls {
                grid-template-columns: 1fr;
            }

            .shop-detail-client-grid {
                grid-template-columns: 1fr;
            }

            .shop-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .shop-card {
                min-height: 306px;
            }

            .shop-card__title {
                min-height: 44px;
                font-size: 0.8rem;
            }

            .shop-card__media {
                height: 148px;
            }

            .shop-card:hover .shop-card__body {
                bottom: -28px;
                transform: translateY(-28px);
            }

            .shop-cart-drawer {
                width: min(100vw, 380px);
            }

            .shop-khqr-modal__dialog {
                width: min(355px, 100%);
                padding: 8px 0 8px;
            }

            .shop-khqr-modal__top {
                padding: 0 40px 2px 14px;
            }

            .shop-khqr-modal__brand-image {
                width: 138px;
            }

            .shop-khqr-modal__title {
                font-size: 1.02rem;
            }

        }

        @media (max-width: 560px) {
            .shop-grid,
            .shop-favorite-grid {
                grid-template-columns: 1fr;
            }

            .shop-detail-thumbs {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>

    <section class="shop-detail-page">
        <div class="shop-detail-shell">
            <div class="shop-detail-primary">
                <div class="shop-detail-gallery">
                    <div class="shop-detail-breadcrumbs">
                        <a href="{{ route('home') }}">{{ __('Home') }}</a>
                        /
                        <a href="{{ route('shop.index') }}">{{ __('Shop') }}</a>
                        /
                        <a href="{{ route('shop.index', ['category' => $product->category?->slug]) }}">{{ $product->category?->name ?: __('Category') }}</a>
                        /
                        <strong>{{ $product->name }}</strong>
                    </div>

                    <div class="shop-detail-main">
                        <button type="button" class="shop-detail-nav shop-detail-nav--prev" data-gallery-nav="prev" aria-label="{{ __('Previous image') }}">
                            <i class="fa-solid fa-arrow-left"></i>
                        </button>
                        <img
                            src="{{ $galleryItems->first() ?: $product->image_url }}"
                            alt="{{ $product->name }}"
                            data-detail-main-image
                        >
                        <button type="button" class="shop-detail-nav shop-detail-nav--next" data-gallery-nav="next" aria-label="{{ __('Next image') }}">
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>

                    <div class="shop-detail-dots">
                        @foreach ($galleryItems as $image)
                            <button type="button" class="shop-detail-dot {{ $loop->first ? 'is-active' : '' }}" data-gallery-dot="{{ $loop->index }}" aria-label="{{ __('Select image') }}"></button>
                        @endforeach
                    </div>

                    @if ($galleryItems->count() > 1)
                        <div class="shop-detail-thumbs">
                            @foreach ($galleryItems as $image)
                                <button type="button" class="shop-detail-thumb {{ $loop->first ? 'is-active' : '' }}" data-gallery-thumb="{{ $loop->index }}">
                                    <img src="{{ $image }}" alt="{{ $product->name }}">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="shop-detail-info">
                    <div class="shop-detail-heading">
                        <h1 class="shop-detail-title">{{ $product->name }}</h1>
                        <div class="shop-detail-category">{{ $product->category?->name ?: __('IT Product') }}</div>
                    </div>

                    <div class="shop-detail-status-row">
                        <span class="shop-detail-badge {{ $product->stock_qty > 0 ? 'is-stock' : 'is-out' }}">
                            {{ $product->stock_qty > 0 ? __('In Stock') : __('Out of Stock') }}
                        </span>
                    </div>

                    <div class="shop-detail-prices">
                        @if ($saveAmount > 0)
                            <span class="shop-detail-cost">${{ number_format($costPrice, 2) }}</span>
                        @endif
                        <span class="shop-detail-sale">${{ number_format($salePrice, 2) }}</span>
                        @if ($saveAmount > 0)
                            <span class="shop-detail-save">{{ __('Save') }} ${{ number_format($saveAmount, 2) }}</span>
                        @endif
                    </div>

                    @if (filled($product->description))
                        <p class="shop-detail-copy">{{ $product->description }}</p>
                    @endif

                    <div class="shop-detail-meta">
                        @if (filled($product->sku))
                            <div><strong>SKU:</strong> {{ $product->sku }}</div>
                        @endif
                        @if (filled($product->barcode))
                            <div><strong>{{ __('Barcode') }}:</strong> {{ $product->barcode }}</div>
                        @endif
                        <div><strong>{{ __('Qty') }}:</strong> {{ $product->stock_qty }}</div>
                    </div>

                    <div class="shop-detail-controls">
                        <select class="shop-detail-select">
                            <option>{{ __('Default Option') }}</option>
                            <option>{{ __('Standard Package') }}</option>
                            <option>{{ __('Developer Setup') }}</option>
                        </select>

                        <div class="shop-detail-qty">
                            <button type="button" data-detail-qty="minus" data-target="detail-qty" data-max-stock="{{ (int) $product->stock_qty }}">-</button>
                            <span data-qty-label="detail-qty">1</span>
                            <button type="button" data-detail-qty="plus" data-target="detail-qty" data-max-stock="{{ (int) $product->stock_qty }}">+</button>
                            <input type="hidden" id="detail-qty" value="1">
                        </div>
                    </div>

                    <div class="shop-detail-actions">
                        <a
                            href="{{ $galleryItems->first() ?: $product->image_url }}"
                            download
                            class="shop-detail-download"
                            data-detail-download
                        >
                            <i class="fa-solid fa-download"></i>
                            <span>{{ __('Download') }}</span>
                        </a>

                        <button
                            type="button"
                            class="shop-detail-add"
                            data-cart-add
                            data-id="{{ $product->id }}"
                            data-name="{{ e($product->name) }}"
                            data-category="{{ e($product->category?->name ?: '-') }}"
                            data-description="{{ e($product->description ?: '-') }}"
                            data-sku="{{ e($product->sku) }}"
                            data-barcode="{{ e($product->barcode ?: '-') }}"
                            data-stock="{{ e((string) $product->stock_qty) }}"
                            data-sale="${{ number_format((float) $product->sale_price, 2) }}"
                            data-cost="${{ number_format((float) $product->cost_price, 2) }}"
                            data-image="{{ $product->image_url ?: '' }}"
                            data-qty-source="detail-qty"
                        >
                            <i class="fa-solid fa-cart-shopping"></i>
                            <span>{{ __('Add to Cart') }}</span>
                        </button>
                    </div>

                    <div class="shop-detail-share">
                        <strong>{{ __('Share') }}</strong>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener noreferrer" class="shop-detail-share__facebook" aria-label="{{ __('Share on Facebook') }}">
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>
                        <a href="https://t.me/share/url?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareTitle) }}" target="_blank" rel="noopener noreferrer" class="shop-detail-share__telegram" aria-label="{{ __('Share on Telegram') }}">
                            <i class="fa-brands fa-telegram"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareTitle) }}" target="_blank" rel="noopener noreferrer" class="shop-detail-share__x" aria-label="{{ __('Share on X') }}">
                            <i class="fa-brands fa-twitter"></i>
                        </a>
                        <a href="mailto:?subject={{ rawurlencode($shareTitle) }}&body={{ rawurlencode($shareUrl) }}" class="shop-detail-share__email" aria-label="{{ __('Share by email') }}">
                            <i class="fa-solid fa-envelope"></i>
                        </a>
                        <a href="#" data-copy-share="{{ $shareUrl }}" class="shop-detail-share__copy" aria-label="{{ __('Copy link') }}">
                            <i class="fa-solid fa-link"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="shop-detail-side">
                <div class="shop-detail-payments">
                    @foreach ($paymentMethods as $method)
                        @if ($loop->first)
                            <button type="button" class="shop-detail-pay-card shop-detail-pay-card--button" data-shop-khqr-open>
                                <div class="shop-detail-pay-icon">
                                    <img src="{{ $method['image'] }}" alt="{{ $method['name'] }}">
                                </div>
                                <div>
                                    <div class="shop-detail-pay-name">{{ $method['name'] }}</div>
                                    <div class="shop-detail-pay-copy">{{ $method['copy'] }}</div>
                                    @if (!empty($method['logos']))
                                        <div class="shop-detail-pay-logos">
                                            @foreach ($method['logos'] as $logo)
                                                @if (($logo['type'] ?? '') === 'mastercard')
                                                    <span class="shop-detail-pay-logo shop-detail-pay-logo--mastercard" aria-label="Mastercard">
                                                        <span></span>
                                                        <span></span>
                                                    </span>
                                                @else
                                                    <span class="shop-detail-pay-logo">
                                                        <img src="{{ $logo['src'] }}" alt="{{ $logo['alt'] }}">
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <span class="shop-detail-pay-arrow">
                                    <i class="fa-solid fa-angle-right"></i>
                                </span>
                            </button>
                        @else
                            <div class="shop-detail-pay-card">
                                <div class="shop-detail-pay-icon">
                                    <img src="{{ $method['image'] }}" alt="{{ $method['name'] }}">
                                </div>
                                <div>
                                    <div class="shop-detail-pay-name">{{ $method['name'] }}</div>
                                    <div class="shop-detail-pay-copy">{{ $method['copy'] }}</div>
                                    @if (!empty($method['logos']))
                                        <div class="shop-detail-pay-logos">
                                            @foreach ($method['logos'] as $logo)
                                                @if (($logo['type'] ?? '') === 'mastercard')
                                                    <span class="shop-detail-pay-logo shop-detail-pay-logo--mastercard" aria-label="Mastercard">
                                                        <span></span>
                                                        <span></span>
                                                    </span>
                                                @else
                                                    <span class="shop-detail-pay-logo">
                                                        <img src="{{ $logo['src'] }}" alt="{{ $logo['alt'] }}">
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <span class="shop-detail-pay-arrow">
                                    <i class="fa-solid fa-angle-right"></i>
                                </span>
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="shop-detail-payments shop-detail-client">
                    <div class="shop-detail-client-card">
                        <div class="shop-detail-client-grid">
                            @foreach ($clientInfoItems as $item)
                                <div class="shop-detail-client-item">
                                    <div class="shop-detail-client-label">{{ $item['label'] }} :</div>
                                    <div class="shop-detail-client-value">{{ filled($item['value']) ? $item['value'] : '-' }}</div>
                                </div>
                            @endforeach
                        </div>

                        <div class="shop-detail-client-note">
                            {{ __('Please confirm your information is correct before continuing with payment or delivery.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="shop-related">
            <div class="shop-related__head">
                <h2>{{ __('Related Products') }}</h2>
                <p>{{ __('Products from the same category are shown here so users can continue browsing similar IT items.') }}</p>
            </div>

            @if ($relatedProducts->isNotEmpty())
                <div class="shop-grid">
                    @foreach ($relatedProducts as $relatedProduct)
                        @include('web.pages.shop.partials.product-card', ['product' => $relatedProduct])
                    @endforeach
                </div>
            @else
                <div class="shop-cart-empty">{{ __('No related products found in this category yet.') }}</div>
            @endif
        </div>
    </section>

    <div class="shop-khqr-modal" data-shop-khqr-modal hidden>
        <div class="shop-khqr-modal__backdrop" data-shop-khqr-close></div>

        <div class="shop-khqr-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="shop-khqr-title">
            <button type="button" class="shop-khqr-modal__close" data-shop-khqr-close aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="shop-khqr-modal__top">
                <h2 class="shop-khqr-modal__title" id="shop-khqr-title">ABA KHQR</h2>
                <img
                    src="https://payway.ababank.com/kh/assets/img/ABA_PAYWAY_logo.svg"
                    alt="ABA PayWay"
                    class="shop-khqr-modal__brand-image"
                >
            </div>

            <div class="shop-khqr-modal__card">
                <div class="shop-khqr-modal__qr">
                    <img src="{{ $shopKhqrPreviewUrl }}" alt="ABA KHQR">
                </div>
            </div>

            <p class="shop-khqr-modal__caption">{{ __('Scan with ABA Mobile, or other Mobile Banking App supporting KHQR') }}</p>
        </div>
    </div>

    @include('web.pages.shop.partials.tools')
@endsection

@include('web.pages.shop.partials.scripts')

@push('web_scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const images = @json($galleryItems->values());
            const mainImage = document.querySelector('[data-detail-main-image]');
            const downloadButton = document.querySelector('[data-detail-download]');
            if (mainImage && images.length) {
                let currentIndex = 0;

                const syncGallery = () => {
                    mainImage.src = images[currentIndex];

                    if (downloadButton) {
                        downloadButton.href = images[currentIndex];
                    }

                    document.querySelectorAll('[data-gallery-thumb]').forEach((button) => {
                        button.classList.toggle('is-active', Number(button.getAttribute('data-gallery-thumb')) === currentIndex);
                    });

                    document.querySelectorAll('[data-gallery-dot]').forEach((button) => {
                        button.classList.toggle('is-active', Number(button.getAttribute('data-gallery-dot')) === currentIndex);
                    });
                };

                document.querySelectorAll('[data-gallery-nav]').forEach((button) => {
                    button.addEventListener('click', () => {
                        currentIndex = button.getAttribute('data-gallery-nav') === 'next'
                            ? (currentIndex + 1) % images.length
                            : (currentIndex - 1 + images.length) % images.length;
                        syncGallery();
                    });
                });

                document.querySelectorAll('[data-gallery-thumb], [data-gallery-dot]').forEach((button) => {
                    button.addEventListener('click', () => {
                        currentIndex = Number(button.getAttribute('data-gallery-thumb') ?? button.getAttribute('data-gallery-dot') ?? 0);
                        syncGallery();
                    });
                });
            }

            const modal = document.querySelector('[data-shop-khqr-modal]');
            const openButton = document.querySelector('[data-shop-khqr-open]');
            const closeButtons = document.querySelectorAll('[data-shop-khqr-close]');

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
        });
    </script>
@endpush
