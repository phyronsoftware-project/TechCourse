<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">

        <title>@yield('title', 'TechCourse')</title>
        {{-- Escape dynamic descriptions so product names and text cannot break preview tags. --}}
        <meta name="description" content="{{ $__env->yieldContent('meta_description', 'TechCourse is a learning platform for app development, web development, and useful IT skills.') }}">
        <meta property="og:site_name" content="TechCourse">
        <meta property="og:type" content="@yield('meta_og_type', 'website')">
        {{-- Let product pages provide one canonical social title and URL without duplicate OG tags. --}}
        <meta property="og:title" content="{{ $__env->yieldContent('meta_og_title', $__env->yieldContent('title', 'TechCourse')) }}">
        <meta property="og:description" content="{{ $__env->yieldContent('meta_description', 'TechCourse is a learning platform for app development, web development, and useful IT skills.') }}">
        <meta property="og:url" content="{{ $__env->yieldContent('meta_og_url', url()->current()) }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $__env->yieldContent('meta_og_title', $__env->yieldContent('title', 'TechCourse')) }}">
        <meta name="twitter:description" content="{{ $__env->yieldContent('meta_description', 'TechCourse is a learning platform for app development, web development, and useful IT skills.') }}">
        @stack('meta')
        {{-- Load GA4 base tracking so page_view starts collecting immediately. --}}
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-6EP9GQSD30"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }

            gtag('js', new Date());
            gtag('config', 'G-6EP9GQSD30');

            // Keep one simple helper for custom GA4 events across the website.
            window.trackEvent = function (eventName, params = {}) {
                if (typeof window.gtag !== 'function') {
                    return;
                }

                window.gtag('event', eventName, params);
            };
        </script>
        @php
            $ga4FlashEvents = session('ga4_events', []);
        @endphp
        @if (!empty($ga4FlashEvents))
            <script>
                // Prepare one-time analytics events confirmed by the backend flow.
                window.__techCourseGa4FlashEvents = @json(array_values($ga4FlashEvents));
            </script>
        @endif
        <script>
            // Apply the saved web theme before rendering to prevent a color flash.
            (() => {
                try {
                    if (window.localStorage.getItem('techcourse-web-theme') === 'dark') {
                        document.documentElement.dataset.webTheme = 'dark';
                    }
                } catch (error) {
                    // Keep light mode when browser storage is unavailable.
                }
            })();
        </script>
        {{-- //logo --}}
        <link rel="icon" type="image/png" href="{{ asset('logo/logo1.png') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Noto+Sans+Khmer:wght@400;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <style>
            :root {
                --footer-bg-color: #111827;
                --footer-text-color: #f9f9f9;
                --footer-link-color: #bec5d5;
                --footer-link-hover-color: #fff;
                --footer-border-color: #30363d;
                --font-body: 'Noto Sans Khmer', sans-serif;
                --font-lato: 'Lato', sans-serif;
                --primary: #2e7dff;
                --text: #dedede;
                --border: #03132d;
            }

            * {
                box-sizing: border-box;
            }

            html,
            body {
                margin: 0;
                padding: 0;
                overflow-x: hidden;
                scroll-behavior: smooth;
                scrollbar-width: none;
                -ms-overflow-style: none;
            }

            html::-webkit-scrollbar,
            body::-webkit-scrollbar {
                width: 0;
                height: 0;
            }

            html.scroll-locked,
            body.scroll-locked {
                overflow: hidden !important;
                overscroll-behavior: none;
            }

            body.scroll-locked {
                position: fixed;
                inset: 0;
                width: 100%;
            }

            body.web-shell {
                min-height: 100vh;
                background:
                    radial-gradient(circle at top left, rgba(2, 123, 255, 0.2), transparent 22%),
                    radial-gradient(circle at top right, rgba(12, 181, 255, 0.18), transparent 18%),
                    linear-gradient(180deg, #0b1730 0%, #091120 44%, #060d19 100%);
                color: var(--text);
                font-family: var(--font-body);
            }

            .web-main {
                min-height: 45vh;
                padding: 74px 0 0;
            }

            .web-home-blank {
                min-height: 42vh;
            }

            .web-container {
                width: 85%;
                max-width: 1400px;
                margin: 0 auto;
            }

            header {
                width: 100%;
                position: fixed;
                top: 0;
                left: 0;
                z-index: 999;
                min-height: 70px;
                background-color: rgba(23, 39, 56, 0.6);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                transition: transform 0.3s ease, opacity 0.3s ease, box-shadow 0.3s ease;
                transform: translateY(0);
                opacity: 1;
            }

            header.header-hidden {
                transform: translateY(-115%);
                opacity: 0;
            }

            .header-box {
                width: 85%;
                min-height: 50px;
                margin: 0 auto;
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: nowrap;
                gap: 16px;
            }

            .header-box .logo {
                display: flex;
                height: 100%;
                align-items: center;
                width: auto;
                flex: 1 1 auto;
                justify-content: flex-start;
                min-width: 0;
            }

            .brand-logo {
                display: inline-flex;
                align-items: center;
                /* gap: 12px; */
                text-decoration: none;
                line-height: 1;
            }

            .brand-logo__image {
                width: auto;
                height: 74px;
                display: block;
                flex-shrink: 0;
            }

            .brand-logo__text {
                color: #0f172a;
                font-family: var(--font-lato);
                font-size: 22px;
                font-weight: 800;
                line-height: 1;
                letter-spacing: -0.03em;
                white-space: nowrap;
            }

            .brand-logo__text span {
                color: #2563eb;
            }

            .logo-title {
                flex-shrink: 0;
            }

            .logo h3:hover {
                color: #dedede;
                transform: scale(1.03);
            }

            .brand-link {
                color: #fff;
                text-decoration: none;
            }

            .brand-link span {
                color: #027bff;
                font-weight: 700;
            }

            .header-box .navbar {
                width: 40%;
                display: flex;
                align-items: center;
                justify-content: flex-end;
                position: static;
                background-color: transparent;
                box-shadow: none;
                padding: 0;
            }

            .header-box ul {
                display: flex;
                align-items: center;
                gap: 4px;
                padding: 0;
                margin: 0;
                list-style: none;
            }

            .header-box ul li a {
                color: white;
                text-decoration: none;
                display: block;
                padding: 4px 10px;
                border-radius: 5px;
                transition: all 0.3s ease-in-out;
                font-size: 14px;
                white-space: nowrap;
            }

            .header-box ul li a:hover {
                background-color: #2b3544;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .header-box ul li a.active-link {
                background-color: #2b3544;
                color: white;
                font-weight: 600;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .menu-item {
                position: relative;
            }

            .menu-link-row {
                display: flex;
                align-items: center;
                gap: 2px;
            }

            .menu-dropdown-toggle {
                width: 28px;
                height: 28px;
                border-radius: 8px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: #dbe5f4;
                transition: 0.2s ease;
                flex-shrink: 0;
            }

            .menu-dropdown-toggle:hover {
                background: rgba(255, 255, 255, 0.08);
            }

            .menu-dropdown-toggle i {
                font-size: 12px;
                transition: transform 0.2s ease;
            }

            .menu-sublist {
                position: absolute;
                top: calc(100% + 12px);
                left: 0;
                min-width: 220px;
                padding: 10px;
                display: grid;
                gap: 6px;
                border-radius: 18px;
                background: linear-gradient(180deg, rgba(12, 20, 33, 0.98), rgba(8, 14, 24, 0.98));
                border: 1px solid rgba(255, 255, 255, 0.08);
                box-shadow: 0 18px 34px rgba(0, 0, 0, 0.28);
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transform: translateY(10px);
                transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s ease;
                z-index: 35;
            }

            .menu-sublist li {
                margin-bottom: 0;
            }

            .header-box .menu-sublist li a {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 10px 12px;
                border-radius: 12px;
                color: #dfe8f7;
                font-size: 14px;
                background: transparent;
            }

            .header-box .menu-sublist li a::after {
                content: '\f054';
                font-family: 'Font Awesome 6 Free';
                font-weight: 900;
                font-size: 11px;
                opacity: 0.55;
            }

            .header-box .menu-sublist li a:hover {
                background: rgba(46, 125, 255, 0.16);
                transform: none;
            }

            @media (hover: hover) and (pointer: fine) {
                .menu-item.has-submenu:hover .menu-sublist {
                    opacity: 1;
                    visibility: visible;
                    pointer-events: auto;
                    transform: translateY(0);
                }

                .menu-item.has-submenu:hover .menu-dropdown-toggle i {
                    transform: rotate(180deg);
                }
            }

            .auth-item {
                margin-left: 8px;
            }

            .header-auth-actions {
                --header-control-height: 44px;
                display: flex;
                align-items: center;
                gap: 14px;
                flex-wrap: nowrap;
            }

            .header-box ul li a.header-auth-user {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                width: auto;
                max-width: 260px;
                min-height: var(--header-control-height);
                padding: 0;
                border-radius: 0;
                border: none;
                background: transparent;
                text-decoration: none;
                box-shadow: none;
                transition: opacity 0.2s ease;
                flex-shrink: 0;
            }

            .header-box ul li a.header-auth-user:hover {
                background: transparent;
                border-color: transparent;
                box-shadow: none;
                transform: none;
                opacity: 0.9;
            }

            .header-auth-user__avatar {
                width: 38px;
                height: 38px;
                border-radius: 18px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: #1a4388;
                color: #ffffff;
                font-size: 15px;
                font-weight: 700;
                flex-shrink: 0;
                overflow: hidden;
                border: none;
            }

            .header-auth-user__avatar-image {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
                border-radius: inherit;
            }

            .header-auth-user__content {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                justify-content: center;
                min-width: 0;
                gap: 2px;
                flex: 1;
                text-align: left;
            }

            .header-auth-user__content strong {
                color: #173f87;
                font-size: 13px;
                font-weight: 600;
                line-height: 1.05;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                display: block;
            }

            .header-auth-user__content span {
                color: #7a8ca8;
                font-size: 11px;
                line-height: 1.05;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                display: block;
                max-width: 100%;
            }

            .header-auth-divider {
                width: 1px;
                height: 34px;
                background: #e5edf6;
                flex-shrink: 0;
            }

            .header-notification {
                position: relative;
                display: inline-flex;
                align-items: center;
                flex-shrink: 0;
            }

            .header-notification__badge {
                position: absolute;
                top: 7px;
                right: 7px;
                min-width: 18px;
                height: 18px;
                padding: 0 4px;
                border-radius: 999px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: #ef4444;
                color: #ffffff;
                font-size: 10px;
                font-weight: 700;
                line-height: 1;
                box-shadow: 0 0 0 2px #ffffff;
            }

            .header-auth-btn {
                min-height: var(--header-control-height);
                min-width: 98px;
                padding: 0 14px;
                border-radius: 12px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                text-decoration: none;
                font-size: 15px;
                transition: 0.25s ease;
                flex-shrink: 0;
                line-height: 1;
            }

            .header-auth-btn-icon {
                min-width: 44px;
                width: 44px;
                padding: 0;
                border-radius: 14px;
            }

            .header-auth-btn-login {
                background: rgba(255, 255, 255, 0.08);
                border: 1px solid rgba(255, 255, 255, 0.12);
                color: #fff;
            }

            .header-auth-btn-login:hover {
                background: rgba(255, 255, 255, 0.14);
            }

            .header-auth-btn-logout {
                display: inline-flex !important;
                width: auto !important;
                align-items: center !important;
                justify-content: center !important;
                min-width: 104px !important;
                max-width: none !important;
                height: 44px !important;
                min-height: 44px !important;
                padding: 0 14px !important;
                gap: 8px !important;
                border-radius: 12px !important;
                background: #ffffff !important;
                border: 1px solid #d7e3f0 !important;
                color: #173f87 !important;
                box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04) !important;
                font-weight: 700 !important;
                text-align: center !important;
                flex: 0 0 auto !important;
            }

            .header-auth-btn-logout:hover {
                background: #f4f8fd !important;
                border-color: #d7e3f0 !important;
                color: #173f87 !important;
                box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04) !important;
            }

            .header-auth-btn-notification {
                background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
                border: 1px solid #d7e3f0;
                color: #173f87;
                box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04);
            }

            .header-auth-btn-notification:hover,
            .header-notification.is-open .header-auth-btn-notification {
                background: #eff6ff;
                color: #0f2f57;
            }

            .header-notification__panel {
                position: absolute;
                top: calc(100% + 12px);
                right: 0;
                width: min(430px, calc(100vw - 24px));
                padding: 20px;
                border-radius: 24px;
                border: 1px solid #d7e3f0;
                background: #ffffff;
                box-shadow: 0 22px 46px rgba(15, 23, 42, 0.14);
                z-index: 35;
                overflow: hidden;
            }

            .header-notification__panel[hidden] {
                display: none;
            }

            .header-notification__panel-head {
                display: flex;
                flex-direction: column;
                gap: 4px;
                padding-bottom: 14px;
                margin-bottom: 14px;
                border-bottom: 1px solid #e5edf6;
            }

            .header-notification__panel-head strong {
                color: #0f172a;
                font-size: 15px;
                line-height: 1.2;
            }

            .header-notification__panel-head span {
                color: #64748b;
                font-size: 12px;
                line-height: 1.4;
            }

            .header-notification__list {
                display: flex;
                flex-direction: column;
                gap: 12px;
                max-height: 360px;
                overflow-y: auto;
                overflow-x: hidden;
                padding-right: 2px;
                scrollbar-width: none;
                -ms-overflow-style: none;
            }

            .header-notification__list::-webkit-scrollbar {
                width: 0;
                height: 0;
            }

            .header-notification__item {
                display: flex;
                align-items: flex-start;
                gap: 16px;
                width: 100%;
                box-sizing: border-box;
                padding: 18px 20px;
                border-radius: 26px;
                border: 1px solid #d7e3f0;
                background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
                text-decoration: none;
                transition: 0.2s ease;
                overflow: visible;
            }

            .header-box ul li a.header-notification__item,
            .navbar.offcanvas-right .drawer__menu li a.header-notification__item {
                display: flex;
                width: 100%;
                max-width: 100%;
                padding: 18px 20px;
                border-radius: 26px;
                white-space: normal;
                text-decoration: none;
                transform: none;
                box-shadow: none;
            }

            .header-notification__item:hover {
                background: linear-gradient(180deg, #ffffff 0%, #f3f8ff 100%);
                border-color: #cfdced;
                transform: none;
                box-shadow: none;
            }

            .header-notification__item.is-unread {
                background: linear-gradient(180deg, #fdfefe 0%, #f3f8ff 100%);
                border-color: #cfe0f3;
            }

            .header-notification__item-content {
                display: flex;
                flex-direction: column;
                gap: 10px;
                flex: 1;
                min-width: 0;
                padding-top: 2px;
                overflow: visible;
            }

            .header-notification__item-head {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 16px;
                width: 100%;
                flex-wrap: wrap;
            }

            .header-notification__item-content strong {
                color: #0f172a;
                display: block;
                font-size: 12px;
                font-weight: 700;
                line-height: 1.4;
                white-space: normal;
                word-break: break-word;
                overflow-wrap: anywhere;
                flex: 1;
            }

            .header-notification__item-message {
                display: block;
                color: #475569;
                font-size: 12px;
                line-height: 1.5;
                white-space: normal;
                word-break: break-word;
                overflow-wrap: anywhere;
            }

            .header-notification__item-time {
                color: #6b7c98;
                display: inline-block;
                font-size: 12px;
                font-weight: 600;
                line-height: 1.4;
                white-space: nowrap;
                flex-shrink: 0;
                padding-top: 3px;
                margin-left: auto;
            }

            .header-notification__item-icon {
                width: 42px;
                height: 42px;
                border-radius: 14px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                font-size: 16px;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5);
            }

            .header-notification__item-icon--info {
                background: #e0f2fe;
                color: #0369a1;
            }

            .header-notification__item-icon--success {
                background: #dcfce7;
                color: #15803d;
            }

            .header-notification__item-icon--warning {
                background: #fef3c7;
                color: #b45309;
            }

            .header-notification__item-icon--error {
                background: #fee2e2;
                color: #b91c1c;
            }

            .header-notification__empty {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 10px;
                padding: 8px 4px 2px;
            }

            .header-notification__empty-icon {
                width: 52px;
                height: 52px;
                border-radius: 18px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: #eff6ff;
                color: #2563eb;
                font-size: 21px;
            }

            .header-notification__empty strong {
                color: #0f172a;
                font-size: 14px;
                line-height: 1.2;
            }

            .header-notification__empty p {
                margin: 0;
                color: #64748b;
                font-size: 12px;
                line-height: 1.5;
            }

            .header-auth-btn-register {
                display: inline-flex !important;
                width: auto !important;
                align-items: center !important;
                justify-content: center !important;
                min-width: 104px !important;
                max-width: none !important;
                height: 44px !important;
                min-height: 44px !important;
                padding: 0 14px !important;
                gap: 8px !important;
                border-radius: 12px !important;
                background: #ffffff !important;
                border: 1px solid #d7e3f0 !important;
                color: #173f87 !important;
                box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04) !important;
                font-weight: 700 !important;
                text-align: center !important;
                flex: 0 0 auto !important;
            }

            .header-auth-btn-register:hover {
                background: #f4f8fd !important;
                border-color: #d7e3f0 !important;
                color: #173f87 !important;
                box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04) !important;
            }

            .header-auth-btn-register i {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 16px !important;
                height: 16px !important;
                color: #173f87 !important;
                font-size: 16px !important;
                flex-shrink: 0 !important;
            }

            .header-auth-btn-logout i {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 16px !important;
                height: 16px !important;
                color: #173f87 !important;
                font-size: 16px !important;
                flex-shrink: 0 !important;
            }

            .header-auth-btn-logout .header-auth-btn__label,
            .header-auth-btn-register .header-auth-btn__label {
                font-size: 13px !important;
                font-weight: 700 !important;
                line-height: 1 !important;
                white-space: nowrap !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            .header-auth-btn__label {
                font-size: 13px !important;
                font-weight: 700 !important;
                line-height: 1 !important;
                white-space: nowrap !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            .web-alert {
                position: fixed;
                right: 24px;
                bottom: 24px;
                width: min(388px, calc(100vw - 30px));
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 12px 14px;
                border-radius: 20px;
                border: 1px solid rgba(215, 227, 240, 0.95);
                background:
                    linear-gradient(90deg, rgba(241, 248, 255, 0.95) 0%, rgba(255, 255, 255, 0.98) 18%, #ffffff 44%),
                    #ffffff;
                color: #0f172a;
                box-shadow: 0 14px 30px rgba(15, 23, 42, 0.10);
                z-index: 1002;
                opacity: 0;
                transform: translateY(16px) scale(0.97);
                animation: web-alert-enter 0.32s ease forwards;
            }

            .web-alert.is-leaving {
                animation: web-alert-exit 0.28s ease forwards;
            }

            .web-alert--success {
                border-color: #d5efdf;
                background:
                    linear-gradient(90deg, rgba(229, 255, 239, 0.92) 0%, rgba(255, 255, 255, 0.98) 18%, #ffffff 44%),
                    #ffffff;
            }

            .web-alert--error {
                border-color: #f5d1d8;
                background:
                    linear-gradient(90deg, rgba(255, 236, 239, 0.94) 0%, rgba(255, 255, 255, 0.98) 18%, #ffffff 44%),
                    #ffffff;
            }

            .web-alert--warning {
                border-color: #f3e1b4;
                background:
                    linear-gradient(90deg, rgba(255, 246, 214, 0.94) 0%, rgba(255, 255, 255, 0.98) 18%, #ffffff 44%),
                    #ffffff;
            }

            .web-alert--info {
                border-color: #d3e6fb;
                background:
                    linear-gradient(90deg, rgba(234, 247, 255, 0.94) 0%, rgba(255, 255, 255, 0.98) 18%, #ffffff 44%),
                    #ffffff;
            }

            .web-alert__icon {
                width: 44px;
                height: 44px;
                border-radius: 14px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                font-size: 18px;
                background: #ffffff;
                box-shadow:
                    0 6px 14px rgba(15, 23, 42, 0.06),
                    inset 0 1px 0 rgba(255, 255, 255, 0.85);
            }

            .web-alert--success .web-alert__icon {
                color: #22b364;
            }

            .web-alert--error .web-alert__icon {
                color: #ef5f70;
            }

            .web-alert--warning .web-alert__icon {
                color: #efad1d;
            }

            .web-alert--info .web-alert__icon {
                color: #44aef7;
            }

            .web-alert__content {
                min-width: 0;
                flex: 1;
                padding-top: 0;
            }

            .web-alert__title {
                font-family: var(--font-lato);
                font-size: 14px;
                font-weight: 800;
                line-height: 1.15;
                margin-bottom: 3px;
                color: #111827;
            }

            .web-alert__message {
                font-size: 12px;
                line-height: 1.45;
                color: #6b7280;
            }

            .web-alert__close {
                width: 28px;
                height: 28px;
                border: 0;
                border-radius: 10px;
                background: transparent;
                color: #b1bac7;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                flex-shrink: 0;
                transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
            }

            .web-alert__close:hover {
                background: #f3f6fb;
                color: #7b8798;
                transform: translateY(-1px);
            }

            @keyframes web-alert-enter {
                from {
                    opacity: 0;
                    transform: translateY(16px) scale(0.97);
                }

                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            @keyframes web-alert-exit {
                from {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }

                to {
                    opacity: 0;
                    transform: translateY(16px) scale(0.96);
                }
            }

            .scroll-top-btn {
                position: fixed;
                right: 24px;
                bottom: 24px;
                width: 58px;
                height: 58px;
                border: 1px solid rgba(15, 23, 42, 0.1);
                border-radius: 22px;
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(241, 245, 249, 0.96));
                color: #0f172a;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                box-shadow: 0 22px 40px rgba(15, 23, 42, 0.14);
                cursor: pointer;
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transform: translateY(14px) scale(0.92);
                transition: opacity 0.24s ease, transform 0.24s ease, visibility 0.24s ease;
                z-index: 998;
            }

            .scroll-top-btn.is-visible {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                transform: translateY(0) scale(1);
            }

            .scroll-top-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 26px 44px rgba(15, 23, 42, 0.18);
            }

            .scroll-top-btn::before {
                content: "";
                position: absolute;
                inset: 8px;
                border-radius: 18px;
                background: linear-gradient(135deg, rgba(15, 23, 42, 0.06), rgba(15, 23, 42, 0));
            }

            .scroll-top-btn i {
                position: relative;
                z-index: 1;
            }

            .web-loading-indicator {
                position: fixed;
                inset: 0;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transition: opacity 0.2s ease, visibility 0.2s ease;
                /* Keep loading above shop rails, drawers, and page modals. */
                z-index: 9999;
            }

            .web-loading-indicator::before {
                content: "";
                position: absolute;
                inset: 0;
                /* Use a clear semi-transparent black overlay without blur. */
                background: rgba(0, 0, 0, 0.55);
            }

            .web-loading-indicator.is-visible {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
            }

            .web-loading-indicator__box {
                position: relative;
                z-index: 1;
                display: inline-flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 18px;
                /* Keep only the page overlay behind the loader icon. */
                padding: 12px;
                background: transparent;
                border: 0;
                color: #f8fafc;
                box-shadow: none;
                transform: scale(0.96);
                transition: transform 0.2s ease;
            }

            .web-loading-indicator.is-visible .web-loading-indicator__box {
                transform: scale(1);
            }

            .web-loading-indicator__cube-grid {
                /* Keep the cube grid compact on the page overlay. */
                width: 28px;
                height: 28px;
            }

            .web-loading-indicator__cube {
                float: left;
                width: 33.333%;
                height: 33.333%;
                background: linear-gradient(145deg, #f8fafc 0%, #6ee7f9 100%);
                animation: web-loading-cube-grid 1.3s infinite ease-in-out;
            }

            .web-loading-indicator__cube--1 {
                animation-delay: 0.2s;
            }

            .web-loading-indicator__cube--2 {
                animation-delay: 0.3s;
            }

            .web-loading-indicator__cube--3 {
                animation-delay: 0.4s;
            }

            .web-loading-indicator__cube--4 {
                animation-delay: 0.1s;
            }

            .web-loading-indicator__cube--5 {
                animation-delay: 0.2s;
            }

            .web-loading-indicator__cube--6 {
                animation-delay: 0.3s;
            }

            .web-loading-indicator__cube--7 {
                animation-delay: 0s;
            }

            .web-loading-indicator__cube--8 {
                animation-delay: 0.1s;
            }

            .web-loading-indicator__cube--9 {
                animation-delay: 0.2s;
            }

            .web-loading-indicator__text {
                font-size: 12px;
                font-weight: 700;
                white-space: nowrap;
                line-height: 1;
                color: rgba(241, 245, 249, 0.92);
            }

            /* Keep card skeleton loading shared across course and product card views. */
            [data-skeleton-card] {
                position: relative;
            }

            [data-skeleton-card].is-skeleton {
                overflow: hidden;
                pointer-events: none;
            }

            [data-skeleton-card].is-skeleton [data-skeleton-image],
            [data-skeleton-card].is-skeleton [data-skeleton-block],
            [data-skeleton-card].is-skeleton [data-skeleton-line] {
                position: relative;
                overflow: hidden;
            }

            [data-skeleton-card].is-skeleton [data-skeleton-image]::after,
            [data-skeleton-card].is-skeleton [data-skeleton-block]::after,
            [data-skeleton-card].is-skeleton [data-skeleton-line]::after {
                content: '';
                position: absolute;
                inset: 0;
                border-radius: inherit;
                background: linear-gradient(90deg, #e8eef5 0%, #f8fbff 50%, #e8eef5 100%);
                background-size: 200% 100%;
                animation: web-card-skeleton-wave 1.4s ease-in-out infinite;
            }

            [data-skeleton-card].is-skeleton img,
            [data-skeleton-card].is-skeleton i {
                opacity: 0;
            }

            [data-skeleton-card].is-skeleton [data-skeleton-line] {
                color: transparent !important;
                min-height: 14px;
            }

            [data-skeleton-card].is-skeleton [data-skeleton-line] > * {
                visibility: hidden;
            }

            [data-skeleton-card].is-skeleton [data-skeleton-block] {
                color: transparent !important;
            }

            .rellax {
                will-change: transform;
            }

            @keyframes web-loading-cube-grid {
                0%,
                70%,
                100% {
                    transform: scale3d(1, 1, 1);
                }

                35% {
                    transform: scale3d(0, 0, 1);
                }
            }

            @keyframes web-card-skeleton-wave {
                0% {
                    background-position: 200% 0;
                }

                100% {
                    background-position: -200% 0;
                }
            }

            .lang-container {
                position: relative;
                display: inline-block;
            }

            .lang-toggle {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
                width: 124px;
                min-width: 124px;
                min-height: 40px;
                padding: 6px 10px;
                background: linear-gradient(180deg, rgba(20, 32, 52, 0.94), rgba(14, 23, 38, 0.94));
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 24px;
                cursor: pointer;
                transition: background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
                color: #f1f1f1;
                font-weight: 600;
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.18);
            }

            .lang-toggle:hover {
                border-color: rgba(46, 125, 255, 0.55);
                box-shadow: 0 12px 24px rgba(0, 0, 0, 0.24);
            }

            .lang-toggle__left {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                min-width: 0;
            }

            .lang-flag {
                width: 18px;
                height: 18px;
                border-radius: 999px;
                object-fit: cover;
                flex-shrink: 0;
                border: 1px solid rgba(255, 255, 255, 0.18);
            }

            .lang-toggle span {
                font-size: 0.88rem;
                line-height: 1;
            }

            .lang-dropdown-menu {
                display: flex;
                flex-direction: column;
                position: absolute;
                top: 100%;
                right: 0;
                min-width: 180px;
                margin-top: 12px;
                list-style: none;
                background: linear-gradient(180deg, rgba(13, 21, 35, 0.98), rgba(9, 16, 28, 0.98));
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 16px;
                box-shadow: 0 18px 34px rgba(0, 0, 0, 0.28);
                overflow: hidden;
                z-index: 30;
                padding: 8px;
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transform: translateY(8px);
                transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s ease;
            }

            .lang-container.is-open .lang-dropdown-menu {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                transform: translateY(0);
            }

            .lang-dropdown-menu li {
                width: 100%;
                margin-bottom: 0;
            }

            .header-box .lang-dropdown-menu li a {
                padding: 10px 12px;
                width: 100%;
                font-size: 0.88rem;
                color: #f1f1f1;
                display: grid;
                grid-template-columns: 22px minmax(0, 1fr);
                align-items: center;
                gap: 10px;
                border-radius: 10px;
                text-align: left;
                line-height: 1.2;
            }

            .header-box .lang-dropdown-menu li a:hover {
                background: rgba(46, 125, 255, 0.16);
                color: #ffffff;
            }

            .header-box .lang-dropdown-menu li a span {
                display: block;
                text-align: left;
                white-space: nowrap;
            }

            .header-box .lang-dropdown-menu li a .lang-flag {
                justify-self: start;
                align-self: center;
            }

            .dropdown-caret {
                font-size: 0.74rem;
                margin-left: 0;
                opacity: 0.9;
                transition: transform 0.2s ease;
            }

            .lang-container.is-open .dropdown-caret {
                transform: rotate(180deg);
            }

            .mobile-menu-icon {
                display: none;
                width: 50px;
                height: 50px;
                padding: 0;
                border: 1px solid rgba(148, 163, 184, 0.45);
                border-radius: 18px;
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(226, 232, 240, 0.92));
                box-shadow: 0 16px 34px rgba(15, 23, 42, 0.12);
                cursor: pointer;
                color: #0f172a;
                z-index: 1000;
                margin-right: 15px;
                align-items: center;
                justify-content: center;
            }

            .mobile-menu-icon__ring {
                width: 100%;
                height: 100%;
                border-radius: 17px;
                display: inline-flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 5px;
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.68), rgba(255, 255, 255, 0.18));
            }

            .mobile-menu-icon__line {
                width: 20px;
                height: 3px;
                border-radius: 999px;
                background: #1e293b;
                box-shadow: 0 1px 0 rgba(255, 255, 255, 0.3);
            }

            .web-drawer-backdrop {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0);
                opacity: 0;
                visibility: hidden;
                transition: 0.2s ease;
                z-index: 998;
            }

            body.menu-open .web-drawer-backdrop {
                background: rgba(0, 0, 0, 0.45);
                opacity: 1;
                visibility: visible;
            }

            body.menu-open {
                overflow: hidden;
            }

            body.menu-open .mobile-menu-icon {
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
            }

            footer {
                margin-top: 60px;
                background-color: #050c1e;
                color: var(--footer-text-color);
                font-family: var(--font-body);
                padding: 30px 0;
                box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.2);
                border-top: 0;
                position: relative;
            }

            footer::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 1px;
                background: #bcbdbd;
                overflow: hidden;
            }

            footer::after {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 1px;
                background:
                    linear-gradient(
                        90deg,
                        rgba(10, 47, 107, 0) 0%,
                        rgba(10, 47, 107, 0.96) 18%,
                        rgba(254, 23, 7, 1) 52%,
                        rgba(188, 189, 189, 0.95) 82%,
                        rgba(188, 189, 189, 0) 100%
                    );
                background-repeat: no-repeat;
                background-size: 280px 100%;
                background-position: -280px 0;
                animation: footerTopLineRun 4.2s linear infinite;
                pointer-events: none;
            }

            @keyframes footerTopLineRun {
                0% {
                    background-position: -280px 0;
                }

                100% {
                    background-position: calc(100% + 280px) 0;
                }
            }

            .footer-content {
                width: 90%;
                max-width: 1200px;
                margin: auto;
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                gap: 30px;
                padding-bottom: 30px;
                border-bottom: 1px solid var(--footer-border-color);
            }

            .fo {
                flex: 1;
                min-width: 400px;
                margin-right: 0;
            }

            .fo1 {
                flex: 1;
                min-width: 160px;
            }

            .fo h3,
            .fo1 h3 {
                font-family: var(--font-lato);
                font-weight: 700;
                font-size: 1.2em;
                margin-bottom: 15px;
                color: var(--footer-link-hover-color);
            }

            .fo p {
                line-height: 1.8;
                font-size: 0.95em;
                color: var(--footer-link-color);
            }

            .footer-store-links {
                display: flex;
                flex-direction: column;
                gap: 12px;
                margin-top: 18px;
                max-width: 220px;
            }

            .footer-store-links a {
                display: inline-flex;
                transition: transform 0.2s ease, opacity 0.2s ease;
            }

            .footer-store-links a:hover {
                transform: translateY(-2px);
                opacity: 0.92;
            }

            .footer-store-links img {
                width: 100%;
                height: auto;
                display: block;
            }

            .footer-store-links--panel {
                max-width: 165px;
                gap: 10px;
            }

            .fo1 ul {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .fo1 li {
                margin-bottom: 10px;
            }

            .fo1 li a {
                color: var(--footer-link-color);
                text-decoration: none;
                transition: color 0.2s ease, transform 0.2s ease;
                display: flex;
                align-items: center;
                font-size: 14px;
            }

            .fo1 li a:hover {
                color: var(--footer-link-hover-color);
                transform: translateX(5px);
            }

            .fo1 li a i {
                margin-right: 10px;
                font-size: 1.1em;
            }

            .sol {
                display: flex;
                list-style: none;
                padding: 0;
                margin: 0;
                gap: 10px;
            }

            .sol li {
                margin-bottom: 0;
            }

            .sol li a {
                width: 30px;
                height: 30px;
                display: flex;
                justify-content: center;
                align-items: center;
                border-radius: 7px;
                transition: transform 0.2s ease;
                text-decoration: none;
                color: #ffffff !important;
            }

            .sol li a:hover {
                transform: translateY(-3px);
            }

            .sol li a i {
                font-size: 20px;
                margin-right: 0;
                color: #ffffff !important;
            }

            .sol li a[href*='facebook'] {
                background-color: #4267b2;
            }

            .sol li a[href*='youtube'] {
                background-color: #ff0000;
            }

            .sol li a[href*='t.me'] {
                background-color: #0088cc;
            }

            .sol li a[href*='tiktok'] {
                background-color: #000000;
            }

            .sol li a[href*='twitter'] {
                background-color: #1da1f2;
            }

            .copyright-text {
                text-align: center;
                font-size: 0.85em;
                color: var(--footer-link-color);
                padding-top: 20px;
                margin-bottom: 0;
            }

            .contact-info-footer {
                display: flex;
                flex-direction: column;
                gap: 10px;
                margin-top: 15px;
                margin-bottom: 15px;
            }

            .contact-info-footer a {
                color: var(--footer-link-color);
                text-decoration: none;
                font-size: 14px;
                display: flex;
                align-items: center;
                transition: transform 0.3s ease;
            }

            .contact-info-footer a i {
                margin-right: 10px;
                font-size: 1.1em;
            }

            .contact-info-footer a:hover {
                color: var(--footer-link-hover-color);
                transform: scale(1.01);
            }

            .web-section {
                width: min(1200px, calc(100% - 32px));
                margin: 0 auto;
            }

            .hero-panel {
                position: relative;
                overflow: hidden;
                border-radius: 32px;
                padding: 42px;
                background:
                    radial-gradient(circle at top left, rgba(51, 157, 255, 0.28), transparent 24%),
                    linear-gradient(135deg, rgba(20, 35, 58, 0.98), rgba(8, 14, 28, 0.98));
                border: 1px solid rgba(255, 255, 255, 0.08);
                box-shadow: 0 20px 50px rgba(0, 0, 0, 0.24);
            }

            .hero-grid {
                display: grid;
                grid-template-columns: minmax(0, 1.1fr) minmax(320px, 0.9fr);
                gap: 28px;
                align-items: center;
            }

            .hero-kicker {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                min-height: 38px;
                padding: 0 14px;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.07);
                color: #d7e5fa;
                font-size: 13px;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            .hero-title {
                margin: 18px 0 14px;
                color: #fff;
                font-family: var(--font-lato);
                font-size: clamp(2.3rem, 5vw, 4.1rem);
                line-height: 1.02;
            }

            .hero-copy {
                max-width: 640px;
                color: #b9cae2;
                font-size: 1.05rem;
                line-height: 1.8;
            }

            .hero-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 14px;
                margin-top: 28px;
            }

            .web-btn {
                min-height: 48px;
                padding: 0 18px;
                border-radius: 14px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                text-decoration: none;
                font-size: 15px;
                font-weight: 700;
                transition: 0.2s ease;
            }

            .web-btn-primary {
                color: #fff;
                background: linear-gradient(135deg, #0b84ff, #3aa2ff);
                box-shadow: 0 12px 24px rgba(11, 132, 255, 0.22);
            }

            .web-btn-secondary {
                color: #dce6f6;
                background: rgba(255, 255, 255, 0.06);
                border: 1px solid rgba(255, 255, 255, 0.12);
            }

            .hero-card-stack {
                display: grid;
                gap: 16px;
            }

            .hero-card-mini {
                padding: 18px;
                border-radius: 22px;
                background: rgba(255, 255, 255, 0.06);
                border: 1px solid rgba(255, 255, 255, 0.08);
            }

            .hero-stat-row {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 14px;
                margin-top: 18px;
            }

            .hero-stat {
                padding: 16px;
                border-radius: 18px;
                background: rgba(7, 14, 27, 0.7);
                border: 1px solid rgba(255, 255, 255, 0.06);
            }

            .hero-stat strong {
                display: block;
                color: #fff;
                font-size: 1.6rem;
                font-family: var(--font-lato);
            }

            .hero-stat span {
                color: #9fb2cd;
                font-size: 13px;
            }

            .section-head {
                display: flex;
                align-items: flex-end;
                justify-content: space-between;
                gap: 18px;
                margin: 46px 0 22px;
            }

            .section-kicker {
                color: #62b3ff;
                font-size: 13px;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .section-title {
                margin: 8px 0 0;
                color: #fff;
                font-family: var(--font-lato);
                font-size: clamp(1.8rem, 4vw, 2.7rem);
            }

            .section-copy {
                color: #9bb0ce;
                line-height: 1.8;
                max-width: 760px;
            }

            .chip-row {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            .chip {
                min-height: 36px;
                padding: 0 14px;
                border-radius: 999px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: rgba(255, 255, 255, 0.06);
                border: 1px solid rgba(255, 255, 255, 0.08);
                color: #d9e6fb;
                text-decoration: none;
                font-size: 13px;
                font-weight: 600;
            }

            .course-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 28px;
            }

            .course-card {
                position: relative;
                overflow: hidden;
                border-radius: 22px;
                background: #1b2d41;
                border: 1px solid rgba(255, 255, 255, 0.08);
                text-decoration: none;
                color: inherit;
                box-shadow: 0 16px 36px rgba(0, 0, 0, 0.18);
                transition: transform 0.22s ease, box-shadow 0.22s ease;
            }

            .course-card:hover {
                transform: translateY(-6px);
                box-shadow: 0 22px 42px rgba(0, 0, 0, 0.24);
            }

            .course-card__media {
                position: relative;
                height: 250px;
                background: linear-gradient(180deg, #5e88bf, #315f9f);
                overflow: hidden;
            }

            .course-card__media img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .course-card__overlay {
                position: absolute;
                inset: 0;
                background: linear-gradient(180deg, rgba(10, 16, 28, 0.02), rgba(10, 16, 28, 0.16));
            }

            .course-card__badges {
                position: absolute;
                top: 16px;
                right: 16px;
                display: flex;
                flex-wrap: wrap;
                justify-content: flex-end;
                gap: 8px;
                z-index: 2;
            }

            .course-card__badge {
                min-height: 34px;
                padding: 0 12px;
                border-radius: 10px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: rgba(34, 45, 65, 0.88);
                color: #fff;
                font-size: 13px;
                font-weight: 700;
                backdrop-filter: blur(8px);
            }

            .course-card__body {
                padding: 22px 22px 24px;
            }

            .course-card__meta {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 14px;
                color: #8cbcff;
                font-size: 12px;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            .course-card__title {
                margin: 0 0 16px;
                color: #fff;
                font-family: var(--font-lato);
                font-size: 1.9rem;
                line-height: 1.15;
            }

            .course-card__title--small {
                font-size: 1.12rem;
                line-height: 1.35;
            }

            .course-card__copy {
                color: rgba(230, 236, 247, 0.68);
                font-size: 15px;
                line-height: 1.7;
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .course-card__footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                margin-top: 18px;
                padding-top: 14px;
                border-top: 1px solid rgba(255, 255, 255, 0.08);
                color: #dce6f5;
                font-size: 14px;
            }

            .course-shell,
            .lesson-shell,
            .auth-shell,
            .info-shell {
                width: min(1200px, calc(100% - 32px));
                margin: 0 auto;
            }

            .course-layout,
            .lesson-layout {
                display: grid;
                grid-template-columns: minmax(0, 1.35fr) 360px;
                gap: 24px;
            }

            .glass-panel {
                border-radius: 28px;
                background: linear-gradient(180deg, rgba(14, 24, 40, 0.94), rgba(9, 16, 28, 0.96));
                border: 1px solid rgba(255, 255, 255, 0.08);
                box-shadow: 0 18px 40px rgba(0, 0, 0, 0.22);
            }

            .course-hero {
                padding: 28px;
            }

            .course-hero__media {
                border-radius: 24px;
                overflow: hidden;
                background: #1f3248;
                height: 360px;
                margin-bottom: 24px;
            }

            .course-hero__media img,
            .lesson-video iframe,
            .lesson-video video {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .detail-badges {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-bottom: 16px;
            }

            .detail-badge {
                min-height: 36px;
                padding: 0 14px;
                border-radius: 999px;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: rgba(255, 255, 255, 0.06);
                color: #e5eefb;
                font-size: 13px;
            }

            .course-hero__title,
            .lesson-title,
            .auth-title,
            .info-title {
                margin: 0 0 14px;
                color: #fff;
                font-family: var(--font-lato);
                font-size: clamp(2rem, 4vw, 3rem);
                line-height: 1.12;
            }

            .course-hero__copy,
            .lesson-copy,
            .info-copy {
                color: #a8bbd7;
                line-height: 1.85;
            }

            .lesson-video {
                border-radius: 24px;
                overflow: hidden;
                background: #0a1322;
                aspect-ratio: 16 / 9;
                margin-bottom: 22px;
            }

            .lesson-sidebar,
            .course-sidebar {
                padding: 24px;
            }

            .lesson-list {
                display: grid;
                gap: 12px;
                margin-top: 18px;
            }

            .lesson-item {
                display: block;
                padding: 16px;
                border-radius: 18px;
                background: rgba(255, 255, 255, 0.04);
                border: 1px solid rgba(255, 255, 255, 0.06);
                text-decoration: none;
                color: #dce6f5;
                transition: 0.2s ease;
            }

            .lesson-item:hover,
            .lesson-item.is-active {
                background: rgba(17, 123, 255, 0.14);
                border-color: rgba(17, 123, 255, 0.34);
            }

            .lesson-item__top {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 8px;
                font-size: 13px;
                color: #89baff;
            }

            .lesson-item__title {
                color: #fff;
                font-weight: 700;
                font-size: 15px;
                line-height: 1.5;
            }

            .lesson-item__copy {
                margin-top: 8px;
                color: #9eb0ca;
                font-size: 13px;
                line-height: 1.65;
            }

            .split-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 16px;
                margin-top: 24px;
            }

            .split-card {
                padding: 18px;
                border-radius: 20px;
                background: rgba(255, 255, 255, 0.04);
                border: 1px solid rgba(255, 255, 255, 0.06);
            }

            .auth-wrap {
                display: grid;
                grid-template-columns: minmax(0, 1fr) minmax(380px, 460px);
                gap: 24px;
                align-items: stretch;
            }

            .auth-feature {
                padding: 32px;
            }

            .auth-card {
                padding: 32px;
            }

            .auth-copy {
                color: #a8bbd7;
                line-height: 1.85;
                margin-bottom: 22px;
            }

            .auth-list {
                display: grid;
                gap: 12px;
                margin-top: 22px;
            }

            .auth-list li {
                display: flex;
                align-items: center;
                gap: 10px;
                color: #dce6f5;
            }

            .auth-form {
                display: grid;
                gap: 16px;
            }

            .auth-field label {
                display: block;
                margin-bottom: 8px;
                color: #e6eef9;
                font-weight: 600;
            }

            .auth-input {
                width: 100%;
                min-height: 48px;
                padding: 0 14px;
                border-radius: 14px;
                border: 1px solid rgba(255, 255, 255, 0.1);
                background: rgba(255, 255, 255, 0.05);
                color: #fff;
            }

            .auth-help {
                color: #95aac9;
                font-size: 14px;
            }

            .auth-switch {
                color: #9eb0ca;
                font-size: 14px;
                text-align: center;
            }

            .auth-switch a {
                color: #72b8ff;
                font-weight: 700;
            }

            .empty-state {
                padding: 26px;
                border-radius: 22px;
                background: rgba(255, 255, 255, 0.04);
                border: 1px dashed rgba(255, 255, 255, 0.14);
                color: #a8bbd7;
                text-align: center;
            }

            .drawer__header {
                display: none;
                align-items: center;
                justify-content: flex-start;
                margin-bottom: 20px;
            }

            .drawer__back {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                outline: none;
                border: none;
                padding: 10px 12px;
                font-size: 15px;
                border-radius: 10px;
                background: #162031;
                color: var(--text);
                cursor: pointer;
            }

            .drawer__back:hover {
                background: #1c2a44;
            }

            .drawer__menu {
                list-style: none;
            }

            @media (max-width: 992px) {
                .mobile-menu-icon {
                    display: block;
                    margin-left: auto;
                    z-index: 1001;
                }

                .header-box {
                    flex-wrap: wrap;
                    justify-content: center;
                    width: 95%;
                    padding: 0 10px;
                }

                .header-box .logo {
                    width: auto;
                    flex-grow: 1;
                    margin-bottom: 0;
                }

                .brand-logo__image {
                    height: 60px;
                }

                .brand-logo__text {
                    font-size: 24px;
                }

                .logo h3 {
                    margin-right: 15px;
                    font-size: 17px;
                }

                .date,
                .header-box .navbar {
                    display: block;
                    width: auto;
                }

                .navbar.offcanvas-right {
                    position: fixed;
                    top: 0;
                    right: 0;
                    width: 100vw;
                    max-width: 100vw;
                    height: 100vh;
                    background-color: #161616;
                    backdrop-filter: blur(10px);
                    -webkit-backdrop-filter: blur(10px);
                    box-shadow: -8px 0 20px rgba(0, 0, 0, 0.25);
                    padding: 20px 16px;
                    z-index: 999;
                    transform: translateX(100%);
                    transition: transform 0.28s ease-in-out;
                    display: flex;
                    flex-direction: column;
                    justify-content: flex-start;
                    align-items: stretch;
                    overflow-y: auto;
                    overflow-x: hidden;
                    scrollbar-width: none;
                    -ms-overflow-style: none;
                }

                .navbar.offcanvas-right::-webkit-scrollbar {
                    width: 0;
                    height: 0;
                }

                body.menu-open .navbar.offcanvas-right {
                    transform: translateX(0);
                }

                .drawer__header {
                    display: flex;
                }

                .navbar.offcanvas-right .drawer__menu {
                    display: flex;
                    flex-direction: column;
                    gap: 6px;
                    margin: 0;
                    padding: 0;
                    width: 100%;
                }

                .menu-link-row {
                    width: 100%;
                    justify-content: space-between;
                    gap: 10px;
                }

                .navbar.offcanvas-right .drawer__menu li {
                    width: 100%;
                    text-align: left;
                }

                .navbar.offcanvas-right .drawer__menu li a {
                    display: block;
                    width: 100%;
                    padding: 12px 10px;
                    font-size: 15px;
                    border-radius: 8px;
                }

                .menu-sublist {
                    position: static;
                    min-width: 100%;
                    margin-top: 8px;
                    border-radius: 14px;
                    opacity: 1;
                    visibility: visible;
                    pointer-events: none;
                    transform: none;
                    max-height: 0;
                    padding-top: 0;
                    padding-bottom: 0;
                    overflow: hidden;
                    transition: max-height 0.2s ease, padding 0.2s ease;
                }

                .menu-item.is-open .menu-sublist {
                    max-height: 320px;
                    padding-top: 8px;
                    padding-bottom: 8px;
                    pointer-events: auto;
                }

                .menu-item.is-open .menu-dropdown-toggle i {
                    transform: rotate(180deg);
                }

                .navbar.offcanvas-right .active-link {
                    background: rgba(255, 255, 255, 0.12);
                }

                .lang-container {
                    width: 100%;
                }

                .lang-toggle {
                    width: 100%;
                    justify-content: space-between;
                    border-radius: 12px;
                    padding: 10px 12px;
                    margin-top: 8px;
                }

                .lang-dropdown-menu {
                    position: static;
                    margin-top: 8px;
                    width: 100%;
                    max-height: 0;
                    padding-top: 0;
                    padding-bottom: 0;
                    border-width: 0;
                    opacity: 1;
                    visibility: visible;
                    pointer-events: none;
                    transform: none;
                    transition: max-height 0.2s ease, padding 0.2s ease, border-width 0.2s ease;
                    box-shadow: none;
                }

                .lang-container.is-open .lang-dropdown-menu {
                    max-height: 160px;
                    padding-top: 8px;
                    padding-bottom: 8px;
                    border-width: 1px;
                    pointer-events: auto;
                }

                .header-auth-actions {
                    flex-direction: row;
                    align-items: center;
                    justify-content: flex-start;
                    width: 100%;
                    max-width: 100%;
                    margin-top: 8px;
                }

                .header-box ul li a.header-auth-user {
                    width: auto;
                    max-width: calc(100vw - 170px);
                }

                .navbar.offcanvas-right .drawer__menu li a.header-auth-user {
                    display: inline-flex;
                    align-items: center;
                    gap: 10px;
                    padding: 0;
                    border-radius: 0;
                    width: auto;
                    max-width: calc(100vw - 170px);
                }

                .header-auth-divider {
                    display: none;
                }

                .header-notification__panel {
                    right: auto;
                    left: 0;
                    width: min(360px, calc(100vw - 36px));
                    padding: 18px;
                }

                .header-notification__item,
                .header-box ul li a.header-notification__item,
                .navbar.offcanvas-right .drawer__menu li a.header-notification__item {
                    padding: 16px;
                    border-radius: 22px;
                    gap: 14px;
                }

                .header-notification__item-icon {
                    width: 38px;
                    height: 38px;
                    border-radius: 12px;
                    font-size: 15px;
                }

                .header-notification__item-content strong {
                    font-size: 12px;
                }

                .header-notification__item-message {
                    font-size: 12px;
                }

                .header-notification__item-head {
                    gap: 12px;
                }

                .header-notification__item-time {
                    font-size: 12px;
                }

                .header-auth-btn {
                    min-height: 42px;
                    min-width: 98px;
                    padding: 0 12px;
                    align-self: center;
                }

                .header-auth-btn-icon {
                    min-width: 42px;
                    width: 42px;
                    padding: 0;
                }

                .header-auth-btn-register {
                    display: inline-flex !important;
                    width: auto !important;
                    align-items: center !important;
                    justify-content: center !important;
                    min-width: 100px !important;
                    max-width: none !important;
                    height: 42px !important;
                    min-height: 42px !important;
                    padding: 0 13px !important;
                    gap: 6px !important;
                    border-radius: 12px !important;
                }

                .header-auth-btn-logout {
                    display: inline-flex !important;
                    width: auto !important;
                    align-items: center !important;
                    justify-content: center !important;
                    min-width: 100px !important;
                    max-width: none !important;
                    height: 42px !important;
                    min-height: 42px !important;
                    padding: 0 13px !important;
                    gap: 6px !important;
                    border-radius: 12px !important;
                    background: #ffffff !important;
                    border: 1px solid #d7e3f0 !important;
                    color: #173f87 !important;
                    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04) !important;
                    text-align: center !important;
                    flex: 0 0 auto !important;
                }

                .scroll-top-btn {
                    right: 16px;
                    bottom: 16px;
                }

                .web-alert {
                    right: 16px;
                    bottom: 16px;
                    width: min(100vw - 20px, 332px);
                    padding: 11px 12px;
                    gap: 10px;
                    border-radius: 18px;
                }

                .web-alert__icon {
                    width: 40px;
                    height: 40px;
                    border-radius: 13px;
                    font-size: 16px;
                }

                .web-alert__title {
                    font-size: 13px;
                }

                .web-alert__message {
                    font-size: 11px;
                }


                .footer-content {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 40px;
                }

                .hero-grid,
                .course-layout,
                .lesson-layout,
                .auth-wrap {
                    grid-template-columns: 1fr;
                }

                .course-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .hero-panel,
                .course-hero,
                .lesson-sidebar,
                .course-sidebar,
                .auth-feature,
                .auth-card {
                    padding: 24px;
                }

                .course-hero__media {
                    height: 300px;
                }

                .split-grid,
                .hero-stat-row {
                    grid-template-columns: 1fr;
                }

                .fo,
                .fo1 {
                    width: 100%;
                    min-width: unset;
                }

                .footer-store-links {
                    max-width: 240px;
                }

                .sol {
                    justify-content: flex-start;
                }
            }

            @media (max-width: 480px) {
                .web-main {
                    padding-top: 84px;
                }

                .brand-logo {
                    gap: 8px;
                }

                .brand-logo__image {
                    height: 48px;
                }

                .brand-logo__text {
                    font-size: 20px;
                }

                .section-head {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .course-grid {
                    grid-template-columns: 1fr;
                }

                footer {
                    padding: 20px 0;
                }

                .footer-content {
                    width: 95%;
                    padding-bottom: 20px;
                    gap: 30px;
                }

                .copyright-text {
                    padding-top: 15px;
                }
            }
            /* White frontend theme overrides */
            :root {
                --footer-bg-color: #ffffff;
                --footer-text-color: #0f172a;
                --footer-link-color: #64748b;
                --footer-link-hover-color: #0f2f57;
                --footer-border-color: #e2e8f0;
                --text: #0f172a;
                --border: #d9e2ec;
            }

            body.web-shell {
                background: #ffffff;
                color: var(--text);
            }

            body.locale-km,
            body.locale-km p,
            body.locale-km label,
            body.locale-km a,
            body.locale-km button,
            body.locale-km summary,
            body.locale-km h1,
            body.locale-km h2,
            body.locale-km h3,
            body.locale-km h4,
            body.locale-km span {
                font-family: 'Noto Sans Khmer', sans-serif;
            }

            .web-main {
                padding: 74px 0 0;
            }

            header {
                background: rgba(255, 255, 255, 0.96);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
                border-bottom: 1px solid #e5edf5;
            }

            .brand-link,
            .logo h3,
            .header-box ul li a {
                color: #0f172a;
            }

            .brand-link span {
                color: #1d4ed8;
            }

            .header-box ul li a:hover,
            .header-box ul li a.active-link {
                background: #eff4fa;
                color: #0f2f57;
                box-shadow: none;
            }

            .menu-dropdown-toggle {
                color: #5b708a;
            }

            .menu-dropdown-toggle:hover {
                background: #eff4fa;
            }

            .menu-sublist,
            .lang-dropdown-menu {
                background: #ffffff;
                border: 1px solid #e5edf5;
                box-shadow: 0 16px 30px rgba(15, 23, 42, 0.08);
            }

            .header-box .menu-sublist li a,
            .header-box .lang-dropdown-menu li a {
                color: #0f172a;
            }

            .header-box .menu-sublist li a:hover,
            .header-box .lang-dropdown-menu li a:hover {
                background: #eff4fa;
                color: #0f2f57;
            }

            .header-auth-btn-login {
                background: #ffffff;
                border: 1px solid #d7e3f0;
                color: #0f172a;
                box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04);
            }

            .header-auth-btn-login:hover {
                background: #eff4fa;
            }

            .header-auth-btn-logout {
                background: #ffffff;
                border: 1px solid #d7e3f0;
                color: #0f172a;
                box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04);
            }

            .header-auth-btn-logout:hover {
                background: #eff4fa;
            }

            .header-auth-btn-notification {
                background: #ffffff;
                border: 1px solid #d7e3f0;
                color: #0f172a;
                box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04);
            }

            .header-auth-btn-notification:hover,
            .header-notification.is-open .header-auth-btn-notification {
                background: #eff4fa;
            }

            .header-auth-btn-register {
                background: linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
                border: 1px solid #cfe0f4;
                color: #0f172a;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
                min-width: 156px;
                min-height: 56px;
                padding: 0 22px;
                gap: 12px;
                border-radius: 22px;
            }

            .header-auth-btn-register:hover {
                background: linear-gradient(180deg, #fbfdff 0%, #edf4fc 100%);
                border-color: #c8dbf1;
                color: #0f172a;
                box-shadow: 0 14px 28px rgba(15, 23, 42, 0.06);
            }

            .lang-toggle {
                background: #ffffff;
                border: 1px solid #d7e3f0;
                color: #0f172a;
                box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04);
            }

            .lang-toggle:hover {
                border-color: #c4d7eb;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
            }

            .lang-flag {
                border-color: #dbe6f1;
            }

            .mobile-menu-icon {
                color: #0f172a;
            }

            body.menu-open .web-drawer-backdrop {
                background: rgba(15, 23, 42, 0.3);
            }

            footer {
                margin-top: 56px;
                background: #ffffff;
                color: var(--footer-text-color);
                box-shadow: none;
                border-top: 0;
            }

            .footer-content {
                border-bottom: 1px solid var(--footer-border-color);
            }

            .fo h3,
            .fo1 h3 {
                color: #0f172a;
            }

            .fo p,
            .fo1 li a,
            .copyright-text,
            .contact-info-footer a {
                color: #64748b;
            }

            .fo1 li a:hover,
            .contact-info-footer a:hover {
                color: #0f2f57;
            }

            .sol {
                gap: 10px;
            }

            .sol li a {
                border: 1px solid #d7e3f0;
                box-shadow: 0 10px 20px rgba(15, 23, 42, 0.05);
            }

            .sol li a i {
                color: #ffffff !important;
            }

            .hero-panel {
                background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
                border: 1px solid #e5edf5;
                box-shadow: 0 20px 44px rgba(15, 23, 42, 0.06);
            }

            .hero-kicker,
            .chip {
                background: #eff4fa;
                border: 1px solid #dbe6f1;
                color: #1e3a5f;
            }

            .hero-title,
            .section-title,
            .course-card__title,
            .course-hero__title,
            .lesson-title,
            .auth-title,
            .info-title {
                color: #0f172a;
            }

            .hero-copy,
            .section-copy,
            .course-card__copy,
            .course-hero__copy,
            .lesson-copy,
            .info-copy,
            .auth-copy,
            .auth-help,
            .auth-switch,
            .empty-state {
                color: #64748b;
            }

            .section-kicker {
                color: #2563eb;
            }

            .glass-panel,
            .split-card,
            .hero-card-mini,
            .hero-stat,
            .course-card,
            .lesson-item,
            .auth-input,
            .empty-state {
                background: #ffffff;
                border-color: #e5edf5;
                box-shadow: 0 14px 28px rgba(15, 23, 42, 0.05);
            }

            .course-card {
                background: #1b2d41;
                border-color: #214768;
                box-shadow: 0 16px 30px rgba(15, 23, 42, 0.1);
            }

            .course-card__copy,
            .course-card__footer {
                color: rgba(230, 236, 247, 0.78);
            }

            .course-card__meta {
                color: #9dc4ff;
            }

            .course-card__footer {
                border-top-color: rgba(255, 255, 255, 0.08);
            }

            .detail-badge,
            .lesson-item {
                background: #f8fafc;
                color: #0f172a;
            }

            .lesson-item:hover,
            .lesson-item.is-active {
                background: #eaf2fb;
                border-color: #cddff2;
            }

            .lesson-item__top {
                color: #4f6b8b;
            }

            .lesson-item__title {
                color: #0f172a;
            }

            .lesson-item__copy {
                color: #64748b;
            }

            .web-btn-primary {
                background: #1d4ed8;
                box-shadow: 0 12px 24px rgba(29, 78, 216, 0.16);
            }

            .web-btn-secondary {
                color: #0f2f57;
                background: #eff4fa;
                border: 1px solid #dbe6f1;
            }

            .auth-input {
                color: #0f172a;
            }

            .auth-field label {
                color: #0f172a;
            }

            @media (max-width: 992px) {
                .navbar.offcanvas-right {
                    background: #ffffff;
                    box-shadow: -8px 0 20px rgba(15, 23, 42, 0.12);
                }

                .drawer__back {
                    background: #eff4fa;
                    color: #0f172a;
                }

                .drawer__back:hover {
                    background: #e4edf7;
                }

                .navbar.offcanvas-right .active-link {
                    background: #eff4fa;
                }
            }

            /* Keep the lesson order control shared across course learning pages. */
            .lesson-sort-toggle {
                min-height: 34px;
                padding: 6px 11px;
                border: 1px solid #d7e3f0;
                border-radius: 999px;
                background: #f8fafc;
                color: #475569;
                font: inherit;
                font-size: 12px;
                font-weight: 700;
                white-space: nowrap;
                cursor: pointer;
                transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, transform 0.3s ease;
            }

            .lesson-sort-toggle:hover {
                background: #eaf2fb;
                color: #0f2f57;
                transform: translateY(-1px);
            }

            /* Provide one persistent light and dark theme for all shared web pages. */
            .web-theme-toggle {
                width: 42px;
                height: 42px;
                padding: 0;
                border: 1px solid #d7e3f0;
                border-radius: 999px;
                background: #ffffff;
                color: #0f172a;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04);
                transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, transform 0.3s ease;
            }

            .web-theme-toggle:hover {
                background: #eff4fa;
                transform: translateY(-1px);
            }

            .web-theme-toggle i {
                transition: transform 0.3s ease;
            }

            html[data-web-theme='dark'] {
                color-scheme: dark;
                --footer-bg-color: #0e1113;
                --footer-text-color: #f8fafc;
                --footer-link-color: #cbd5e1;
                --footer-link-hover-color: #ffffff;
                --footer-border-color: #26313a;
                --text: #f8fafc;
                --border: #26313a;
            }

            html[data-web-theme='dark'] body.web-shell,
            html[data-web-theme='dark'] body.web-shell .web-main {
                background: #000000 !important;
                color: #f8fafc;
            }

            html[data-web-theme='dark'] body.web-shell header,
            html[data-web-theme='dark'] body.web-shell footer,
            html[data-web-theme='dark'] body.web-shell .navbar.offcanvas-right,
            html[data-web-theme='dark'] body.web-shell .menu-sublist,
            html[data-web-theme='dark'] body.web-shell .lang-dropdown-menu,
            html[data-web-theme='dark'] body.web-shell .header-notification__panel {
                background: #0e1113 !important;
                border-color: #26313a !important;
                color: #f8fafc !important;
            }

            /* Let the desktop navigation inherit the transitioning header surface instead of flashing as a black block. */
            @media (min-width: 993px) {
                html[data-web-theme='dark'] body.web-shell .navbar.offcanvas-right {
                    background: transparent !important;
                    border-color: transparent !important;
                    box-shadow: none !important;
                }
            }

            /* Keep the profile-to-notification divider visible in dark mode. */
            html[data-web-theme='dark'] body.web-shell .header-auth-divider {
                background: #64748b;
            }

            html[data-web-theme='dark'] body.web-shell .web-main :where(
                [class$='-card'],
                [class*='-card '],
                [class$='-box'],
                [class*='-box '],
                [class$='-panel'],
                [class*='-panel '],
                [class$='-drawer'],
                [class*='-drawer '],
                [class$='-dropdown'],
                [class*='-dropdown '],
                [class$='-toolbar'],
                [class*='-toolbar '],
                [class$='-hero'],
                [class*='-hero ']
            ),
            html[data-web-theme='dark'] body.web-shell .web-main :where(
                .home-hero__window,
                .home-hero__floating,
                .home-notice-popup,
                .home-featured-card__media,
                .home-featured-empty,
                .home-why-item,
                .faq-item,
                .faq-toggle__icon,
                .technology-empty,
                .tech-detail-item,
                .shop-detail-primary,
                .shop-detail-payments,
                .shop-detail-client-item,
                .shop-detail-nav,
                .shop-detail-thumb,
                .shop-detail-select,
                .shop-detail-qty,
                .shop-detail-download,
                .shop-detail-share a,
                .shop-detail-pay-card--button,
                .shop-detail-pay-icon,
                .shop-detail-pay-arrow,
                .shop-khqr-summary,
                .shop-khqr-modal__download,
                .shop-search,
                .shop-search input,
                .shop-category-mobile select,
                .shop-category-chip,
                .shop-card__body,
                .shop-card__save,
                .shop-card__favorite,
                .shop-empty,
                .shop-modal__dialog,
                .shop-modal__close,
                .shop-gallery-main,
                .shop-gallery-thumb,
                .shop-modal__meta,
                .shop-cart-rail__btn,
                .shop-favorite-card__media,
                .shop-cart-empty,
                .shop-cart-item,
                .shop-cart-checkout-item,
                .shop-cart-item__media,
                .shop-cart-qty,
                .shop-cart-drawer__foot,
                .shop-cart-checkout-summary,
                .profile-order,
                .profile-order__course,
                .profile-stat-item,
                .profile-library-item,
                .profile-side-link,
                .profile-tabs,
                .profile-field input,
                .profile-field select,
                .profile-empty,
                .profile-errors,
                .checkout-payments,
                .checkout-pay-card--button,
                .checkout-pay-icon,
                .checkout-pay-status,
                .checkout-pay-arrow,
                .khqr-official-card__body,
                .khqr-modal__download,
                .course-toolbar-trigger,
                .course-toolbar-dropdown,
                .web-page-btn,
                .learning-action-chip,
                .comment-composer,
                .comment-send-btn,
                .comment-empty,
                .comment-action-btn,
                .comment-edit-form textarea,
                .resource-open-btn,
                .comment-item,
                .lesson-chip,
                .lesson-comment-area,
                .lesson-comment-send,
                .lesson-comment-empty,
                .lesson-comment-action-btn,
                .lesson-comment-edit-form textarea,
                .lesson-resource-open-btn,
                .lesson-comment-item,
                .lesson-list-row,
                .learning-lesson-item,
                .resource-item,
                .lesson-resource-item
            ) {
                background: #0e1113 !important;
                background-image: none !important;
                border-color: #26313a !important;
                box-shadow: none;
            }

            html[data-web-theme='dark'] body.web-shell .web-main :where(input, textarea, select, option, summary),
            html[data-web-theme='dark'] body.web-shell :where(
                .lang-toggle,
                .header-auth-btn,
                .header-auth-user,
                .web-theme-toggle,
                .drawer__back,
                .lesson-sort-toggle
            ) {
                background: #0e1113 !important;
                border-color: #26313a !important;
                color: #f8fafc !important;
            }

            /* Keep the profile control transparent while the header changes theme to prevent a dark rectangle flash. */
            html[data-web-theme='dark'] body.web-shell .header-auth-user,
            html[data-web-theme='dark'] body.web-shell .header-auth-user:hover,
            html[data-web-theme='dark'] body.web-shell .header-profile__toggle {
                background: transparent !important;
                background-image: none !important;
                border-color: transparent !important;
                box-shadow: none !important;
            }

            html[data-web-theme='dark'] body.web-shell .web-main *,
            html[data-web-theme='dark'] body.web-shell :where(
                .brand-link,
                .brand-logo__text,
                .brand-logo__text span,
                .logo h3,
                .header-box ul li a,
                .menu-dropdown-toggle,
                .mobile-menu-icon,
                .fo h3,
                .fo1 h3,
                .fo p,
                .fo1 li a,
                .copyright-text,
                .contact-info-footer a
            ) {
                color: #f8fafc !important;
            }

            /* Preserve the blue Course word in the brand while Tech stays readable in dark mode. */
            html[data-web-theme='dark'] body.web-shell .brand-logo__text span {
                color: #2563eb !important;
            }

            /* Keep notification cards black and readable in dark mode. */
            html[data-web-theme='dark'] body.web-shell .header-notification__item,
            html[data-web-theme='dark'] body.web-shell .header-box ul li a.header-notification__item,
            html[data-web-theme='dark'] body.web-shell .navbar.offcanvas-right .drawer__menu li a.header-notification__item,
            html[data-web-theme='dark'] body.web-shell .header-notification__item:hover,
            html[data-web-theme='dark'] body.web-shell .header-notification__item.is-unread {
                background: #000000 !important;
                background-image: none !important;
                border-color: #26313a !important;
            }

            /* Keep notification labels visible on the black card background. */
            html[data-web-theme='dark'] body.web-shell .header-notification__panel-head strong,
            html[data-web-theme='dark'] body.web-shell .header-notification__item-content strong {
                color: #f8fafc !important;
            }

            html[data-web-theme='dark'] body.web-shell .header-notification__panel-head span,
            html[data-web-theme='dark'] body.web-shell .header-notification__item-message,
            html[data-web-theme='dark'] body.web-shell .header-notification__item-time {
                color: #cbd5e1 !important;
            }

            /* Keep flash and dynamic alert messages readable against the requested dark surface. */
            html[data-web-theme='dark'] body.web-shell .web-alert {
                background: #0e1113 !important;
                background-image: none !important;
                border-color: #334155 !important;
                color: #f8fafc !important;
                box-shadow: 0 16px 34px rgba(0, 0, 0, 0.38);
            }

            html[data-web-theme='dark'] body.web-shell .web-alert__icon {
                background: #171c20 !important;
                box-shadow: none;
            }

            html[data-web-theme='dark'] body.web-shell .web-alert__title {
                color: #ffffff !important;
            }

            html[data-web-theme='dark'] body.web-shell .web-alert__message {
                color: #d7dee7 !important;
            }

            html[data-web-theme='dark'] body.web-shell .web-alert__close {
                color: #cbd5e1 !important;
            }

            html[data-web-theme='dark'] body.web-shell .web-alert__close:hover {
                background: #20272d !important;
                color: #ffffff !important;
            }

            /* Keep dark text and icons readable when a badge keeps its light background. */
            html[data-web-theme='dark'] body.web-shell .web-main :where(
                .hero-kicker,
                .chip,
                .home-hero__eyebrow,
                .home-hero__button.is-secondary,
                .home-hero__highlight-icon,
                .home-hero__floating-icon,
                .home-why-card__icon,
                .technology-card__icon,
                .home-featured-card__price,
                .course-price-badge,
                .shop-card__badge,
                .shop-detail-badge,
                .checkout-pill,
                .learning-lesson-badge,
                .lesson-list-badge,
                .resource-badge,
                .lesson-resource-badge,
                .resource-item__icon,
                .lesson-resource-item__icon
            ),
            html[data-web-theme='dark'] body.web-shell .web-main :where(
                .hero-kicker,
                .chip,
                .home-hero__eyebrow,
                .home-hero__button.is-secondary,
                .home-hero__highlight-icon,
                .home-hero__floating-icon,
                .home-why-card__icon,
                .technology-card__icon,
                .home-featured-card__price,
                .course-price-badge,
                .shop-card__badge,
                .shop-detail-badge,
                .checkout-pill,
                .learning-lesson-badge,
                .lesson-list-badge,
                .resource-badge,
                .lesson-resource-badge,
                .resource-item__icon,
                .lesson-resource-item__icon
            ) * {
                color: #173f87 !important;
            }

            html[data-web-theme='dark'] body.web-shell .web-main :where(
                .course-price-badge.is-free,
                .shop-card__badge.is-stock,
                .shop-detail-badge.is-stock
            ),
            html[data-web-theme='dark'] body.web-shell .web-main :where(
                .course-price-badge.is-free,
                .shop-card__badge.is-stock,
                .shop-detail-badge.is-stock
            ) * {
                color: #157347 !important;
            }

            html[data-web-theme='dark'] body.web-shell .web-main :where(
                .shop-card__badge.is-out,
                .shop-detail-badge.is-out
            ),
            html[data-web-theme='dark'] body.web-shell .web-main :where(
                .shop-card__badge.is-out,
                .shop-detail-badge.is-out
            ) * {
                color: #b4233f !important;
            }

            /* Preserve useful product and price colors on dark shop cards. */
            html[data-web-theme='dark'] body.web-shell .web-main :where(
                .shop-card__sale,
                .shop-detail-sale
            ) {
                color: #fb7185 !important;
            }

            html[data-web-theme='dark'] body.web-shell .web-main :where(
                .shop-card__cost,
                .shop-detail-cost
            ) {
                color: #cbd5e1 !important;
            }

            html[data-web-theme='dark'] body.web-shell .web-main :where(
                .shop-card__category,
                .shop-card__meta,
                .shop-card__fee-description
            ) {
                color: #b8c4d4 !important;
            }

            html[data-web-theme='dark'] body.web-shell .web-main .shop-card__save {
                color: #93c5fd !important;
                border-color: #3b82f6 !important;
            }

            /* Keep course dropdown states readable against the dark menu. */
            html[data-web-theme='dark'] body.web-shell .web-main .course-toolbar-option:hover,
            html[data-web-theme='dark'] body.web-shell .web-main .course-toolbar-option.is-active {
                background: #182026 !important;
                color: #f8fafc !important;
            }

            html[data-web-theme='dark'] body.web-shell .web-main .course-toolbar-option:hover *,
            html[data-web-theme='dark'] body.web-shell .web-main .course-toolbar-option.is-active * {
                color: #f8fafc !important;
            }

            /* Keep the about timeline wrapper transparent so its animated line stays visible. */
            html[data-web-theme='dark'] body.web-shell .web-main .about-profile__timeline-item,
            html[data-web-theme='dark'] body.web-shell .web-main .about-profile__timeline-card {
                background: transparent !important;
                border-color: transparent !important;
                box-shadow: none !important;
            }

            /* Remove the year box while keeping its timeline label readable. */
            html[data-web-theme='dark'] body.web-shell .web-main .about-profile__timeline-card::before {
                background: transparent !important;
                color: #f8fafc !important;
                box-shadow: none !important;
            }

            /* Keep the platform purpose and certificate boxes as dark surfaces. */
            html[data-web-theme='dark'] body.web-shell .web-main .about-profile__summary-card {
                background: #0e1113 !important;
                border-color: #26313a !important;
            }

            html[data-web-theme='dark'] body.web-shell .web-main .about-profile__timeline::before {
                background: #405064 !important;
            }

            html[data-web-theme='dark'] body.web-shell .web-main .about-profile__experience,
            html[data-web-theme='dark'] body.web-shell .web-main .about-profile__experience * {
                color: #1d4ed8 !important;
            }

            html[data-web-theme='dark'] body.web-shell .web-main :where(.policy-page, .terms-page) {
                background: #000000 !important;
            }

            html[data-web-theme='dark'] body.web-shell .web-main :where(input, textarea)::placeholder {
                color: #94a3b8 !important;
            }

            html[data-web-theme='dark'] body.web-shell :where(
                .header-box ul li a:hover,
                .header-box ul li a.active-link,
                .header-auth-btn:hover,
                .lang-toggle:hover,
                .web-theme-toggle:hover,
                .drawer__back:hover,
                .lesson-sort-toggle:hover
            ) {
                background: #182026 !important;
                border-color: #33414c !important;
            }

            body.web-shell,
            body.web-shell .web-main,
            body.web-shell header,
            body.web-shell footer {
                transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
            }

            /* Slide the notification panel down from the header without changing its contents. */
            .header-notification__panel { opacity: 0; transform: translateY(-12px); transition: opacity .24s ease, transform .24s ease; }
            .header-notification.is-open .header-notification__panel { opacity: 1; transform: translateY(0); }

            /* Keep profile and logout actions in one compact, keyboard-friendly menu. */
            .header-profile { position: relative; min-width: 0; flex-shrink: 0; }
            .header-box ul li .header-profile__toggle { display: inline-flex; align-items: center; gap: 10px; max-width: 275px; min-height: 44px; padding: 0; border: 0; background: transparent; cursor: pointer; font: inherit; }
            .header-profile__toggle:hover { opacity: .9; }
            .header-profile__caret { color: #64748b; font-size: 11px; transition: transform .24s ease; }
            .header-profile.is-open .header-profile__caret { transform: rotate(180deg); }
            .header-profile__menu { position: absolute; top: calc(100% + 12px); left: 0; z-index: 36; width: 220px; padding: 8px; border: 1px solid #d7e3f0; border-radius: 14px; background: #fff; box-shadow: 0 18px 40px rgba(15,23,42,.14); opacity: 0; transform: translateY(-10px); transition: opacity .24s ease, transform .24s ease; }
            .header-profile__menu[hidden] { display: none; }
            .header-profile.is-open .header-profile__menu { opacity: 1; transform: translateY(0); }
            .header-box ul li .header-profile__menu a, .header-box ul li .header-profile__menu button { display: flex; align-items: center; gap: 10px; width: 100%; min-height: 42px; padding: 9px 12px; border: 0; border-radius: 9px; background: transparent; color: #173f87; font: inherit; font-size: 13px; font-weight: 700; text-align: left; text-decoration: none; cursor: pointer; }
            .header-profile__menu form { margin: 0; }
            .header-box ul li .header-profile__menu a:hover, .header-box ul li .header-profile__menu button:hover { background: #eff6ff; }
            html[data-web-theme='dark'] body.web-shell .header-profile__menu { background: #0e1113; border-color: #26313a; }
            html[data-web-theme='dark'] body.web-shell .header-profile__menu :is(a, button) { color: #f8fafc; }
            html[data-web-theme='dark'] body.web-shell .header-profile__menu :is(a, button):hover { background: #182026; }

            /* Match the reference pagination while retaining normal server-side links. */
            .web-pagination-wrap, .web-pagination-pages { gap: 10px; }
            .web-pagination-wrap .web-page-btn { min-width: 44px; height: 44px; border-radius: 4px; box-shadow: none; }
            .web-pagination-wrap .web-page-btn.is-active { background: #343bb4; border-color: #343bb4; color: #fff; }
            .web-pagination-wrap .web-page-btn.is-muted { border-color: transparent; background: transparent; color: #334155; }
            /* Preserve the active page highlight above the existing dark-theme control rule. */
            html[data-web-theme='dark'] body.web-shell .web-main .web-page-btn.is-active { background: #343bb4 !important; border-color: #343bb4 !important; color: #fff !important; }
            html[data-web-theme='dark'] body.web-shell .web-main .web-page-btn.is-muted { background: transparent !important; border-color: transparent !important; color: #cbd5e1 !important; }

            /* Show short social names on hover and keyboard focus. */
            .sol li { position: relative; }
            .sol li a[data-tooltip]::after { content: attr(data-tooltip); position: absolute; left: 50%; bottom: calc(100% + 9px); transform: translate(-50%, 5px); padding: 5px 9px; border-radius: 6px; background: #172033; color: #fff; font-size: 11px; font-weight: 700; white-space: nowrap; opacity: 0; pointer-events: none; transition: opacity .18s ease, transform .18s ease; z-index: 3; }
            .sol li a[data-tooltip]:is(:hover, :focus-visible)::after { opacity: 1; transform: translate(-50%, 0); }

            /* Present unavailable app badges as a lightweight payment-style announcement. */
            .footer-store-links button { display: inline-flex; padding: 0; border: 0; background: transparent; cursor: pointer; transition: transform .2s ease, opacity .2s ease; }
            .footer-store-links button:hover { transform: translateY(-2px); opacity: .92; }
            .footer-store-modal[hidden] { display: none; }
            .footer-store-modal { position: fixed; inset: 0; z-index: 1500; display: grid; place-items: center; padding: 20px; background: rgba(15,23,42,.7); opacity: 0; transition: opacity .22s ease; }
            .footer-store-modal.is-open { opacity: 1; }
            .footer-store-modal__card { position: relative; width: min(430px, 100%); padding: 42px 30px 30px; border-radius: 20px; background: #fff; color: #172033; text-align: center; transform: translateY(14px) scale(.97); transition: transform .22s ease; box-shadow: 0 24px 50px rgba(0,0,0,.18); }
            .footer-store-modal.is-open .footer-store-modal__card { transform: translateY(0) scale(1); }
            .footer-store-modal__close { position: absolute; top: 12px; right: 14px; border: 0; background: transparent; color: #64748b; font-size: 19px; cursor: pointer; }
            .footer-store-modal__icon { display: inline-grid; place-items: center; width: 76px; height: 76px; border-radius: 50%; background: #e8f1ff; color: #2563eb; font-size: 31px; }
            .footer-store-modal__card h2 { margin: 18px 0 6px; font-size: 1.7rem; }
            .footer-store-modal__card p { margin: 0 0 22px; color: #64748b; }
            .footer-store-modal__done { width: 100%; min-height: 45px; border: 0; border-radius: 9px; background: #2563eb; color: #fff; font: inherit; font-weight: 700; cursor: pointer; }
            /* Keep the profile menu inside the mobile drawer instead of floating over it. */
            @media (max-width: 992px) { .header-profile { width: min(100%, 300px); } .header-profile__menu { position: static; width: 100%; margin-top: 8px; box-shadow: none; } .header-box ul li .header-profile__toggle { max-width: calc(100vw - 140px); } .navbar.offcanvas-right .drawer__menu li .header-profile__menu a { display: flex; align-items: center; width: 100%; padding: 9px 12px; } }
            @media (prefers-reduced-motion: reduce) { .header-notification__panel, .header-profile__menu, .header-profile__caret, .footer-store-modal, .footer-store-modal__card, .sol li a[data-tooltip]::after { transition-duration: .01ms; } }

            @media (max-width: 992px) {
                .theme-item,
                .web-theme-toggle {
                    width: 100%;
                }

                .web-theme-toggle {
                    border-radius: 12px;
                }
            }
        </style>
        @stack('web_styles')
    </head>
    <body class="web-shell antialiased {{ app()->getLocale() === 'km' ? 'locale-km' : 'locale-en' }}">
        <div class="web-drawer-backdrop" data-web-menu-close></div>

        @include('web.components.header')

        <main class="web-main">
            <div class="web-container">
                @include('web.components.alert')
                @yield('content')
            </div>
        </main>

        <button type="button" class="scroll-top-btn" data-scroll-top aria-label="Scroll to top">
            <i class="fa-solid fa-arrow-up"></i>
        </button>

        {{-- Shared loading overlay covers slow page changes and async actions. --}}
        <div class="web-loading-indicator" data-web-loading aria-live="polite" aria-hidden="true">
            <div class="web-loading-indicator__box">
                <div class="web-loading-indicator__cube-grid" aria-hidden="true">
                    <div class="web-loading-indicator__cube web-loading-indicator__cube--1"></div>
                    <div class="web-loading-indicator__cube web-loading-indicator__cube--2"></div>
                    <div class="web-loading-indicator__cube web-loading-indicator__cube--3"></div>
                    <div class="web-loading-indicator__cube web-loading-indicator__cube--4"></div>
                    <div class="web-loading-indicator__cube web-loading-indicator__cube--5"></div>
                    <div class="web-loading-indicator__cube web-loading-indicator__cube--6"></div>
                    <div class="web-loading-indicator__cube web-loading-indicator__cube--7"></div>
                    <div class="web-loading-indicator__cube web-loading-indicator__cube--8"></div>
                    <div class="web-loading-indicator__cube web-loading-indicator__cube--9"></div>
                </div>
                <span class="web-loading-indicator__text">{{ __('Loading...') }}</span>
            </div>
        </div>

        @include('web.components.footer')

        {{-- Load the parallax helper from the local app domain so CSP does not block it. --}}
        <script src="{{ asset('vendor/rellax.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (Array.isArray(window.__techCourseGa4FlashEvents)) {
                    window.__techCourseGa4FlashEvents.forEach((eventPayload) => {
                        if (!eventPayload || !eventPayload.name) {
                            return;
                        }

                        window.trackEvent(eventPayload.name, eventPayload.params || {});
                    });

                    window.__techCourseGa4FlashEvents = [];
                }

                const body = document.body;
                const root = document.documentElement;
                const header = document.querySelector('header');
                const menuToggle = document.querySelector('[data-web-menu-toggle]');
                const menuClose = document.querySelector('[data-web-menu-close-button]');
                const menuCloseTargets = document.querySelectorAll('[data-web-menu-close]');
                const langWrap = document.querySelector('[data-web-lang]');
                const langToggle = document.querySelector('[data-web-lang-toggle]');
                const notificationWrap = document.querySelector('[data-web-notification]');
                const notificationToggle = document.querySelector('[data-web-notification-toggle]');
                const notificationPanel = document.querySelector('[data-web-notification-panel]');
                const notificationBadge = document.querySelector('[data-web-notification-badge]');
                const profileWrap = document.querySelector('[data-web-profile]');
                const profileToggle = document.querySelector('[data-web-profile-toggle]');
                const profileMenu = document.querySelector('[data-web-profile-menu]');
                const themeToggle = document.querySelector('[data-web-theme-toggle]');
                const scrollTopButton = document.querySelector('[data-scroll-top]');
                const loadingIndicator = document.querySelector('[data-web-loading]');
                const webAlert = document.querySelector('[data-web-alert]');
                const menuItems = document.querySelectorAll('.menu-item.has-submenu');
                let notificationReadRequestSent = false;
                let notificationCloseTimer;
                let profileCloseTimer;
                let lastScrollY = window.scrollY;
                const headerHideOffset = 160;

                // Toggle and remember the visitor's selected web color theme.
                const applyWebTheme = (theme) => {
                    const selectedTheme = theme === 'dark' ? 'dark' : 'light';

                    if (selectedTheme === 'dark') {
                        root.dataset.webTheme = 'dark';
                    } else {
                        delete root.dataset.webTheme;
                    }

                    if (themeToggle) {
                        const opensDarkMode = selectedTheme === 'light';
                        const label = opensDarkMode ? @json(__('Dark mode')) : @json(__('Light mode'));
                        const icon = themeToggle.querySelector('i');

                        themeToggle.setAttribute('aria-label', label);
                        themeToggle.setAttribute('title', label);
                        themeToggle.setAttribute('aria-pressed', selectedTheme === 'dark' ? 'true' : 'false');

                        if (icon) {
                            icon.className = opensDarkMode ? 'fa-solid fa-moon' : 'fa-solid fa-sun';
                        }
                    }
                };

                applyWebTheme(root.dataset.webTheme === 'dark' ? 'dark' : 'light');

                themeToggle?.addEventListener('click', () => {
                    const nextTheme = root.dataset.webTheme === 'dark' ? 'light' : 'dark';
                    applyWebTheme(nextTheme);

                    try {
                        window.localStorage.setItem('techcourse-web-theme', nextTheme);
                    } catch (error) {
                        // Keep the selected theme for this page when storage is unavailable.
                    }
                });

                // Reverse lesson boxes without changing the existing lesson routes or data.
                document.querySelectorAll('[data-lesson-sort-toggle]').forEach((button) => {
                    const list = button.closest('aside')?.querySelector('[data-lesson-sort-list]');

                    if (!list) {
                        return;
                    }

                    button.addEventListener('click', () => {
                        const descending = button.getAttribute('aria-pressed') !== 'true';
                        Array.from(list.children).reverse().forEach((lesson) => list.appendChild(lesson));
                        button.setAttribute('aria-pressed', descending ? 'true' : 'false');
                        button.textContent = descending
                            ? button.dataset.descendingLabel
                            : button.dataset.ascendingLabel;
                    });
                });

                // Keep the page fully frozen while payment and success popups are open.
                window.TechCourseScrollLock = window.TechCourseScrollLock || (() => {
                    let lockCount = 0;
                    let lockedScrollY = 0;

                    return {
                        lock() {
                            lockCount += 1;

                            if (lockCount > 1) {
                                return;
                            }

                            lockedScrollY = window.scrollY || window.pageYOffset || 0;
                            root.classList.add('scroll-locked');
                            body.classList.add('scroll-locked');
                            body.style.top = `-${lockedScrollY}px`;
                        },
                        unlock(force = false) {
                            if (lockCount === 0 && !force) {
                                return;
                            }

                            lockCount = force ? 0 : Math.max(0, lockCount - 1);

                            if (lockCount > 0) {
                                return;
                            }

                            const restoreScrollY = Math.abs(parseInt(body.style.top || '0', 10)) || lockedScrollY;
                            root.classList.remove('scroll-locked');
                            body.classList.remove('scroll-locked');
                            body.style.top = '';
                            lockedScrollY = 0;
                            window.scrollTo(0, restoreScrollY);
                        },
                    };
                })();

                const closeMenu = () => {
                    body.classList.remove('menu-open');
                };

                // Let the notification panel finish its upward closing animation.
                const closeNotificationPanel = () => {
                    if (!notificationWrap || !notificationToggle || !notificationPanel) {
                        return;
                    }

                    notificationWrap.classList.remove('is-open');
                    notificationToggle.setAttribute('aria-expanded', 'false');
                    window.clearTimeout(notificationCloseTimer);
                    notificationCloseTimer = window.setTimeout(() => { notificationPanel.hidden = true; }, 240);
                };

                // Close the profile menu on outside click or Escape without changing logout's POST flow.
                const closeProfileMenu = () => {
                    if (!profileWrap || !profileToggle || !profileMenu) return;
                    profileWrap.classList.remove('is-open');
                    profileToggle.setAttribute('aria-expanded', 'false');
                    window.clearTimeout(profileCloseTimer);
                    profileCloseTimer = window.setTimeout(() => { profileMenu.hidden = true; }, 240);
                };

                const markNotificationsAsRead = async () => {
                    if (!notificationToggle || notificationReadRequestSent) {
                        return;
                    }

                    const url = notificationToggle.getAttribute('data-web-notification-read-url');
                    const csrf = notificationToggle.getAttribute('data-web-notification-csrf');

                    if (!url || !csrf) {
                        return;
                    }

                    notificationReadRequestSent = true;

                    try {
                        await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({}),
                        });

                        if (notificationBadge) {
                            notificationBadge.remove();
                        }

                        notificationPanel.querySelectorAll('.header-notification__item.is-unread').forEach((item) => {
                            item.classList.remove('is-unread');
                        });
                    } catch (error) {
                        notificationReadRequestSent = false;
                        console.error(error);
                    }
                };

                const showLoading = () => {
                    if (!loadingIndicator) {
                        return;
                    }

                    loadingIndicator.classList.add('is-visible');
                    loadingIndicator.setAttribute('aria-hidden', 'false');
                };

                const hideLoading = () => {
                    if (!loadingIndicator) {
                        return;
                    }

                    loadingIndicator.classList.remove('is-visible');
                    loadingIndicator.setAttribute('aria-hidden', 'true');
                };

                // Expose one small loader helper so page scripts can show the same loading UI.
                window.TechCoursePageLoader = {
                    show: showLoading,
                    hide: hideLoading,
                };

                // Keep card skeletons reusable for initial and dynamically loaded cards.
                const initializeCardSkeletons = (container = document) => {
                    container.querySelectorAll('[data-skeleton-card]:not([data-skeleton-ready])').forEach((card) => {
                        card.setAttribute('data-skeleton-ready', 'true');
                        card.classList.add('is-skeleton');

                        const media = Array.from(card.querySelectorAll('img'));
                        if (!media.length) {
                            window.setTimeout(() => card.classList.remove('is-skeleton'), 180);
                            return;
                        }

                        let loadedCount = 0;
                        const finishCard = () => {
                            loadedCount += 1;

                            if (loadedCount >= media.length) {
                                window.setTimeout(() => card.classList.remove('is-skeleton'), 120);
                            }
                        };

                        media.forEach((image) => {
                            if (image.complete) {
                                finishCard();
                                return;
                            }

                            image.addEventListener('load', finishCard, { once: true });
                            image.addEventListener('error', finishCard, { once: true });
                        });
                    });
                };

                window.TechCourseCardSkeleton = {
                    init: initializeCardSkeletons,
                };
                initializeCardSkeletons();

                // Initialize light parallax only on marked decorative homepage elements.
                if (typeof window.Rellax === 'function' && document.querySelector('.rellax')) {
                    new window.Rellax('.rellax', {
                        center: false,
                        round: true,
                        vertical: true,
                    });
                }

                const closeAlert = () => {
                    if (!webAlert || webAlert.classList.contains('is-leaving')) {
                        return;
                    }

                    webAlert.classList.add('is-leaving');

                    window.setTimeout(() => {
                        webAlert.remove();
                    }, 280);
                };

                if (webAlert) {
                    const closeButton = webAlert.querySelector('[data-web-alert-close]');

                    if (closeButton) {
                        closeButton.addEventListener('click', closeAlert);
                    }

                    window.setTimeout(closeAlert, 4600);
                }

                if (menuToggle) {
                    menuToggle.addEventListener('click', () => {
                        body.classList.add('menu-open');
                    });
                }

                if (menuClose) {
                    menuClose.addEventListener('click', closeMenu);
                }

                menuCloseTargets.forEach((item) => {
                    item.addEventListener('click', closeMenu);
                });

                menuItems.forEach((item) => {
                    const toggle = item.querySelector('.menu-dropdown-toggle');

                    toggle?.addEventListener('click', (event) => {
                        if (window.innerWidth > 992) {
                            return;
                        }

                        event.preventDefault();
                        event.stopPropagation();

                        const isOpen = item.classList.toggle('is-open');
                        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    });
                });

                if (langWrap && langToggle) {
                    langToggle.addEventListener('click', (event) => {
                        event.stopPropagation();
                        const isOpen = langWrap.classList.toggle('is-open');
                        langToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    });

                    document.addEventListener('click', (event) => {
                        if (!langWrap.contains(event.target)) {
                            langWrap.classList.remove('is-open');
                            langToggle.setAttribute('aria-expanded', 'false');
                        }
                    });

                    langWrap.querySelectorAll('a').forEach((link) => {
                        link.addEventListener('click', () => {
                            langWrap.classList.remove('is-open');
                            langToggle.setAttribute('aria-expanded', 'false');
                        });
                    });
                }

                if (notificationWrap && notificationToggle && notificationPanel) {
                    notificationToggle.addEventListener('click', async (event) => {
                        event.stopPropagation();
                        if (notificationWrap.classList.contains('is-open')) { closeNotificationPanel(); return; }
                        closeProfileMenu();
                        window.clearTimeout(notificationCloseTimer);
                        notificationPanel.hidden = false;
                        requestAnimationFrame(() => notificationWrap.classList.add('is-open'));
                        notificationToggle.setAttribute('aria-expanded', 'true');
                        await markNotificationsAsRead();
                    });

                    notificationPanel.addEventListener('click', (event) => {
                        event.stopPropagation();
                    });

                    document.addEventListener('click', (event) => {
                        if (!notificationWrap.contains(event.target)) {
                            closeNotificationPanel();
                        }
                    });
                }

                // Toggle the account dropdown with the same short slide as notifications.
                if (profileWrap && profileToggle && profileMenu) {
                    profileToggle.addEventListener('click', (event) => {
                        event.stopPropagation();
                        if (profileWrap.classList.contains('is-open')) { closeProfileMenu(); return; }
                        closeNotificationPanel();
                        window.clearTimeout(profileCloseTimer);
                        profileMenu.hidden = false;
                        requestAnimationFrame(() => profileWrap.classList.add('is-open'));
                        profileToggle.setAttribute('aria-expanded', 'true');
                    });
                    document.addEventListener('click', (event) => {
                        if (!profileWrap.contains(event.target)) closeProfileMenu();
                    });
                    document.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape' && profileWrap.classList.contains('is-open')) {
                            closeProfileMenu();
                            profileToggle.focus();
                        }
                    });
                }

                if (header) {
                    window.addEventListener('scroll', () => {
                        const currentScrollY = window.scrollY;

                        if (body.classList.contains('menu-open')) {
                            header.classList.remove('header-hidden');
                            lastScrollY = currentScrollY;
                            return;
                        }

                        if (currentScrollY <= headerHideOffset) {
                            header.classList.remove('header-hidden');
                            lastScrollY = currentScrollY;
                            return;
                        }

                        if (currentScrollY > lastScrollY) {
                            header.classList.add('header-hidden');
                        } else {
                            header.classList.remove('header-hidden');
                        }

                        lastScrollY = currentScrollY;
                    }, { passive: true });
                }

                if (scrollTopButton) {
                    const toggleScrollTop = () => {
                        scrollTopButton.classList.toggle('is-visible', window.scrollY > 320);
                    };

                    toggleScrollTop();
                    window.addEventListener('scroll', toggleScrollTop, { passive: true });

                    scrollTopButton.addEventListener('click', () => {
                        closeNotificationPanel();
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth',
                        });
                    });
                }

                document.querySelectorAll('[data-share-url]').forEach((button) => {
                    button.addEventListener('click', async () => {
                        const shareUrl = button.getAttribute('data-share-url');
                        const absoluteUrl = shareUrl ? new URL(shareUrl, window.location.origin).toString() : window.location.href;

                        try {
                            if (navigator.share) {
                                await navigator.share({
                                    title: document.title,
                                    url: absoluteUrl,
                                });

                                return;
                            }

                            if (navigator.clipboard?.writeText) {
                                await navigator.clipboard.writeText(absoluteUrl);
                                window.alert('Link copied successfully.');
                                return;
                            }
                        } catch (error) {
                            console.error(error);
                        }

                        window.prompt('Copy this link:', absoluteUrl);
                    });
                });

                // Show the page loader for internal navigation without changing existing routes.
                document.querySelectorAll('a[href]').forEach((link) => {
                    link.addEventListener('click', (event) => {
                        const href = link.getAttribute('href') || '';
                        const target = link.getAttribute('target');

                        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                            return;
                        }

                        if (!href || href.startsWith('#') || href.startsWith('javascript:')) {
                            return;
                        }

                        if (target && target !== '_self') {
                            return;
                        }

                        const absoluteUrl = new URL(href, window.location.href);
                        const samePageAnchor = absoluteUrl.pathname === window.location.pathname
                            && absoluteUrl.search === window.location.search
                            && absoluteUrl.hash !== '';

                        if (absoluteUrl.origin !== window.location.origin || samePageAnchor || link.hasAttribute('download')) {
                            return;
                        }

                        showLoading();
                    });
                });

                // Apply the same loader to normal form submits that may take time.
                document.querySelectorAll('form').forEach((form) => {
                    form.addEventListener('submit', () => {
                        // Async forms manage their own request state without page navigation.
                        if (form.hasAttribute('data-async-form')) {
                            return;
                        }

                        showLoading();
                    });
                });

                window.addEventListener('pageshow', hideLoading);
                window.addEventListener('popstate', hideLoading);
                window.addEventListener('focus', hideLoading);

            });
        </script>
        @stack('web_scripts')
    </body>
</html>
