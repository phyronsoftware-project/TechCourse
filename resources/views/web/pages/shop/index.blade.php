@extends('web.layouts.app')

@section('title', __('Shop'))

@php
    $authUser = auth()->user();
    $shopProducts = $products instanceof \Illuminate\Contracts\Pagination\Paginator ? $products : collect();
    // Keep the shop filter labels localized without changing shared translations.
    $categoryPlaceholder = app()->getLocale() === 'km' ? 'ជ្រើសប្រភេទទំនិញ...' : 'Select categories...';
    $categoryHeading = app()->getLocale() === 'km' ? 'ស្វែងរកតាមប្រភេទ' : 'Browse by category';
    $categoryHint = app()->getLocale() === 'km' ? 'អាចជ្រើសបានច្រើនប្រភេទ' : 'Select multiple categories';
    $selectedProvince = $provinces->firstWhere('id', $authUser?->province_id);
    $shopCartKhqrCardId = 'shop-cart-khqr-card';
@endphp

@section('content')
    {{-- Load the searchable multi-select only on the shop page. --}}
    @vite('resources/js/shop-category.js')
    <style>
        /* Separate the shop filter card from the header. */
        .shop-page {
            width: min(1320px, calc(100% - 32px));
            margin: 0 auto;
            display: grid;
            gap: 18px;
            padding-top: 24px;
            padding-bottom: 42px;
        }

        .shop-hero {
            display: grid;
            gap: 16px;
            padding: 0;
        }

        .shop-hero__kicker {
            display: inline-flex;
            align-items: center;
            min-height: 34px;
            padding: 0 14px;
            border-radius: 999px;
            background: #eff6ff;
            color: #2563eb;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .shop-hero__title {
            margin: 8px 0 6px;
            color: #0f172a;
            font-size: clamp(1.9rem, 3vw, 2.65rem);
            line-height: 1.12;
            letter-spacing: -0.04em;
            font-weight: 850;
        }

        .shop-hero__copy {
            margin: 0;
            max-width: 660px;
            color: #60738c;
            font-size: 0.96rem;
            line-height: 1.8;
        }

        .shop-toolbar {
            display: grid;
            gap: 14px;
        }

        /* Frame the category filter as one compact shop toolbar. */
        .shop-search {
            display: grid;
            gap: 11px;
            padding: 16px 18px;
            border-radius: 20px;
            border: 1px solid #d6e5f8;
            background: linear-gradient(120deg, #ffffff, #f6faff);
            box-shadow: 0 10px 24px rgba(24, 75, 158, 0.05);
        }

        /* Give the filter a clear label and a small multi-select hint. */
        .shop-filter-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-width: 0;
        }

        .shop-filter-head__label {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: #163d78;
            font-size: 0.88rem;
            font-weight: 800;
        }

        .shop-filter-head__label i {
            display: grid;
            place-items: center;
            width: 27px;
            height: 27px;
            border-radius: 8px;
            background: #e7f0ff;
            color: #2167d8;
            font-size: 0.73rem;
        }

        .shop-filter-head__hint {
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Keep a submit control only when JavaScript is unavailable. */
        .shop-search > button {
            margin-top: 12px;
            min-height: 48px;
            border: 0;
            border-radius: 14px;
            background: linear-gradient(135deg, #1d8cff, #1570ef);
            color: #ffffff;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
        }

        /* Keep selected category tags clear and the dropdown easy to discover. */
        .shop-category-picker {
            position: relative;
            min-width: 0;
        }

        .shop-category-picker::after {
            content: '';
            position: absolute;
            top: 50%;
            right: 18px;
            width: 8px;
            height: 8px;
            border-right: 2px solid #5475a4;
            border-bottom: 2px solid #5475a4;
            transform: translateY(-70%) rotate(45deg);
            pointer-events: none;
        }

        .shop-category-picker > select {
            width: 100%;
            min-height: 52px;
            border: 1px solid #d5e2ef;
            border-radius: 12px;
        }

        .shop-category-picker .ts-control {
            min-height: 52px;
            padding: 7px 38px 7px 11px;
            border: 1px solid #cbdcf2;
            border-radius: 12px;
            background: #ffffff;
            color: #0f172a;
            font-size: 0.88rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.025);
        }

        .shop-category-picker .ts-wrapper.focus .ts-control {
            border-color: #8ab7eb;
            box-shadow: 0 0 0 3px rgba(29, 140, 255, 0.12);
        }

        .shop-category-picker .ts-wrapper.multi .ts-control > .item {
            margin: 2px 5px 2px 0;
            padding: 4px 9px;
            border: 1px solid #bdd3f6;
            border-radius: 999px;
            background: #e8f1ff;
            color: #194a91;
            font-size: 0.82rem;
            font-weight: 750;
        }

        .shop-category-picker .ts-wrapper.multi .ts-control > .item .remove {
            margin-left: 7px;
            border-left-color: #b6cdf1;
            color: #194a91;
        }

        .shop-category-picker .ts-wrapper.multi .ts-control > .item .remove:hover {
            background: #d5e7ff;
            color: #123b77;
        }

        .shop-category-picker .ts-control > input {
            width: auto;
            min-height: 0;
            padding: 0;
            border: 0;
            background: transparent;
            box-shadow: none;
        }

        .shop-category-picker .ts-dropdown {
            z-index: 20;
            overflow: hidden;
            margin-top: 6px;
            padding: 5px;
            border: 1px solid #d2e0f3;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 16px 32px rgba(15, 42, 86, 0.14);
        }

        .shop-category-picker .ts-dropdown .option {
            margin: 2px 0;
            padding: 9px 11px;
            border-radius: 8px;
            color: #24344e;
            font-weight: 650;
        }

        .shop-category-picker .ts-dropdown .active {
            background: #eaf2ff;
            color: #184b9e;
        }

        html[data-web-theme='dark'] .shop-category-picker .ts-wrapper.multi .ts-control > .item {
            border-color: #365b8a;
            background: #19375e;
            color: #ffffff;
        }

        html[data-web-theme='dark'] .shop-category-picker .ts-control {
            border-color: #26313a;
            background: #0e1113;
            color: #ffffff;
        }

        html[data-web-theme='dark'] .shop-category-picker .ts-dropdown {
            border-color: #26313a;
            background: #0e1113;
            color: #ffffff;
        }

        html[data-web-theme='dark'] .shop-filter-head__label i,
        html[data-web-theme='dark'] .shop-category-picker .ts-dropdown .active {
            background: #19375e;
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
            border-radius: 0;
            background: #ffffff;
            border: 1px solid #dde4ee;
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
            transition: none;
        }

        .shop-card__warranty {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 48px;
            height: 48px;
            padding: 0;
            background: #173f87;
            color: #1c2e64;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.52rem;
            font-weight: 800;
            line-height: 1;
            text-transform: uppercase;
            text-align: center;
            white-space: nowrap;
            clip-path: polygon(50% 0%, 60% 12%, 71% 3%, 78% 16%, 91% 9%, 88% 25%, 100% 25%, 92% 38%, 100% 50%, 92% 62%, 100% 75%, 88% 75%, 91% 91%, 78% 84%, 71% 97%, 60% 88%, 50% 100%, 40% 88%, 29% 97%, 22% 84%, 9% 91%, 12% 75%, 0% 75%, 8% 62%, 0% 50%, 8% 38%, 0% 25%, 12% 25%, 9% 9%, 22% 16%, 29% 3%, 40% 12%);
            z-index: 2;
        }

        .shop-card__warranty::before {
            content: "";
            position: absolute;
            inset: 7px;
            border-radius: 50%;
            background: #ffdf07;
            z-index: 1;
        }

        .shop-card__warranty > span {
            position: relative;
            z-index: 2;
        }


        .shop-card__body {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            min-height: 136px;
            padding: 10px 12px 9px;
            border-radius: 0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.92) 0%, #ffffff 10%, #ffffff 100%);
            z-index: 5;
            transition: transform 0.32s ease, bottom 0.32s ease;
        }

        .shop-card:hover .shop-card__body {
            bottom: -34px;
            transform: translateY(-34px);
        }

        .shop-card__category {
            min-height: 10px;
            color: #6a7a90;
            font-size: 0.66rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            text-align: center;
        }

        .shop-card__title {
            margin: 0 0 3px;
            min-height: 28px;
            color: #364152;
            font-size: 0.84rem;
            line-height: 1.08;
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
            min-height: 20px;
            margin-bottom: 2px;
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
            gap: 3px;
            margin-top: 0;
        }

        .shop-card__price-row {
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

        .shop-card__bottom-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
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
            transition: background 0.22s ease, color 0.22s ease, border-color 0.22s ease, transform 0.22s ease;
        }

        .shop-card__favorite:hover {
            border-color: #173f88;
            color: #173f88;
            transform: translateY(-1px);
        }

        .shop-card__favorite.is-active {
            border-color: #173f88;
            background: #173f88;
            color: #ffffff;
        }

        .shop-card__installment {
            color: #374151;
            min-height: auto;
            font-size: 0.72rem;
            line-height: 1.16;
            display: grid;
            gap: 2px;
            flex: 1 1 auto;
        }

        .shop-card__installment-line {
            white-space: nowrap;
        }

        .shop-card__fee-description {
            display: block;
            max-width: 150px;
            overflow: hidden;
            color: #718096;
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
            margin-top: 0;
            padding-top: 2px;
            transform: translateY(0);
            opacity: 1;
        }

        .shop-card__btn {
            width: 100%;
            min-height: 32px;
            border-radius: 0;
            border: 1px solid #173f88;
            background: #173f88;
            color: #ffffff;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.22s ease, color 0.22s ease, border-color 0.22s ease;
        }

        .shop-card__btn:hover {
            background: #173f88;
            border-color: #173f88;
            color: #ffffff;
        }

        .shop-card__btn.is-added,
        .shop-card__btn:disabled {
            background: #e8eef8;
            border-color: #d4deeb;
            color: #173f88;
            cursor: pointer;
        }

        .shop-empty {
            padding: 26px;
            border-radius: 24px;
            border: 1px dashed #d7e3f0;
            background: #ffffff;
            color: #64748b;
            text-align: center;
            font-size: 0.92rem;
            line-height: 1.8;
        }

        .web-pagination-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .web-pagination-pages {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .web-page-btn {
            min-width: 38px;
            height: 38px;
            padding: 0 10px;
            border-radius: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f6f8ff;
            border: 1px solid #edf1fb;
            color: #334155;
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 700;
            line-height: 1;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04);
            transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
        }

        .web-page-btn:hover {
            background: #eef4ff;
            border-color: #d8e3f5;
            color: #173f88;
            transform: translateY(-1px);
        }

        .web-page-btn.is-active {
            background: linear-gradient(135deg, #5a5bd6 0%, #4547d8 100%);
            border-color: #4547d8;
            color: #ffffff;
            box-shadow: 0 14px 26px rgba(87, 88, 220, 0.22);
        }

        .web-page-btn.is-muted,
        .web-page-btn.is-disabled {
            background: #f8faff;
            border-color: #edf1fb;
            color: #9aa8bf;
        }

        .shop-modal {
            position: fixed;
            inset: 0;
            z-index: 1100;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background: rgba(15, 23, 42, 0.5);
        }

        .shop-modal.is-open {
            display: flex;
        }

        .shop-modal__dialog {
            width: min(980px, 100%);
            max-height: min(88vh, 860px);
            overflow: auto;
            border-radius: 28px;
            border: 1px solid #dbe6f1;
            background: #ffffff;
            box-shadow: 0 30px 60px rgba(15, 23, 42, 0.18);
            padding: 20px;
        }

        .shop-modal__close {
            margin-left: auto;
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 14px;
            background: #f4f8fd;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .shop-modal__body {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(320px, 0.95fr);
            gap: 20px;
            margin-top: 10px;
        }

        .shop-gallery-main {
            height: 420px;
            border-radius: 22px;
            overflow: hidden;
            border: 1px solid #e2eaf3;
            background: #f8fbff;
        }

        .shop-gallery-main img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .shop-gallery-thumbs {
            margin-top: 12px;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .shop-gallery-thumb {
            height: 84px;
            border-radius: 16px;
            border: 1px solid #dbe6f1;
            overflow: hidden;
            cursor: pointer;
            background: #ffffff;
            padding: 0;
        }

        .shop-gallery-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .shop-modal__info {
            display: grid;
            gap: 12px;
            align-content: start;
        }

        .shop-modal__title {
            margin: 0;
            color: #0f172a;
            font-size: 1.55rem;
            font-family: var(--font-lato);
            line-height: 1.2;
        }

        .shop-modal__copy,
        .shop-modal__specs li {
            color: #64748b;
            font-size: 0.92rem;
            line-height: 1.8;
        }

        .shop-modal__specs {
            margin: 0;
            padding-left: 18px;
            display: grid;
            gap: 6px;
        }

        .shop-modal__meta {
            display: grid;
            gap: 10px;
            padding: 16px;
            border-radius: 20px;
            border: 1px solid #e1eaf3;
            background: #f8fbff;
        }

        .shop-modal__row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            font-size: 0.88rem;
        }

        .shop-modal__row strong {
            color: #0f172a;
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

        body.menu-open .shop-cart-rail {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translateY(-50%) translateX(24px);
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
            transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }

        .shop-cart-rail__btn:hover {
            background: #f8fbff;
            border-color: #c8d7eb;
            color: #173f88;
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
            padding: 18px 18px 16px;
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

        .shop-favorite-card {
            display: flex;
            flex-direction: column;
            min-height: 280px;
            border: 1px solid #e3ebf4;
            background: #ffffff;
            overflow: hidden;
        }

        .shop-favorite-card__media {
            height: 154px;
            padding: 14px;
            border-bottom: 1px solid #eef3f8;
            background: linear-gradient(180deg, #fbfdff 0%, #ffffff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .shop-favorite-card__media img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .shop-favorite-card__body {
            display: grid;
            gap: 10px;
            padding: 14px;
        }

        .shop-favorite-card__category {
            color: #6a7a90;
            font-size: 0.64rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .shop-favorite-card__name {
            margin: 0;
            min-height: 42px;
            color: #1e293b;
            font-size: 0.94rem;
            line-height: 1.25;
            font-weight: 800;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .shop-favorite-card__meta {
            color: #64748b;
            font-size: 0.72rem;
            line-height: 1.5;
        }

        .shop-favorite-card__price {
            color: #ea4b72;
            font-size: 0.96rem;
            font-weight: 800;
        }

        .shop-favorite-card__actions {
            margin-top: auto;
            display: flex;
            justify-content: flex-end;
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

        @media (max-width: 1080px) {
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

            .shop-modal__body {
                grid-template-columns: 1fr;
            }

            .shop-cart-rail {
                right: 10px;
            }

            .shop-favorite-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .shop-page {
                width: min(100%, calc(100% - 8px));
                gap: 12px;
            }

            .shop-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }

            .shop-card {
                min-height: 286px;
            }

            .shop-card__title {
                min-height: 36px;
                font-size: 0.76rem;
            }

            .shop-card__media {
                height: 128px;
            }

            .shop-card:hover .shop-card__body {
                bottom: -28px;
                transform: translateY(-28px);
            }

            .shop-card__body {
                padding: 12px 9px 10px;
            }

            .shop-card__category {
                margin-bottom: 4px;
                font-size: 0.52rem;
            }

            .shop-card__meta,
            .shop-card__bottom-row,
            .shop-card__price-row {
                gap: 8px;
            }

            .shop-card__sale {
                font-size: 0.7rem;
            }

            .shop-card__cost,
            .shop-card__save,
            .shop-card__installment {
                font-size: 0.58rem;
            }

            .shop-card__favorite {
                width: 38px;
                height: 38px;
            }

            .shop-card__btn {
                min-height: 38px;
                font-size: 0.74rem;
            }

            .shop-search {
                padding: 12px;
            }

            .shop-gallery-main {
                height: 280px;
            }

            .shop-cart-drawer {
                width: min(100vw, 380px);
            }

            .shop-favorite-drawer {
                top: 0;
                bottom: 0;
                width: min(100vw, 1070px);
            }

            .shop-favorite-drawer.is-open {
                transform: translateX(0);
            }
        }

        @media (max-width: 560px) {
            .shop-page {
                width: min(100%, calc(100% - 6px));
            }

            /* Keep the filter heading concise on narrow screens. */
            .shop-filter-head__hint {
                display: none;
            }

            .shop-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
            }

            .shop-favorite-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
            }

            .shop-card {
                min-height: 278px;
            }

            .shop-search {
                padding: 10px;
                border-radius: 18px;
            }

            .shop-search > button {
                min-height: 42px;
            }

            .shop-card__body {
                padding: 11px 8px 9px;
            }

            .shop-card__title {
                min-height: auto;
                padding-bottom: 4px;
            }

            .shop-gallery-thumbs {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>

    <section class="shop-page">
        <div class="shop-toolbar">
            {{-- Category changes filter products automatically; the dropdown provides its own search. --}}
            <form action="{{ route('shop.index') }}" method="GET" class="shop-search">
                <div class="shop-filter-head">
                    <span class="shop-filter-head__label"><i class="fa-solid fa-filter" aria-hidden="true"></i>{{ $categoryHeading }}</span>
                    <span class="shop-filter-head__hint">{{ $categoryHint }}</span>
                </div>
                <div class="shop-category-picker">
                    <select name="category[]" multiple data-shop-category-select data-placeholder="{{ $categoryPlaceholder }}" data-remove-label="{{ app()->getLocale() === 'km' ? 'ដកប្រភេទទំនិញចេញ' : 'Remove category' }}" aria-label="{{ __('Category') }}">
                        @foreach ($categories as $category)
                            <option value="{{ $category->slug }}" @selected(in_array($category->slug, $activeCategories, true) || in_array($category->name, $activeCategories, true))>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <noscript><button type="submit">{{ __('Search') }}</button></noscript>
            </form>
        </div>

        <div data-shop-results>
            @if (! $shopReady)
                <div class="shop-empty">{{ __('Shop tables are not ready yet. Please run the SQL or migration first, then insert category, product, and product image data.') }}</div>
            @elseif ($shopProducts instanceof \Illuminate\Contracts\Pagination\Paginator && $shopProducts->count() > 0)
                <div class="shop-grid">
                    @foreach ($shopProducts as $product)
                        @include('web.pages.shop.partials.product-card', ['product' => $product])
                    @endforeach
                </div>

                {{ $shopProducts->links('vendor.pagination.web') }}
            @else
                <div class="shop-empty">{{ __('No shop products found yet. Add IT accessory product rows and they will appear here.') }}</div>
            @endif
        </div>
    </section>

    @include('web.pages.shop.partials.tools')

    <style>
        .shop-cart-checkout-modal[hidden] {
            display: none;
        }

        .shop-cart-checkout-modal {
            position: fixed;
            inset: 0;
            z-index: 1450;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, 0.72);
            opacity: 0;
            transition: opacity 180ms ease;
        }

        .shop-cart-checkout-modal.is-open {
            opacity: 1;
        }

        .shop-cart-checkout-dialog {
            display: grid;
            gap: 10px;
            width: min(360px, calc(100vw - 28px));
            /* Keep the cart checkout popup fully visible without showing an outer scrollbar. */
            max-height: none;
            overflow: visible;
            padding: 0;
            border: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
            transform: translateY(12px);
            transition: transform 180ms ease;
        }

        .shop-cart-checkout-modal.is-open .shop-cart-checkout-dialog {
            transform: translateY(0);
        }

        .shop-cart-checkout-message {
            width: min(320px, 100%);
            margin: 2px auto 0;
            color: #fff;
            text-align: center;
            font-size: 0.72rem;
            line-height: 1.6;
        }

        .shop-cart-checkout-close {
            display: none;
        }

        .shop-cart-checkout-items {
            display: grid;
            gap: 8px;
            max-height: 150px;
            overflow-y: auto;
            padding: 0 0 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        .shop-cart-checkout-item {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            color: #334155;
            font-size: 0.82rem;
        }

        .shop-cart-checkout-item strong:last-child {
            white-space: nowrap;
            color: #173f87;
        }

        .shop-cart-checkout-summary {
            display: grid;
            gap: 7px;
            padding: 12px 14px;
            border-radius: 14px;
            background: #fff;
            border: 1px solid #dbe5f0;
        }

        .shop-cart-checkout-summary__row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            color: #52657f;
            font-size: 0.84rem;
        }

        .shop-cart-checkout-summary__total {
            padding-top: 8px;
            border-top: 1px dashed #cbd5e1;
            color: #173f87;
            font-size: 1rem;
            font-weight: 850;
        }

        .shop-cart-checkout-status {
            margin: 0;
            text-align: center;
            color: #fff;
            font-size: 0.8rem;
        }

        /* Show a clear thank-you message after the cart payment is confirmed. */
        .shop-cart-success-modal[hidden] {
            display: none;
        }

        .shop-cart-success-modal {
            position: fixed;
            inset: 0;
            z-index: 1500;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, 0.56);
            opacity: 0;
            transition: opacity 300ms ease;
        }

        .shop-cart-success-modal.is-open {
            opacity: 1;
        }

        .shop-cart-success-card {
            position: relative;
            width: min(480px, calc(100vw - 32px));
            padding: 44px 38px 36px;
            overflow: hidden;
            border-radius: 24px;
            background: #fff;
            color: #0f1f3d;
            text-align: center;
            box-shadow: 0 24px 70px rgba(15, 31, 61, 0.24);
            transform: translateY(18px) scale(0.97);
            transition: transform 300ms cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        .shop-cart-success-modal.is-open .shop-cart-success-card {
            transform: translateY(0) scale(1);
        }

        .shop-cart-success-close {
            position: absolute;
            top: 15px;
            right: 18px;
            width: 38px;
            height: 38px;
            padding: 0;
            border: 0;
            background: transparent;
            color: #9ca3af;
            font-size: 2rem;
            font-weight: 300;
            line-height: 1;
            cursor: pointer;
        }

        .shop-cart-success-icon {
            position: relative;
            width: 132px;
            height: 132px;
            margin: 8px auto 24px;
            display: grid;
            place-items: center;
            border: 20px solid rgba(49, 205, 76, 0.14);
            border-radius: 50%;
            background: linear-gradient(180deg, #55d415 0%, #00bc45 100%);
            box-sizing: border-box;
            box-shadow: 0 0 0 1px rgba(73, 211, 94, 0.08);
        }

        .shop-cart-success-icon::before,
        .shop-cart-success-icon::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            background: rgba(85, 212, 21, 0.18);
        }

        .shop-cart-success-icon::before {
            width: 13px;
            height: 13px;
            left: -48px;
            top: 35px;
            box-shadow: 30px -51px 0 -3px rgba(85, 212, 21, 0.16);
        }

        .shop-cart-success-icon::after {
            width: 8px;
            height: 8px;
            right: -34px;
            top: -2px;
            box-shadow: 18px 33px 0 -2px rgba(85, 212, 21, 0.16);
        }

        .shop-cart-success-icon svg {
            width: 64px;
            height: 64px;
            color: #fff;
        }

        .shop-cart-success-title {
            margin: 0;
            color: #0f1f3d;
            font-size: clamp(2rem, 7vw, 2.65rem);
            font-weight: 900;
            line-height: 1.1;
        }

        .shop-cart-success-text {
            max-width: 340px;
            margin: 22px auto 34px;
            color: #74748d;
            font-size: 1.12rem;
            line-height: 1.5;
        }

        .shop-cart-success-done {
            width: 100%;
            min-height: 64px;
            border: 0;
            border-radius: 12px;
            background: linear-gradient(100deg, #5d95f5 0%, #2467f4 100%);
            color: #fff;
            font-size: 1.65rem;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 10px 24px rgba(36, 103, 244, 0.2);
        }

        @media (max-width: 520px) {
            .shop-cart-success-card {
                padding: 38px 24px 26px;
                border-radius: 20px;
            }

            .shop-cart-success-icon {
                width: 116px;
                height: 116px;
                border-width: 17px;
            }

            .shop-cart-success-text {
                margin: 18px auto 28px;
                font-size: 1rem;
            }

            .shop-cart-success-done {
                min-height: 56px;
                font-size: 1.35rem;
            }
        }
    </style>

    <div class="shop-cart-checkout-modal" data-cart-checkout-modal hidden>
        <div class="shop-cart-checkout-dialog" role="dialog" aria-modal="true" aria-labelledby="shop-cart-checkout-title">
            <button type="button" class="shop-cart-checkout-close" data-cart-checkout-close aria-label="{{ __('Close') }}">&times;</button>

            @include('components.khqr-card', [
                'cardId' => $shopCartKhqrCardId,
                'merchantName' => config('bakong.merchant_name') ?: 'TechCourse',
                'amount' => 0,
                'currency' => 'USD',
                'khqrString' => null,
                'qrImageUrl' => null,
                'expiredAt' => null,
                'status' => 'pending',
                'showStatusMeta' => true,
                'showCenterBadge' => true,
                'emptyMessage' => __('The cart KHQR will appear here after checkout starts.'),
            ])

            <p class="shop-cart-checkout-message">
                {{ __('Note: This website is for testing only. If you make a payment, I will not be responsible for any loss.') }}
            </p>

            <div class="shop-cart-checkout-summary">
                <div class="shop-cart-checkout-items" data-cart-checkout-items></div>
                <div class="shop-cart-checkout-summary__row"><span>{{ __('Items') }}</span><strong data-cart-checkout-qty>0</strong></div>
                <div class="shop-cart-checkout-summary__row"><span>{{ __('Subtotal') }}</span><strong data-cart-checkout-subtotal>$0.00</strong></div>
                <div class="shop-cart-checkout-summary__row"><span>{{ __('Delivery Fee') }}</span><strong data-cart-checkout-delivery>${{ number_format((float) ($selectedProvince?->delivery_fee ?? 0), 2) }}</strong></div>
                <div class="shop-cart-checkout-summary__row shop-cart-checkout-summary__total"><span>{{ __('Total') }}</span><strong data-cart-checkout-total>$0.00 USD</strong></div>
            </div>

            <p class="shop-cart-checkout-status" data-cart-checkout-status>{{ __('Preparing cart checkout...') }}</p>
        </div>
    </div>

    {{-- Confirm the completed cart payment with the requested success popup. --}}
    <div class="shop-cart-success-modal" data-cart-success-modal hidden>
        <div class="shop-cart-success-card" role="dialog" aria-modal="true" aria-labelledby="shop-cart-success-title">
            <button type="button" class="shop-cart-success-close" data-cart-success-close aria-label="{{ __('Close') }}">&times;</button>
            <div class="shop-cart-success-icon" aria-hidden="true">
                <svg viewBox="0 0 64 64" fill="none">
                    <path d="M15 32.5L27 44.5L50 19.5" stroke="currentColor" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h2 class="shop-cart-success-title" id="shop-cart-success-title">{{ __('Success!') }}</h2>
            <p class="shop-cart-success-text">
                {{ __('Your payment was completed successfully. Thank you for your purchase!') }}
            </p>
            <button type="button" class="shop-cart-success-done" data-cart-success-close>{{ __('Done') }}</button>
        </div>
    </div>

    <div class="shop-modal" data-shop-modal aria-hidden="true">
        <div class="shop-modal__dialog">
            <button type="button" class="shop-modal__close" data-shop-close aria-label="{{ __('Close') }}">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="shop-modal__body">
                <div>
                    <div class="shop-gallery-main">
                        <img src="" alt="" data-shop-main-image>
                    </div>
                    <div class="shop-gallery-thumbs" data-shop-thumbs></div>
                </div>

                <div class="shop-modal__info">
                    <div class="shop-card__category" data-shop-category></div>
                    <h2 class="shop-modal__title" data-shop-name></h2>
                    <div class="shop-card__prices">
                        <span class="shop-card__sale" data-shop-sale></span>
                        <span class="shop-card__cost" data-shop-cost></span>
                    </div>
                    <p class="shop-modal__copy" data-shop-description></p>

                    <div class="shop-modal__meta">
                        <div class="shop-modal__row">
                            <span>{{ __('Stock') }}</span>
                            <strong data-shop-stock></strong>
                        </div>
                        <div class="shop-modal__row">
                            <span>SKU</span>
                            <strong data-shop-sku></strong>
                        </div>
                        <div class="shop-modal__row">
                            <span>{{ __('Barcode') }}</span>
                            <strong data-shop-barcode></strong>
                        </div>
                    </div>

                    <ul class="shop-modal__specs">
                        <li>{{ __('Suitable for IT learners, office setup, and developer workstations.') }}</li>
                        <li>{{ __('Use profile address and phone information for delivery coordination later.') }}</li>
                        <li>{{ __('Multiple product images can be shown in this gallery for better customer view.') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const checkoutModal = document.querySelector('[data-cart-checkout-modal]');
            const checkoutItems = document.querySelector('[data-cart-checkout-items]');
            const checkoutStatus = document.querySelector('[data-cart-checkout-status]');
            const checkoutQty = document.querySelector('[data-cart-checkout-qty]');
            const checkoutSubtotal = document.querySelector('[data-cart-checkout-subtotal]');
            const checkoutDelivery = document.querySelector('[data-cart-checkout-delivery]');
            const checkoutTotal = document.querySelector('[data-cart-checkout-total]');
            const closeButton = document.querySelector('[data-cart-checkout-close]');
            const successModal = document.querySelector('[data-cart-success-modal]');
            const successCloseButtons = document.querySelectorAll('[data-cart-success-close]');
            const cardId = @json($shopCartKhqrCardId);
            const createUrl = @json(auth()->check() ? route('shop-payments.cart.bakong.create') : null);
            const statusBaseUrl = @json(url('/shop-payments'));
            const csrfToken = @json(csrf_token());
            const deliveryReady = @json((bool) $selectedProvince);
            let pollTimer = null;
            let pollAttempt = 0;
            let paymentStatusUrl = null;

            if (!checkoutModal) {
                return;
            }

            const formatMoney = (value) => `$${Number(value || 0).toFixed(2)}`;
            const lockPage = () => window.TechCourseScrollLock?.lock?.() ?? (document.body.style.overflow = 'hidden');
            const unlockPage = () => window.TechCourseScrollLock?.unlock?.() ?? (document.body.style.overflow = '');

            // Close checkout normally or keep the page locked while switching popups.
            const closeCheckout = (immediate = false, keepPageLocked = false) => {
                if (pollTimer) {
                    window.clearTimeout(pollTimer);
                    pollTimer = null;
                }

                pollAttempt = 0;
                paymentStatusUrl = null;
                checkoutModal.classList.remove('is-open');

                if (immediate) {
                    checkoutModal.hidden = true;
                    if (!keepPageLocked) {
                        unlockPage();
                    }
                    return;
                }

                window.setTimeout(() => {
                    checkoutModal.hidden = true;
                    unlockPage();
                }, 180);
            };

            // Replace the checkout popup with the thank-you popup after payment.
            const showCartSuccess = () => {
                if (!successModal) {
                    unlockPage();
                    return;
                }

                successModal.hidden = false;
                requestAnimationFrame(() => successModal.classList.add('is-open'));
            };

            const closeCartSuccess = () => {
                if (!successModal || successModal.hidden) {
                    return;
                }

                successModal.classList.remove('is-open');
                window.setTimeout(() => {
                    successModal.hidden = true;
                    unlockPage();
                }, 300);
            };

            const renderCheckout = (items) => {
                const safeItems = Array.isArray(items) ? items : [];
                const totalQty = safeItems.reduce((sum, item) => sum + Number(item.qty || 0), 0);
                const subtotal = safeItems.reduce((sum, item) => sum + (Number(item.qty || 0) * Number(item.salePrice || 0)), 0);
                const delivery = {{ (float) ($selectedProvince?->delivery_fee ?? 0) }};

                checkoutItems.innerHTML = safeItems.map((item) => `
                    <div class="shop-cart-checkout-item">
                        <span>${item.name || 'Product'} x ${Number(item.qty || 1)}</span>
                        <strong>${formatMoney(Number(item.qty || 1) * Number(item.salePrice || 0))}</strong>
                    </div>
                `).join('');
                checkoutQty.textContent = String(totalQty);
                checkoutSubtotal.textContent = formatMoney(subtotal);
                checkoutDelivery.textContent = formatMoney(delivery);
                checkoutTotal.textContent = `${formatMoney(subtotal + delivery)} USD`;
            };

            const updateCheckoutFromServer = (data) => {
                checkoutQty.textContent = String(data.quantity || 0);
                checkoutSubtotal.textContent = formatMoney(data.subtotal);
                checkoutDelivery.textContent = formatMoney(data.delivery_fee);
                checkoutTotal.textContent = `${formatMoney(data.amount)} ${data.currency || 'USD'}`;
            };

            const scheduleStatusCheck = (delay = 3000) => {
                if (!paymentStatusUrl || pollTimer) {
                    return;
                }

                pollTimer = window.setTimeout(async () => {
                    pollTimer = null;
                    await checkStatus();
                }, delay);
            };

            async function checkStatus() {
                if (!paymentStatusUrl) {
                    return;
                }

                try {
                    const response = await fetch(paymentStatusUrl, {
                        headers: { Accept: 'application/json' },
                        credentials: 'same-origin',
                    });
                    const result = await response.json();
                    const status = result?.data?.status;

                    if (status === 'success') {
                        // Hide cart checkout immediately, then show the payment success message.
                        window.dispatchEvent(new Event('shop:payment-success'));
                        closeCheckout(true, true);
                        showCartSuccess();
                        return;
                    }

                    if (['failed', 'cancelled', 'expired'].includes(status)) {
                        checkoutStatus.textContent = `Payment ${status}. Please checkout again.`;
                        return;
                    }

                    checkoutStatus.textContent = 'Waiting for payment confirmation...';
                    // Back off status checks so one checkout does not exhaust the Bakong daily quota.
                    const nextDelay = Math.min(5000 * (2 ** Math.min(pollAttempt, 4)), 60000);
                    pollAttempt += 1;
                    scheduleStatusCheck(nextDelay);
                } catch (error) {
                    checkoutStatus.textContent = 'Checking payment status again...';
                    pollAttempt += 1;
                    scheduleStatusCheck(60000);
                }
            }

            const startCheckout = async (items) => {
                if (!deliveryReady) {
                    window.location.href = @json(route('profile.show'));
                    return;
                }

                if (!createUrl || !items?.length) {
                    return;
                }

                renderCheckout(items);
                // Start each cart payment with a fresh polling backoff sequence.
                pollAttempt = 0;
                checkoutModal.hidden = false;
                lockPage();
                requestAnimationFrame(() => checkoutModal.classList.add('is-open'));
                checkoutStatus.textContent = 'Creating one KHQR for all cart products...';
                window.TechCoursePageLoader?.show?.();

                try {
                    const response = await fetch(createUrl, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({}),
                    });
                    const result = await response.json();

                    if (!response.ok || !result?.data?.payment_id) {
                        throw new Error(result?.message || 'Unable to create cart payment.');
                    }

                    updateCheckoutFromServer(result.data);
                    window.TechCourseKhqrCards?.setQr(cardId, result.data.khqr_string, result.data.expired_at);
                    window.TechCourseKhqrCards?.setAmount(cardId, result.data.amount, result.data.currency);
                    paymentStatusUrl = `${statusBaseUrl}/${result.data.payment_id}/bakong-status`;
                    checkoutStatus.textContent = 'Scan this one KHQR to pay for all products.';
                    scheduleStatusCheck(3000);
                } catch (error) {
                    checkoutStatus.textContent = error.message || 'Unable to create cart payment.';
                    window.TechCourseKhqrCards?.showToast(
                        error.message || 'Unable to create cart payment.',
                        'error',
                    );
                } finally {
                    window.TechCoursePageLoader?.hide?.();
                }
            };

            window.addEventListener('shop:cart-checkout', (event) => startCheckout(event.detail?.items || []));
            closeButton?.addEventListener('click', () => closeCheckout());
            successCloseButtons.forEach((button) => button.addEventListener('click', closeCartSuccess));
            checkoutModal.addEventListener('click', (event) => {
                if (event.target === checkoutModal) {
                    closeCheckout();
                }
            });
            successModal?.addEventListener('click', (event) => {
                if (event.target === successModal) {
                    closeCartSuccess();
                }
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && successModal && !successModal.hidden) {
                    closeCartSuccess();
                    return;
                }

                if (event.key === 'Escape' && !checkoutModal.hidden) {
                    closeCheckout();
                }
            });
        });
    </script>
@endsection

@include('web.pages.shop.partials.scripts')
