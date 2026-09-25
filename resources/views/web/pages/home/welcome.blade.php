@extends('web.layouts.app')

@section('title', 'TechCourse')

@php
    $isKhmer = app()->getLocale() === 'km';

    $trackingSectionTitle = $isKhmer
        ? 'ស្ថិតិរហ័សរបស់ Platform'
        : 'Quick Platform Stats';

    $trackingSectionCopy = $isKhmer
        ? 'មើលចំនួនអ្នកប្រើ guest visits វគ្គសិក្សា និងផលិតផលសរុបក្នុងកន្លែងតែមួយ ដើម្បីតាមដានស្ថានភាពទូទៅរបស់ website បានលឿន។'
        : 'See total users, guest visits, courses, and products in one place for a fast overview of your website activity.';
    $heroEyebrow = $isKhmer ? 'Platform រៀន និង Digital Services' : 'Learning Platform And Digital Services';
    $heroTitle = $isKhmer
        ? 'រៀនជំនាញ Tech និងស្វែងរក Web Service ដែលសមនឹងអាជីវកម្មរបស់អ្នក'
        : 'Learn Tech Skills And Find Web Services That Fit Your Business';
    $heroCopy = $isKhmer
        ? 'TechCourse បង្កើតឡើងសម្រាប់អ្នកចង់រៀនជំនាញ tech ជាមួយ course ដែលអាចចាប់ផ្តើមបានលឿន មាន service សម្រាប់ web, mobile app, UI/UX, digital growth ហើយក៏មាន product មួយចំនួនសម្រាប់អ្នកអាចជ្រើសទិញបានក្នុងកន្លែងតែមួយ។'
        : 'TechCourse brings together practical courses, digital services for web and mobile, and selected products you can buy in one place.';
    $heroPrimaryCta = $isKhmer ? 'មើលវគ្គសិក្សា' : 'Browse Courses';
    $heroSecondaryCta = $isKhmer ? 'មើលសេវាកម្ម' : 'View Services';
    $heroHighlights = $isKhmer
        ? [
            ['icon' => 'fa-solid fa-graduation-cap', 'label' => 'វគ្គសិក្សាចាប់ផ្តើមបានលឿន'],
            ['icon' => 'fa-solid fa-laptop-code', 'label' => 'Web និង Mobile Service'],
            ['icon' => 'fa-solid fa-headset', 'label' => 'Support ងាយស្រួលទំនាក់ទំនង'],
        ]
        : [
            ['icon' => 'fa-solid fa-graduation-cap', 'label' => 'Courses you can start quickly'],
            ['icon' => 'fa-solid fa-laptop-code', 'label' => 'Web and mobile services'],
            ['icon' => 'fa-solid fa-headset', 'label' => 'Friendly support flow'],
        ];
    $homeNoticeTitle = $isKhmer ? 'សេចក្ដីជូនដំណឹងសម្រាប់ Test Website' : 'Test Website Notice';
    $homeNoticeCopy = $isKhmer
        ? 'Website នេះសម្រាប់សាកល្បងតែប៉ុណ្ណោះ។ បើមានការទូទាត់ ឬប្រតិបត្តិការណាមួយ យើងអាចមិនទទួលយក complaint ឬ issue ពាក់ព័ន្ធនឹង test transaction ទេ។'
        : 'This website is for testing only. If you make a payment here, we may not accept issues or complaints related to test transactions.';

    $whyChooseTitle = $isKhmer ? 'ហេតុអ្វីជ្រើសយើង' : 'Why Choose Us';
    $whyChooseCopy = $isKhmer
        ? 'ចំណុចសំខាន់ខ្លីៗដែលជួយឲ្យអតិថិជន និងអ្នករៀនទុកចិត្តលើ service និង learning flow របស់យើង។'
        : 'A few clear reasons clients and learners can trust our service and learning flow.';
    $whyChooseItems = $isKhmer
        ? [
            ['icon' => 'fa-solid fa-bolt', 'title' => 'ជំនួយរហ័ស', 'copy' => 'ឆ្លើយតបរហ័ស និងជួយដោះស្រាយបញ្ហាតាមតម្រូវការពិត។'],
            ['icon' => 'fa-solid fa-diagram-project', 'title' => 'រៀនតាម Project ពិត', 'copy' => 'ផ្តោតលើ project និងការអនុវត្តដែលអាចយកទៅប្រើបាន។'],
            ['icon' => 'fa-solid fa-mobile-screen', 'title' => 'ប្រើបានល្អលើ Mobile', 'copy' => 'UI ត្រូវបានរៀបចំឲ្យប្រើងាយលើ mobile និង desktop។'],
            ['icon' => 'fa-solid fa-shield-heart', 'title' => 'សេវាកម្មគួរជាទុកចិត្ត', 'copy' => 'រក្សា flow សាមញ្ញ ស្ថេរភាព និងងាយគ្រប់គ្រងបន្ត។'],
        ]
        : [
            ['icon' => 'fa-solid fa-bolt', 'title' => 'Fast Support', 'copy' => 'Quick response and practical help for real user needs.'],
            ['icon' => 'fa-solid fa-diagram-project', 'title' => 'Real Project Learning', 'copy' => 'Focused on practical projects and useful implementation.'],
            ['icon' => 'fa-solid fa-mobile-screen', 'title' => 'Mobile Friendly', 'copy' => 'Layouts designed to work smoothly on phone and desktop.'],
            ['icon' => 'fa-solid fa-shield-heart', 'title' => 'Trusted Service', 'copy' => 'Simple, stable, and easy-to-maintain product flow.'],
        ];

    $featuredSectionTitle = $isKhmer ? 'វគ្គសិក្សាណែនាំ' : 'Featured Courses';
    $featuredSectionCopy = $isKhmer
        ? 'ជ្រើសមើលវគ្គសិក្សាដែលគួរចាប់ផ្តើម ឬពេញនិយម ដើម្បីចូលមើលបានលឿនពីទំព័រដើម។'
        : 'Open a few highlighted courses quickly from the homepage.';

    $ctaTitle = $isKhmer ? 'ត្រៀមចាប់ផ្តើមជាមួយយើងមែនទេ?' : 'Ready To Start With Us?';
    $ctaCopy = $isKhmer
        ? 'បើអ្នកចង់សាកល្បង service ឬចាប់ផ្តើមរៀន សូមចូលមើល courses ឬទាក់ទងមកយើងដោយផ្ទាល់។'
        : 'If you want to explore our services or begin learning, open the courses page or contact us directly.';
    $ctaPrimary = $isKhmer ? 'មើលវគ្គសិក្សា' : 'View Courses';
    $ctaSecondary = $isKhmer ? 'ទាក់ទងមកយើង' : 'Contact Us';

    $trackingItems = [
        [
            'key' => 'login-users',
            'label' => __('Logged In Users'),
            'value' => number_format((int) ($trackingStats['login_users'] ?? 0)),
            'icon' => 'fa-solid fa-user-check',
            'tone' => 'is-blue',
            'description' => __('Total registered users currently stored in the system database.'),
            'source' => __('Laravel DB'),
        ],
        [
            'key' => 'guest-views',
            'label' => $trackingStats['guest_views_label'] ?? __('Guest Website Views'),
            'value' => number_format((int) ($trackingStats['guest_views'] ?? 0)),
            'live_value' => number_format((int) ($trackingStats['guest_live_views'] ?? 0)),
            'icon' => 'fa-solid fa-eye',
            'tone' => 'is-emerald',
            'description' => $trackingStats['guest_views_description'] ?? __('Guest visitor total from DB tracking table if that table is available.'),
            'source' => $trackingStats['guest_views_source'] ?? __('Laravel DB'),
        ],
        [
            'key' => 'total-courses',
            'label' => __('Total Courses'),
            'value' => number_format((int) ($trackingStats['courses'] ?? 0)),
            'icon' => 'fa-solid fa-book-open-reader',
            'tone' => 'is-violet',
            'description' => __('Total course records currently stored in the database.'),
            'source' => __('Laravel DB'),
        ],
        [
            'key' => 'total-products',
            'label' => __('Total Products'),
            'value' => number_format((int) ($trackingStats['products'] ?? 0)),
            'icon' => 'fa-solid fa-bag-shopping',
            'tone' => 'is-rose',
            'description' => __('Total product records currently stored in the database.'),
            'source' => __('Laravel DB'),
        ],
    ];

    $serviceItems = [
        [
            'icon' => 'fa-solid fa-laptop-code',
            'tone' => 'is-blue',
            'title' => __('Web Design'),
            'description' => __('Beautiful and user-friendly websites that capture attention from the first visit to your website.'),
        ],
        [
            'icon' => 'fa-solid fa-code',
            'tone' => 'is-indigo',
            'title' => __('Web Development'),
            'description' => __('Technology-driven websites with stable performance and speed that help you work more effectively.'),
        ],
        [
            'icon' => 'fa-solid fa-mobile-screen-button',
            'tone' => 'is-violet',
            'title' => __('Mobile App Development'),
            'description' => __('We build high-quality mobile applications for iOS and Android that deliver a memorable experience for users.'),
        ],
        [
            'icon' => 'fa-solid fa-pen-ruler',
            'tone' => 'is-rose',
            'title' => __('UI/UX Design'),
            'description' => __('User interface and user experience designs that are intuitive and visually appealing, helping improve engagement and conversions.'),
        ],
        [
            'icon' => 'fa-solid fa-magnifying-glass-chart',
            'tone' => 'is-emerald',
            'title' => __('SEO Optimization'),
            'description' => __('Improve your website ranking and grow your business through better visibility in search engines.'),
        ],
        [
            'icon' => 'fa-solid fa-tablet-screen-button',
            'tone' => 'is-cyan',
            'title' => __('Responsive Website'),
            'description' => __('Websites designed to fit mobile screens and other devices, improving ease of use and user experience across platforms.'),
        ],
    ];
