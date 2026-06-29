@extends('web.layouts.app')

@section('title', __('Shop'))

@php
    $shopProducts = $products instanceof \Illuminate\Contracts\Pagination\Paginator ? $products : collect();
    $activeCategoryLabel = $categories->firstWhere('slug', $activeCategory)?->name
        ?? $categories->firstWhere('name', $activeCategory)?->name
        ?? __('All');
@endphp

@section('content')
    <style>
        .shop-page {
            width: min(1320px, calc(100% - 32px));
            margin: 0 auto;
            display: grid;
            gap: 18px;
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

        .shop-search {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            padding: 18px;
            border-radius: 24px;
            border: 1px solid #dce7f3;
            background: #ffffff;
            box-shadow: 0 12px 26px rgba(15, 23, 42, 0.04);
        }

        .shop-search input {
            width: 100%;
            min-height: 48px;
            border-radius: 14px;
            border: 1px solid #d5e2ef;
            background: #ffffff;
            padding: 0 15px;
            font-size: 0.92rem;
            color: #0f172a;
            outline: none;
        }

        .shop-search input:focus {
            border-color: #8ab7eb;
            box-shadow: 0 0 0 4px rgba(29, 140, 255, 0.08);
        }

        .shop-search button {
            min-width: 120px;
            min-height: 48px;
            border: 0;
            border-radius: 14px;
            background: linear-gradient(135deg, #1d8cff, #1570ef);
            color: #ffffff;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
        }

        .shop-category-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .shop-category-mobile {
            display: none;
        }

        .shop-category-mobile select {
            width: 100%;
            min-height: 44px;
            border-radius: 14px;
            border: 1px solid #d5e2ef;
            background: #ffffff;
            padding: 0 14px;
            color: #0f172a;
            font-size: 0.84rem;
            font-weight: 700;
            outline: none;
        }

        .shop-category-chip {
            min-height: 38px;
            padding: 0 14px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            background: #ffffff;
            border: 1px solid #d9e4ef;
            color: #0f172a;
            font-size: 0.82rem;
            font-weight: 700;
            transition: 0.2s ease;
        }

        .shop-category-chip:hover,
        .shop-category-chip.is-active {
            background: #eff6ff;
            border-color: #bfd6ef;
            color: #0f2f57;
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

            .shop-category-row {
                display: none;
            }

            .shop-category-mobile {
                display: block;
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
                grid-template-columns: 1fr;
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

            .shop-search input,
            .shop-search button {
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
            <form action="{{ route('shop.index') }}" method="GET" class="shop-search">
                <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('Search adapter, keyboard, mouse, memory...') }}">
                @if ($activeCategory !== '')
                    <input type="hidden" name="category" value="{{ $activeCategory }}">
                @endif
                <button type="submit">{{ __('Search') }}</button>
            </form>

            <div class="shop-category-row">
                <a href="{{ route('shop.index', array_filter(['search' => $search ?: null])) }}" class="shop-category-chip {{ $activeCategory === '' ? 'is-active' : '' }}">{{ __('All') }}</a>
                @foreach ($categories as $category)
                    <a href="{{ route('shop.index', array_filter(['category' => $category->slug, 'search' => $search ?: null])) }}" class="shop-category-chip {{ $activeCategory === $category->slug ? 'is-active' : '' }}">{{ $category->name }}</a>
                @endforeach
            </div>

            <form action="{{ route('shop.index') }}" method="GET" class="shop-category-mobile">
                @if ($search !== '')
                    <input type="hidden" name="search" value="{{ $search }}">
                @endif

                <select name="category" aria-label="{{ __('Category') }}" onchange="this.form.submit()">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" @selected($activeCategory === $category->slug)>{{ $category->name }}</option>
                    @endforeach
                </select>
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
@endsection

@include('web.pages.shop.partials.scripts')
