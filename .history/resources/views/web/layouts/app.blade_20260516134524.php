<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', 'TechCourse')</title>
        {{-- //logo --}}
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
                scrollbar-width: none;
                -ms-overflow-style: none;
            }

            html::-webkit-scrollbar,
            body::-webkit-scrollbar {
                width: 0;
                height: 0;
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
                padding: 110px 0 0;
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
                padding: 13px 0;
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
                width: 60%;
                justify-content: flex-start;
                min-width: 0;
                gap: 12px;
            }

            .logo h3 {
                margin: 0 40px 0 0;
                cursor: pointer;
                font-weight: bold;
                transition: all 0.3s ease-in-out;
                font-family: var(--font-lato);
                font-size: 20px;
            }

            .logo-icon-box {
                width: 46px;
                height: 46px;
                border-radius: 14px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #0284ff, #0ea5e9);
                box-shadow: 0 10px 20px rgba(2, 123, 255, 0.24);
                color: #fff;
                font-size: 20px;
                flex-shrink: 0;
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

            .header-auth-btn-login {
                background: rgba(255, 255, 255, 0.08);
                border: 1px solid rgba(255, 255, 255, 0.12);
                color: #fff;
            }

            .header-auth-btn-login:hover {
                background: rgba(255, 255, 255, 0.14);
            }

            .header-auth-btn-logout {
                background: transparent;
                border: none;
                color: #173f87;
                box-shadow: none;
            }

            .header-auth-btn-logout:hover {
                background: #f4f8fd;
            }

            .header-auth-btn-register {
                background: linear-gradient(135deg, #027bff, #1498ff);
                border: 1px solid transparent;
                color: #fff;
                box-shadow: 0 10px 18px rgba(2, 123, 255, 0.22);
            }

            .header-auth-btn-register:hover {
                transform: translateY(-1px);
                box-shadow: 0 14px 24px rgba(2, 123, 255, 0.28);
            }

            .header-auth-btn i {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 20px;
                height: 20px;
            }

            .header-auth-btn__label {
                font-size: 13px;
                font-weight: 500;
                line-height: 1;
                white-space: nowrap;
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
                font-size: 28px;
                cursor: pointer;
                color: white;
                z-index: 1000;
                margin-right: 15px;
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

            footer {
                margin-top: 60px;
                background-color: #050c1e;
                color: var(--footer-text-color);
                font-family: var(--font-body);
                padding: 30px 0;
                box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.2);
                border-top: 1px solid #5b6472;
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

                .header-auth-btn {
                    min-height: 42px;
                    min-width: 98px;
                    padding: 0 12px;
                    align-self: center;
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

                .sol {
                    justify-content: flex-start;
                }
            }

            @media (max-width: 480px) {
                .web-main {
                    padding-top: 95px;
                }

                .logo-icon-box {
                    width: 40px;
                    height: 40px;
                    font-size: 18px;
                }

                .logo h3 {
                    margin-right: 8px;
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
                padding: 102px 0 0;
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

            .header-auth-btn-register {
                background: linear-gradient(135deg, #1d8cff, #1570ef);
                border: 1px solid #1570ef;
                color: #fff;
                box-shadow: 0 12px 24px rgba(21, 112, 239, 0.18);
            }

            .header-auth-btn-register:hover {
                transform: translateY(-1px);
                box-shadow: 0 16px 28px rgba(21, 112, 239, 0.28);
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
                border-top: 1px solid #e5edf5;
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

        @include('web.components.footer')

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const body = document.body;
                const header = document.querySelector('header');
                const menuToggle = document.querySelector('[data-web-menu-toggle]');
                const menuClose = document.querySelector('[data-web-menu-close-button]');
                const menuCloseTargets = document.querySelectorAll('[data-web-menu-close]');
                const langWrap = document.querySelector('[data-web-lang]');
                const langToggle = document.querySelector('[data-web-lang-toggle]');
                const menuItems = document.querySelectorAll('.menu-item.has-submenu');
                let lastScrollY = window.scrollY;
                const headerHideOffset = 160;

                const closeMenu = () => {
                    body.classList.remove('menu-open');
                };

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

            });
        </script>
        @stack('web_scripts')
    </body>
</html>