@endphp

@section('content')
    <style>
        /* Keep the desktop hero at the requested 600px presentation height. */
        .home-hero {
            position: relative;
            overflow: hidden;
            width: 100vw;
            margin-left: calc(50% - 50vw);
            margin-top: 0;
            min-height: 600px;
            padding: 20px 0 46px;
            background:
                linear-gradient(180deg, #f8fbff 0%, #edf4ff 18%, #eef4ff 58%, #f6f9ff 100%);
            border-bottom: 1px solid #e6eef8;
        }

        .home-hero__bg {
            position: absolute;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .home-hero__blob {
            position: absolute;
            border-radius: 999px;
            filter: blur(48px);
            opacity: 0.9;
        }

        .home-hero__blob.is-primary {
            top: -150px;
            right: -110px;
            width: 460px;
            height: 460px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.16), rgba(147, 197, 253, 0.08));
        }

        .home-hero__blob.is-secondary {
            bottom: -130px;
            left: -70px;
            width: 280px;
            height: 280px;
            background: linear-gradient(135deg, rgba(191, 219, 254, 0.18), rgba(125, 211, 252, 0.08));
        }

        /* Drift the hero dots gently using the TechCourse blue in both themes. */
        .home-hero__pattern {
            position: absolute;
            inset: -28px;
            opacity: 0.72;
            background-image: radial-gradient(circle at 1px 1px, rgba(37, 99, 235, 0.22) 1.15px, transparent 1.7px);
            background-size: 28px 28px;
            animation: home-hero-dot-drift 10s ease-in-out infinite alternate;
            will-change: transform;
        }

        @keyframes home-hero-dot-drift {
            from { transform: translate3d(0, 0, 0); }
            to { transform: translate3d(18px, 12px, 0); }
        }

        html[data-web-theme='dark'] .home-hero__pattern {
            background-image: radial-gradient(circle at 1px 1px, rgba(96, 165, 250, 0.44) 1.15px, transparent 1.7px);
        }

        .home-hero__inner {
            position: relative;
            z-index: 1;
            width: min(1180px, calc(100% - 36px));
            margin: 0 auto;
            min-height: calc(600px - 66px);
            display: grid;
            grid-template-columns: minmax(0, 1.04fr) minmax(300px, 0.88fr);
            gap: 28px;
            align-items: center;
        }

        .home-hero__content {
            max-width: 620px;
            width: 100%;
        }

        .home-hero__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 34px;
            padding: 0 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(191, 219, 254, 0.92);
            color: #1d4ed8;
            font-size: 0.76rem;
            font-weight: 700;
            box-shadow: 0 12px 22px rgba(59, 130, 246, 0.08);
        }

        /* Pulse the badge dot softly without moving its text or layout. */
        .home-hero__eyebrow-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #2563eb;
            box-shadow: 0 0 0 6px rgba(37, 99, 235, 0.12);
            animation: home-hero-dot-pulse 2.8s ease-in-out infinite;
        }

        @keyframes home-hero-dot-pulse {
            0%, 100% { box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.12); }
            50% { box-shadow: 0 0 0 9px rgba(37, 99, 235, 0.04); }
        }

        html[data-web-theme='dark'] .home-hero__eyebrow-dot {
            background: #60a5fa;
        }

        @media (prefers-reduced-motion: reduce) {
            .home-hero__pattern,
            .home-hero__eyebrow-dot {
                animation: none;
                will-change: auto;
            }
        }

        .home-hero__title {
            margin: 18px 0 0;
            color: #0f172a;
            font-family: var(--font-lato);
            font-size: clamp(1.15rem, 2vw, 1.7rem);
            line-height: 1.22;
            letter-spacing: -0.02em;
            font-weight: 900;
        }

        .home-hero__copy {
            margin: 16px 0 0;
            max-width: 590px;
            color: #64748b;
            font-size: 0.98rem;
            line-height: 1.85;
        }

        .home-hero__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 24px;
        }

        .home-hero__button {
            min-height: 46px;
            padding: 0 18px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            font-size: 0.84rem;
            font-weight: 800;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .home-hero__button:hover {
            transform: translateY(-2px);
        }

        .home-hero__button.is-primary {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            color: #ffffff;
            box-shadow: 0 18px 32px rgba(37, 99, 235, 0.2);
        }

        .home-hero__button.is-secondary {
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid rgba(191, 219, 254, 0.96);
            color: #1e3a8a;
            box-shadow: 0 12px 22px rgba(15, 23, 42, 0.06);
        }

        .home-hero__highlights {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 24px;
        }

        .home-hero__highlight {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #475569;
            font-size: 0.84rem;
            font-weight: 700;
        }

        .home-hero__highlight-icon {
            width: 32px;
            height: 32px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(219, 234, 254, 0.92);
            color: #2563eb;
            flex-shrink: 0;
        }

        .home-hero__mockup {
            position: relative;
            max-width: 430px;
            width: 100%;
            margin: 0 auto 0 auto;
            transition: transform 0.25s ease-out;
            will-change: transform;
        }

        /* Present the developer portrait on a soft circular TechCourse background. */
        .home-hero__portrait-stage {
            position: relative;
            min-height: 450px;
            border-radius: 46% 54% 48% 52% / 52% 44% 56% 48%;
            transform-style: preserve-3d;
            transition: transform 0.14s ease-out;
            will-change: transform;
            isolation: isolate;
        }

        .home-hero__portrait-stage::before {
            content: '';
            position: absolute;
            top: 48px;
            left: 50%;
            width: 340px;
            height: 340px;
            border-radius: 50%;
            transform: translateX(-50%);
            background:
                radial-gradient(circle at 36% 28%, rgba(255, 255, 255, 0.96) 0 7%, transparent 28%),
                linear-gradient(145deg, #dbeafe 0%, #93c5fd 48%, #2563eb 100%);
            border: 1px solid rgba(255, 255, 255, 0.84);
            box-shadow:
                0 34px 72px rgba(37, 99, 235, 0.25),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
            z-index: -2;
        }

        .home-hero__portrait-stage::after {
            content: '';
            position: absolute;
            top: 27px;
            left: 50%;
            width: 382px;
            height: 382px;
            border-radius: 50%;
            transform: translateX(-50%);
            border: 1px dashed rgba(37, 99, 235, 0.28);
            z-index: -3;
        }

        /* Fade the portrait into the blue circle without exposing its lower caption. */
        .home-hero__portrait-image {
            position: absolute;
            top: 2px;
            left: 50%;
            width: min(400px, 94%);
            height: auto;
            transform: translateX(-50%);
            -webkit-mask-image: linear-gradient(to bottom, #000 0%, #000 66%, transparent 74%);
            mask-image: linear-gradient(to bottom, #000 0%, #000 66%, transparent 74%);
            z-index: 2;
            user-select: none;
            pointer-events: none;
        }

        .home-hero__portrait-glare {
            position: absolute;
            top: 48px;
            left: 50%;
            width: 340px;
            height: 340px;
            border-radius: 50%;
            transform: translateX(-50%);
            opacity: 0;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.35), transparent 52%);
            transition: opacity 0.28s ease;
            pointer-events: none;
            z-index: 3;
        }

        .home-hero__skill {
            position: absolute;
            z-index: 5;
            min-height: 48px;
            padding: 7px 13px 7px 8px;
            display: inline-flex;
            align-items: center;
            gap: 9px;
            border: 1px solid rgba(191, 219, 254, 0.96);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.9);
            color: #0f2f57;
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.12);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            font-size: 0.76rem;
            font-weight: 800;
            white-space: nowrap;
            transition: transform 0.2s ease-out, box-shadow 0.2s ease;
            will-change: transform;
        }

        .home-hero__skill:hover {
            box-shadow: 0 20px 38px rgba(37, 99, 235, 0.2);
        }

        .home-hero__skill-icon {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: linear-gradient(145deg, #dbeafe, #bfdbfe);
            color: #1d4ed8;
            font-size: 0.82rem;
        }

        .home-hero__skill.is-web { top: 42px; right: -8px; }
        .home-hero__skill.is-app { top: 146px; left: -22px; }
        .home-hero__skill.is-cicd { bottom: 78px; left: -2px; }
        .home-hero__skill.is-spring { right: -10px; bottom: 28px; }

        html[data-web-theme='dark'] .home-hero__portrait-stage::before {
            background:
                radial-gradient(circle at 36% 28%, rgba(96, 165, 250, 0.2) 0 8%, transparent 30%),
                linear-gradient(145deg, #172554 0%, #1e3a8a 50%, #2563eb 100%);
            border-color: rgba(96, 165, 250, 0.38);
            box-shadow: 0 34px 76px rgba(37, 99, 235, 0.25);
        }

        html[data-web-theme='dark'] .home-hero__portrait-stage::after {
            border-color: rgba(96, 165, 250, 0.42);
        }

        html[data-web-theme='dark'] .home-hero__skill {
            background: rgba(14, 17, 19, 0.9);
            border-color: #334155;
            color: #f8fafc;
            box-shadow: 0 18px 34px rgba(0, 0, 0, 0.32);
        }

        html[data-web-theme='dark'] .home-hero__skill-icon {
            background: rgba(37, 99, 235, 0.2);
            color: #93c5fd;
        }

        .home-tracking {
            position: relative;
            width: 100vw;
            margin-left: calc(50% - 50vw);
            padding: 0 0 24px;
            /* Keep the stats background full width at every browser zoom level. */
            background: #111111;
        }

        .home-notice-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            z-index: 1301;
            width: min(460px, calc(100vw - 28px));
            padding: 16px 18px;
            border-radius: 20px;
            border: 1px solid #fde68a;
            background: linear-gradient(180deg, #fff8db, #fff2b8);
            color: #854d0e;
            box-shadow: 0 20px 34px rgba(146, 64, 14, 0.12);
            transform: translate(-50%, -50%);
            transition: opacity 0.28s ease, transform 0.28s ease;
        }

        .home-notice-popup.is-hidden {
            opacity: 0;
            transform: translate(-50%, calc(-50% - 10px));
            pointer-events: none;
        }

        .home-notice-popup__title {
            margin: 0 0 6px;
            font-family: var(--font-lato);
            font-size: 0.94rem;
            font-weight: 800;
            text-align: center;
        }

        .home-notice-popup__copy {
            margin: 0;
            font-size: 0.82rem;
            line-height: 1.65;
            text-align: center;
        }

        .home-notice-backdrop {
            position: fixed;
            inset: 0;
            z-index: 1300;
            background: rgba(15, 23, 42, 0.34);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            transition: opacity 0.28s ease;
        }

        .home-notice-backdrop.is-hidden {
            opacity: 0;
            pointer-events: none;
        }

        .home-tracking__inner {
            display: grid;
            align-content: start;
            gap: 18px;
            width: min(1440px, 100vw);
            min-height: 354px;
            margin: 0 auto;
            padding: 76px 28px 26px;
            border-radius: 0;
            background: #111111;
            border: 0;
            box-shadow: none;
        }

        .home-tracking__head {
            display: grid;
            gap: 8px;
            justify-items: center;
            text-align: center;
        }

        .home-tracking__badge {
            display: none;
            align-items: center;
            justify-content: center;
            min-height: 24px;
            padding: 0 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #f3f4f6;
            font-size: 0.66rem;
            font-weight: 700;
        }

        .home-tracking__title {
            margin: 0;
            max-width: 760px;
            color: #f8fafc;
            font-size: clamp(1.15rem, 2vw, 1.7rem);
            line-height: 1.12;
            letter-spacing: -0.02em;
            font-weight: 800;
            font-family: var(--font-lato);
        }

        .home-tracking__copy {
            margin: 0;
            max-width: 820px;
            color: rgba(241, 245, 249, 0.72);
            font-size: 0.96rem;
            line-height: 1.7;
            text-align: center;
            box-shadow: none;
        }

        .home-tracking__grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            width: min(1120px, calc(100% - 56px));
            margin: 0 auto;
            background: transparent;
            border: 0;
        }

        /* Present live platform totals as compact analytics cards with distinct accents. */
        .home-tracking-card {
            --tracking-accent: 96 165 250;
            position: relative;
            min-height: 178px;
            padding: 20px;
            border-radius: 22px;
            border: 1px solid rgb(var(--tracking-accent) / 0.22);
            background: linear-gradient(145deg, rgb(255 255 255 / 0.075), rgb(255 255 255 / 0.025));
            box-shadow: 0 18px 38px rgba(0, 0, 0, 0.24);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            overflow: hidden;
            transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        }

        .home-tracking-card:last-child {
            border-right-color: rgb(var(--tracking-accent) / 0.22);
        }

        .home-tracking-card:hover {
            transform: translateY(-5px);
            border-color: rgb(var(--tracking-accent) / 0.48);
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.3), 0 0 28px rgb(var(--tracking-accent) / 0.08);
        }

        .home-tracking-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 5% 0%, rgb(var(--tracking-accent) / 0.24), transparent 54%);
            pointer-events: none;
        }

        .home-tracking-card::after {
            content: "";
            position: absolute;
            top: 0;
            left: 20px;
            right: 20px;
            height: 2px;
            border-radius: 999px;
            background: linear-gradient(90deg, transparent, rgb(var(--tracking-accent) / 0.9), transparent);
            pointer-events: none;
        }

        .home-tracking-card.is-blue {
            --tracking-accent: 96 165 250;
        }

        .home-tracking-card.is-emerald {
            --tracking-accent: 52 211 153;
        }

        .home-tracking-card.is-violet {
            --tracking-accent: 167 139 250;
        }

        .home-tracking-card.is-rose {
            --tracking-accent: 251 113 133;
        }

        .home-tracking-card__body {
            position: relative;
            z-index: 1;
            display: grid;
            align-content: space-between;
            gap: 14px;
            height: 100%;
        }

        .home-tracking-card__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            width: 100%;
        }

        .home-tracking-card__icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgb(var(--tracking-accent) / 0.22);
            background: rgb(var(--tracking-accent) / 0.12);
            color: rgb(var(--tracking-accent));
            font-size: 1rem;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }

        .home-tracking-card__source {
            min-height: 24px;
            padding: 0 9px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.055);
            color: rgba(226, 232, 240, 0.72);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.58rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .home-tracking-card__label {
            margin: 0;
            color: rgba(226, 232, 240, 0.76);
            font-size: 0.84rem;
            font-weight: 700;
            line-height: 1.5;
            text-align: left;
        }

        .home-tracking-card__value {
            margin: 0;
            color: #ffffff;
            font-family: var(--font-lato);
            font-size: clamp(2rem, 2.7vw, 2.65rem);
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.04em;
            text-align: left;
            text-shadow: 0 8px 22px rgb(var(--tracking-accent) / 0.16);
        }

        .home-tracking-card__text {
            display: none;
        }

        .home-tracking-card__meta {
            display: none;
        }

        @media (prefers-reduced-motion: reduce) {
            .home-tracking-card {
                transition: none;
            }

            .home-tracking-card:hover {
                transform: none;
            }
        }

        .home-services {
            position: relative;
            width: min(1280px, calc(100% - 32px));
            margin: 0 auto;
            padding: 62px 0 104px;
        }

        .home-services__inner {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 28px;
        }

        .home-services__head {
            position: static;
            text-align: center;
            display: grid;
            justify-items: center;
            gap: 14px;
            padding: 0;
            background: transparent;
            border: 0;
            box-shadow: none;
        }

        .home-services__badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 0 16px;
            border-radius: 999px;
            background: rgba(29, 140, 255, 0.1);
            border: 1px solid rgba(29, 140, 255, 0.18);
            color: #1d4ed8;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        .home-services__title {
            position: static;
            margin: 0;
            max-width: 760px;
            color: #0f172a;
            font-family: var(--font-lato);
            font-size: clamp(1.15rem, 2vw, 1.7rem);
            line-height: 1.12;
            letter-spacing: -0.02em;
            font-weight: 800;
        }

        .home-services__copy {
            position: static;
            margin: 0;
            max-width: 640px;
            color: #64748b;
            font-size: 1.02rem;
            line-height: 1.8;
        }

        .home-services__grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .home-compact-section {
            width: min(1280px, calc(100% - 32px));
            margin: 0 auto;
            padding: 0 0 86px;
        }

        .home-compact-head {
            display: grid;
            gap: 8px;
            justify-items: center;
            text-align: center;
            padding-bottom: 16px;
        }

        .home-compact-head__title {
            margin: 0;
            color: #0f172a;
            font-family: var(--font-lato);
            font-size: clamp(1.15rem, 2vw, 1.7rem);
            line-height: 1.12;
            letter-spacing: -0.02em;
            font-weight: 800;
        }

        .home-compact-head__copy {
            margin: 0;
            max-width: 720px;
            color: #64748b;
            font-size: 0.96rem;
            line-height: 1.7;
            text-align: center;
        }

        .home-why-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            width: min(920px, 100%);
            margin: 0 auto;
        }

        .home-why-item {
            border-radius: 20px;
            border: 1px solid #dde8f3;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(247, 250, 255, 0.98));
            overflow: hidden;
            transition: border-color 0.24s ease;
        }

        .home-why-item:hover {
            border-color: #cddcf0;
        }

        .home-why-item summary {
            list-style: none;
            display: grid;
            grid-template-columns: 44px minmax(0, 1fr) 22px;
            align-items: center;
            gap: 12px;
            padding: 16px 18px;
            cursor: pointer;
        }

        .home-why-item summary::-webkit-details-marker {
            display: none;
        }

        .home-why-card__icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eef4ff;
            color: #2563eb;
            font-size: 1rem;
        }

        .home-why-card__title {
            margin: 0;
            color: #0f172a;
            font-size: 0.92rem;
            font-weight: 800;
            line-height: 1.4;
            text-align: left;
        }

        .home-why-card__copy {
            margin: 0;
            padding: 0 18px 18px 74px;
            color: #64748b;
            font-size: 0.86rem;
            line-height: 1.65;
        }

        .home-why-item__chevron {
            color: #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.42s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .home-why-item.is-open .home-why-item__chevron {
            transform: rotate(180deg);
        }

        .home-why-item__content {
            height: 0;
            overflow: hidden;
            transition: height 0.52s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .home-why-item__content-inner {
            opacity: 0;
            transform: translateY(-6px);
            transition: opacity 0.42s ease, transform 0.42s ease;
        }

        .home-why-item.is-open .home-why-item__content-inner {
            opacity: 1;
            transform: translateY(0);
            transition-delay: 0.08s;
        }

        .home-featured-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        /* Match the featured cards to the course catalog without changing other homepage cards. */
        #home-featured-section { width: min(1320px, calc(100% - 32px)); }
        .home-featured-card {
            display: flex;
            flex-direction: column;
            text-decoration: none;
            min-height: 100%;
            border-radius: 0;
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #dbe6f1;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .home-featured-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 28px rgba(15, 23, 42, 0.1);
        }

        .home-featured-card__media {
            position: relative;
            height: 178px;
            min-height: 178px;
            background: #f8fafc;
            border-bottom: 1px solid #e7eef5;
        }

        .home-featured-card__media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .home-featured-card__fallback {
            width: 100%;
            height: 100%;
            display: grid;
            place-items: center;
            color: #5b708a;
            font-size: 48px;
        }

        .home-featured-card__badges {
            position: absolute;
            top: 9px;
            right: 9px;
            z-index: 2;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 5px;
        }

        .home-featured-card__badge {
            display: inline-flex;
            align-items: center;
            min-height: 20px;
            padding: 0 7px;
            background: rgba(21, 31, 48, 0.9);
            color: #fff;
            font-size: 9px;
            font-weight: 700;
        }

        .home-featured-card__body {
            display: grid;
            grid-template-rows: auto auto minmax(44px, 1fr) auto auto;
            gap: 6px;
            flex: 1;
            padding: 12px;
        }

        .home-featured-card__meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 2px;
            color: #64748b;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .home-featured-card__title {
            margin: 0;
            color: #0f172a;
            font-size: 0.92rem;
            line-height: 1.36;
            font-family: var(--font-lato);
        }

        .home-featured-card__copy {
            margin: 0 0 2px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.52;
            min-height: 44px;
        }

        .home-featured-card__footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            color: #64748b;
            font-size: 12px;
        }

        .home-featured-card__footer strong { color: #0f172a; font-size: 12px; }

        html[data-web-theme='dark'] .home-featured-card__footer strong {
            color: #f8fafc;
        }

        .home-featured-card__price {
            min-height: 22px;
            padding: 0 9px;
            border-radius: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eef4ff;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 700;
        }

        .home-featured-card__price.is-free { background: #e6f8ee; color: #157347; }
        html[data-web-theme='dark'] body.web-shell .web-main .home-featured-card__price.is-free { color: #157347 !important; }

        .home-featured-empty {
            min-height: 160px;
            display: grid;
            place-items: center;
            text-align: center;
            border-radius: 20px;
            border: 1px solid #dde8f3;
            background: #ffffff;
            color: #64748b;
            font-size: 0.92rem;
        }

        .home-service-card {
            position: relative;
            min-height: 270px;
            padding: 24px 22px;
            border-radius: 24px;
            border: 1px solid #dde8f3;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(247, 250, 255, 0.98));
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.06);
            overflow: hidden;
            transition: transform 0.24s ease, box-shadow 0.24s ease, border-color 0.24s ease;
        }

        .home-service-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(29, 140, 255, 0.05), rgba(103, 80, 255, 0.02));
            opacity: 0;
            transition: opacity 0.24s ease;
            pointer-events: none;
        }

        .home-service-card:hover {
            transform: translateY(-6px);
            border-color: #cbdcf0;
            box-shadow: 0 28px 50px rgba(15, 23, 42, 0.1);
        }

        .home-service-card:hover::before {
            opacity: 1;
        }

        .home-service-card__body {
            position: relative;
            z-index: 1;
            display: grid;
            justify-items: center;
            text-align: center;
            gap: 14px;
        }

        .home-service-card__icon {
            width: 62px;
            height: 62px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
        }

        .home-service-card.is-blue .home-service-card__icon {
            background: rgba(29, 140, 255, 0.12);
            color: #1d8cff;
        }

        .home-service-card.is-indigo .home-service-card__icon {
            background: rgba(79, 70, 229, 0.12);
            color: #4f46e5;
        }

        .home-service-card.is-violet .home-service-card__icon {
            background: rgba(124, 58, 237, 0.12);
            color: #7c3aed;
        }

        .home-service-card.is-rose .home-service-card__icon {
            background: rgba(244, 63, 94, 0.12);
            color: #e11d48;
        }

        .home-service-card.is-emerald .home-service-card__icon {
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
        }

        .home-service-card.is-cyan .home-service-card__icon {
            background: rgba(6, 182, 212, 0.12);
            color: #0891b2;
        }

        .home-service-card__title {
            margin: 0;
            color: #0f172a;
            font-size: 1.2rem;
            font-weight: 800;
            line-height: 1.35;
        }

        .home-service-card__text {
            margin: 0;
            color: #64748b;
            font-size: 0.96rem;
            line-height: 1.7;
        }

        @media (max-width: 1080px) {
            .home-hero__inner {
                grid-template-columns: 1fr;
            }

            .home-hero__content {
                max-width: 100%;
            }

            .home-hero__mockup {
                margin: 6px auto 0;
            }

            .home-tracking__grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                width: min(620px, calc(100% - 24px));
            }

            .home-featured-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .home-services__grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .home-hero {
                margin-top: 0;
                min-height: auto;
                padding: 20px 0 56px;
            }

            .home-hero__inner {
                width: min(100%, calc(100% - 20px));
                min-height: auto;
                gap: 20px;
            }

            /* Lead with the developer portrait before the hero description on phones. */
            .home-hero__mockup {
                grid-row: 1;
                margin-top: 0;
            }

            .home-hero__content {
                grid-row: 2;
            }

            .home-hero__title {
                font-size: clamp(1.05rem, 5.4vw, 1.35rem);
            }

            .home-hero__copy {
                font-size: 0.92rem;
            }

            .home-hero__actions {
                flex-direction: column;
                align-items: stretch;
            }

            .home-hero__button {
                width: 100%;
            }

            .home-hero__highlights {
                gap: 10px;
            }

            .home-hero__highlight {
                width: 100%;
                font-size: 0.8rem;
            }

            .home-hero__mockup,
            .home-hero__portrait-stage,
            .home-hero__skill {
                transform: none !important;
            }

            .home-hero__mockup {
                max-width: 410px;
            }

            .home-hero__portrait-stage {
                min-height: 430px;
            }

            .home-hero__portrait-stage::before,
            .home-hero__portrait-glare {
                width: 310px;
                height: 310px;
            }

            .home-hero__portrait-stage::after {
                width: 348px;
                height: 348px;
            }

            .home-hero__portrait-image {
                width: min(370px, 92%);
            }

            .home-hero__skill.is-web { right: 0; }
            .home-hero__skill.is-app { left: 0; }
            .home-hero__skill.is-cicd { left: 2px; }
            .home-hero__skill.is-spring { right: 0; }

            .home-hero__skill {
                min-height: 44px;
                padding: 6px 10px 6px 7px;
                font-size: 0.7rem;
            }

            .home-hero__skill-icon {
                width: 30px;
                height: 30px;
            }

            .home-notice-popup {
                width: min(420px, calc(100vw - 20px));
                padding: 14px 14px;
            }

            .home-tracking {
                padding: 0 0 14px;
            }

            .home-tracking__head {
                text-align: center;
                justify-items: center;
            }

            .home-tracking__inner {
                gap: 18px;
                min-height: auto;
                padding: 100px 12px 18px;
            }

            .home-tracking__title {
                font-size: clamp(1rem, 4.8vw, 1.3rem);
            }

            .home-tracking__copy {
                font-size: 0.96rem;
                line-height: 1.7;
            }

            .home-tracking__grid {
                grid-template-columns: 1fr;
                width: 100%;
            }

            .home-tracking-card {
                min-height: 158px;
                padding: 18px;
                border-right-color: rgb(var(--tracking-accent) / 0.22);
                background: linear-gradient(145deg, rgb(255 255 255 / 0.075), rgb(255 255 255 / 0.025));
            }

            .home-tracking-card:last-child {
                border-right-color: rgb(var(--tracking-accent) / 0.22);
            }

            .home-tracking-card__icon {
                width: 40px;
                height: 40px;
                font-size: 0.92rem;
            }

            .home-tracking-card__value {
                font-size: 1.8rem;
            }

            .home-services {
                width: min(100%, calc(100% - 20px));
                padding: 36px 0 80px;
            }

            .home-compact-section {
                width: min(100%, calc(100% - 20px));
                padding: 0 0 68px;
            }

            .home-featured-grid {
                grid-template-columns: 1fr;
            }

            .home-compact-head__copy {
                font-size: 0.9rem;
            }

            .home-why-item summary {
                grid-template-columns: 40px minmax(0, 1fr) 20px;
                padding: 14px 14px;
            }

            .home-why-card__copy {
                padding: 0 14px 14px 66px;
            }

            .home-services__grid {
                grid-template-columns: 1fr;
            }

            .home-service-card {
                min-height: auto;
                padding: 22px 18px;
            }
        }

        @media (max-width: 420px) {
            .home-hero__portrait-stage {
                min-height: 400px;
            }

            .home-hero__portrait-stage::before,
            .home-hero__portrait-glare {
                top: 54px;
                width: 270px;
                height: 270px;
            }

            .home-hero__portrait-stage::after {
                top: 38px;
                width: 302px;
                height: 302px;
            }

            .home-hero__portrait-image {
                top: 18px;
                width: min(330px, 94%);
            }

            .home-hero__skill {
                min-height: 40px;
                font-size: 0.65rem;
            }

            .home-hero__skill.is-web { top: 32px; }
            .home-hero__skill.is-app { top: 132px; }
            .home-hero__skill.is-cicd { bottom: 62px; }
            .home-hero__skill.is-spring { bottom: 14px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .home-hero__mockup,
            .home-hero__portrait-stage,
            .home-hero__skill {
                transition: none;
                will-change: auto;
            }
        }
    </style>

    {{-- Home hero section follows the reference layout but uses TechCourse-specific content and actions. --}}
    <section class="home-hero" id="home-hero-section">
        <div class="home-hero__bg" aria-hidden="true">
            <div class="home-hero__blob is-primary"></div>
            <div class="home-hero__blob is-secondary"></div>
            <div class="home-hero__pattern"></div>
        </div>

        <div class="home-hero__inner">
            <div class="home-hero__content">
                <span class="home-hero__eyebrow">
                    <span class="home-hero__eyebrow-dot"></span>
                    {{ $heroEyebrow }}
                </span>

                <h1 class="home-hero__title">{{ $heroTitle }}</h1>
                <p class="home-hero__copy">{{ $heroCopy }}</p>

                <div class="home-hero__actions">
                    {{-- Send the primary course action to the full course catalog. --}}
                    <a href="{{ route('courses.index') }}" class="home-hero__button is-primary">
                        <i class="fa-solid fa-graduation-cap"></i>
                        {{ $heroPrimaryCta }}
                    </a>
                    <a href="#home-services-section" class="home-hero__button is-secondary">
                        <i class="fa-solid fa-arrow-right"></i>
                        {{ $heroSecondaryCta }}
                    </a>
                </div>

                <div class="home-hero__highlights">
                    @foreach ($heroHighlights as $highlight)
                        <div class="home-hero__highlight">
                            <span class="home-hero__highlight-icon">
                                <i class="{{ $highlight['icon'] }}"></i>
                            </span>
                            <span>{{ $highlight['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Keep the developer portrait and skill labels decorative without changing hero actions. --}}
            <div class="home-hero__mockup rellax" data-rellax-speed="-2" data-hero-mockup>
                <div class="home-hero__portrait-stage" data-hero-visual>
                    <img
                        src="{{ asset('logo/me_removebg.png') }}"
                        alt="Phon Phyron - Full Stack Developer"
                        class="home-hero__portrait-image"
                        width="794"
                        height="898"
                        fetchpriority="high"
                    >
                    <span class="home-hero__portrait-glare" data-hero-glare aria-hidden="true"></span>

                    <div class="home-hero__skill is-web" data-hero-badge="web">
                        <span class="home-hero__skill-icon">
                            <i class="fa-solid fa-code" aria-hidden="true"></i>
                        </span>
                        <span>Web Developer</span>
                    </div>

                    <div class="home-hero__skill is-app" data-hero-badge="app">
                        <span class="home-hero__skill-icon">
                            <i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i>
                        </span>
                        <span>App Developer</span>
                    </div>

                    <div class="home-hero__skill is-cicd" data-hero-badge="cicd">
                        <span class="home-hero__skill-icon">
                            <i class="fa-solid fa-code-branch" aria-hidden="true"></i>
                        </span>
                        <span>CI/CD</span>
                    </div>

                    <div class="home-hero__skill is-spring" data-hero-badge="spring">
                        <span class="home-hero__skill-icon">
                            <i class="fa-solid fa-leaf" aria-hidden="true"></i>
                        </span>
                        <span>Spring Boot</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="home-tracking" id="home-tracking-section">
        <div class="home-notice-backdrop" data-home-notice-backdrop></div>
        <div class="home-notice-popup" data-home-notice-popup>
            <p class="home-notice-popup__title">{{ $homeNoticeTitle }}</p>
            <p class="home-notice-popup__copy">{{ $homeNoticeCopy }}</p>
        </div>

        <div class="home-tracking__inner">
            <div class="home-tracking__head">
                <span class="home-tracking__badge">{{ __('Website Tracking') }}</span>
                <h1 class="home-tracking__title">{{ $trackingSectionTitle }}</h1>
                <p class="home-tracking__copy">{{ $trackingSectionCopy }}</p>
            </div>

            <div class="home-tracking__grid">
                @foreach ($trackingItems as $trackingItem)
                    <article class="home-tracking-card {{ $trackingItem['tone'] }}">
                        <div class="home-tracking-card__body">
                            <div class="home-tracking-card__top">
                                <span class="home-tracking-card__icon">
                                    <i class="{{ $trackingItem['icon'] }}"></i>
                                </span>
                                <span class="home-tracking-card__source">{{ $trackingItem['source'] }}</span>
                            </div>

                            <div>
                                <p class="home-tracking-card__label">{{ $trackingItem['label'] }}</p>
                                <h2 class="home-tracking-card__value">{{ $trackingItem['value'] }}</h2>
                            </div>

                            <p class="home-tracking-card__text">{{ $trackingItem['description'] }}</p>
                            <span class="home-tracking-card__meta">
                                <i class="fa-solid fa-signal"></i>
                                @if (($trackingItem['key'] ?? '') === 'guest-views')
                                    {{ __('Live viewers') }}: {{ $trackingItem['live_value'] ?? '0' }}
                                @else
                                    {{ __('Live total') }}
                                @endif
                            </span>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="home-services" id="home-services-section">
        <div class="home-services__inner">
            <div class="home-services__head">
                <span class="home-services__badge">{{ __('Our Services') }}</span>
                <h1 class="home-services__title">{{ __('What We Offer') }}</h1>
                <p class="home-services__copy">{{ __('We provide complete web solutions that meet your business needs.') }}</p>
            </div>

            <div class="home-services__grid">
                @foreach ($serviceItems as $service)
                    <article class="home-service-card {{ $service['tone'] }}">
                        <div class="home-service-card__body">
                            <span class="home-service-card__icon">
                                <i class="{{ $service['icon'] }}"></i>
                            </span>
                            <h2 class="home-service-card__title">{{ $service['title'] }}</h2>
                            <p class="home-service-card__text">{{ $service['description'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="home-compact-section">
        <div class="home-compact-head">
            <h2 class="home-compact-head__title">{{ $whyChooseTitle }}</h2>
            <p class="home-compact-head__copy">{{ $whyChooseCopy }}</p>
        </div>

        <div class="home-why-grid">
            @foreach ($whyChooseItems as $item)
                <details class="home-why-item {{ $loop->first ? 'is-open' : '' }}" @if($loop->first) open @endif data-why-item>
                    <summary>
                        <span class="home-why-card__icon">
                            <i class="{{ $item['icon'] }}"></i>
                        </span>
                        <h3 class="home-why-card__title">{{ $item['title'] }}</h3>
                        <span class="home-why-item__chevron">
                            <i class="fa-solid fa-chevron-down"></i>
                        </span>
                    </summary>
                    <div class="home-why-item__content" data-why-content>
                        <div class="home-why-item__content-inner">
                            <p class="home-why-card__copy">{{ $item['copy'] }}</p>
                        </div>
                    </div>
                </details>
            @endforeach
        </div>
    </section>

    <section class="home-compact-section" id="home-featured-section">
        <div class="home-compact-head">
            <h2 class="home-compact-head__title">{{ $featuredSectionTitle }}</h2>
            <p class="home-compact-head__copy">{{ $featuredSectionCopy }}</p>
        </div>

        @if ($featuredCourses->isNotEmpty())
            <div class="home-featured-grid">
                @foreach ($featuredCourses->take(4) as $course)
                    @php
                        $lessonCount = (int) ($course->lessons_count ?? $course->total_lessons ?? 0);
                        $resourceCount = (int) ($course->resources_count ?? 0);
                        $courseCategory = $course->category?->name ?: __('General');
                        $priceLabel = $course->is_free ? __('Free') : (($course->currency ?: '$') . number_format((float) $course->price, 2));
                    @endphp
                    {{-- Featured course cards mirror the catalog while retaining the homepage links and skeleton hooks. --}}
                    <a href="{{ route('courses.show', $course->slug ?: $course->id) }}" class="home-featured-card" data-skeleton-card>
                        <div class="home-featured-card__media" data-skeleton-image>
                            <div class="home-featured-card__badges">
                                <span class="home-featured-card__badge">{{ $lessonCount }} {{ __('Lessons') }}</span>
                                <span class="home-featured-card__badge">{{ __(\Illuminate\Support\Str::headline($course->level ?: 'Beginner')) }}</span>
                            </div>
                            @if ($course->thumbnail_url)
                                <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}">
                            @else
                                <div class="home-featured-card__fallback">
                                    <i class="fa-solid fa-laptop-code" aria-hidden="true"></i>
                                </div>
                            @endif
                        </div>

                        <div class="home-featured-card__body">
                            <div class="home-featured-card__meta" data-skeleton-line>
                                <span>{{ $courseCategory }}</span>
                                <span>{{ $course->language ?: __('Khmer') }}</span>
                            </div>
                            <h3 class="home-featured-card__title" data-skeleton-line>{{ $course->title }}</h3>
                            <p class="home-featured-card__copy" data-skeleton-block>
                                {{ $course->short_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $course->description), 120) }}
                            </p>
                            <div class="home-featured-card__footer" data-skeleton-line>
                                <span>{{ __('Total Resource') }}</span>
                                <strong>{{ $resourceCount }}</strong>
                            </div>
                            <div class="home-featured-card__footer" data-skeleton-line>
                                <span>{{ __('Price') }}</span>
                                <span class="home-featured-card__price {{ $course->is_free ? 'is-free' : 'is-paid' }}">{{ $priceLabel }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="home-featured-empty">
                {{ $isKhmer ? 'មិនទាន់មានវគ្គសិក្សាណែនាំសម្រាប់បង្ហាញនៅពេលនេះទេ។' : 'No featured courses available right now.' }}
            </div>
        @endif
    </section>

    <script>
        (() => {
            const homeNotice = document.querySelector('[data-home-notice-popup]');
            const homeNoticeBackdrop = document.querySelector('[data-home-notice-backdrop]');
            const heroMockup = document.querySelector('[data-hero-mockup]');
            const heroVisual = document.querySelector('[data-hero-visual]');
            const heroGlare = document.querySelector('[data-hero-glare]');
            const heroBadges = Array.from(document.querySelectorAll('[data-hero-badge]'));
            const whyItems = Array.from(document.querySelectorAll('[data-why-item]'));
            const homeNoticeStorageKey = 'techcourse_home_notice_seen';

            // Show the home popup only once for the first website visit in this browser.
            if (homeNotice) {
                const hasSeenNotice = window.localStorage.getItem(homeNoticeStorageKey) === '1';

                if (hasSeenNotice) {
                    homeNotice.hidden = true;
                    if (homeNoticeBackdrop) {
                        homeNoticeBackdrop.hidden = true;
                    }
                } else {
                    window.localStorage.setItem(homeNoticeStorageKey, '1');

                    window.setTimeout(() => {
                        homeNotice.classList.add('is-hidden');
                        homeNoticeBackdrop?.classList.add('is-hidden');

                        window.setTimeout(() => {
                            homeNotice.hidden = true;
                            if (homeNoticeBackdrop) {
                                homeNoticeBackdrop.hidden = true;
                            }
                        }, 300);
                    }, 6000);
                }
            }

            // Add gentle pointer-follow motion to the portrait and skill labels.
            if (
                heroMockup
                && heroVisual
                && window.matchMedia('(pointer: fine)').matches
                && !window.matchMedia('(prefers-reduced-motion: reduce)').matches
            ) {
                const resetHeroMotion = () => {
                    heroMockup.style.transform = 'translate3d(0, 0, 0)';
                    heroVisual.style.transform = 'rotateX(0deg) rotateY(0deg) scale(1)';

                    heroBadges.forEach((badge) => {
                        badge.style.transform = 'translate3d(0, 0, 0)';
                    });

                    if (heroGlare) {
                        heroGlare.style.opacity = '0';
                        heroGlare.style.background = 'linear-gradient(168deg, rgba(255, 255, 255, 0.18) 0%, transparent 52%)';
                    }
                };

                heroMockup.addEventListener('pointermove', (event) => {
                    const rect = heroMockup.getBoundingClientRect();
                    const relativeX = (event.clientX - rect.left) / rect.width;
                    const relativeY = (event.clientY - rect.top) / rect.height;
                    const offsetX = relativeX - 0.5;
                    const offsetY = relativeY - 0.5;
                    const rotateY = offsetX * 8;
                    const rotateX = offsetY * -8;
                    const mockupShiftX = offsetX * 8;
                    const mockupShiftY = offsetY * 8;
                    const badgeShiftX = offsetX * 18;
                    const badgeShiftY = offsetY * 12;

                    heroMockup.style.transform = `translate3d(${mockupShiftX}px, ${mockupShiftY}px, 0)`;
                    heroVisual.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.01)`;

                    heroBadges.forEach((badge, index) => {
                        const direction = index === 0 ? -1 : 1;
                        badge.style.transform = `translate3d(${badgeShiftX * direction}px, ${badgeShiftY * direction}px, 0)`;
                    });

                    if (heroGlare) {
                        const glareX = relativeX * 100;
                        const glareY = relativeY * 100;
                        heroGlare.style.opacity = '1';
                        heroGlare.style.background = `linear-gradient(168deg, rgba(255, 255, 255, 0.22) 0%, rgba(255, 255, 255, 0.08) 18%, transparent 54%), radial-gradient(circle at ${glareX}% ${glareY}%, rgba(255, 255, 255, 0.2), transparent 34%)`;
                    }
                });

                heroMockup.addEventListener('pointerleave', resetHeroMotion);
                heroMockup.addEventListener('pointercancel', resetHeroMotion);
                resetHeroMotion();
            }

            // Animate accordion panels slowly and keep only one item open at a time.
            if (whyItems.length) {
                const setPanelState = (item, shouldOpen) => {
                    const content = item.querySelector('[data-why-content]');

                    if (!content) {
                        return;
                    }

                    content.style.overflow = 'hidden';

                    if (shouldOpen) {
                        item.open = true;
                        item.classList.add('is-open');

                        const targetHeight = content.scrollHeight;
                        content.style.height = `${targetHeight}px`;

                        window.setTimeout(() => {
                            if (item.classList.contains('is-open')) {
                                content.style.height = 'auto';
                            }
                        }, 540);
                    } else {
                        const startHeight = content.scrollHeight;
                        content.style.height = `${startHeight}px`;

                        requestAnimationFrame(() => {
                            item.classList.remove('is-open');
                            content.style.height = '0px';
                        });

                        window.setTimeout(() => {
                            if (!item.classList.contains('is-open')) {
                                item.open = false;
                            }
                        }, 540);
                    }
                };

                whyItems.forEach((item) => {
                    const summary = item.querySelector('summary');
                    const content = item.querySelector('[data-why-content]');

                    if (!summary || !content) {
                        return;
                    }

                    content.style.height = item.classList.contains('is-open') ? 'auto' : '0px';

                    summary.addEventListener('click', (event) => {
                        event.preventDefault();

                        const isOpen = item.classList.contains('is-open');

                        whyItems.forEach((otherItem) => {
                            if (otherItem !== item && otherItem.classList.contains('is-open')) {
                                setPanelState(otherItem, false);
                            }
                        });

                        setPanelState(item, !isOpen);
                    });
                });
            }
        })();
    </script>

@endsection
