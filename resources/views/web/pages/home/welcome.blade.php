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
    $heroMockupLabel = $isKhmer ? 'TechCourse Overview' : 'TechCourse Overview';
    $heroMockupTitle = $isKhmer ? 'Learning + Service ក្នុងកន្លែងតែមួយ' : 'Learning + Services In One Place';
    $heroMockupCopy = $isKhmer
        ? 'មើល course, service និងស្ថិតិទូទៅបានលឿនពីទំព័រដើម។'
        : 'Quickly view courses, services, and overall platform activity from the homepage.';
    $heroMockupFile = $isKhmer ? 'techcourse_home.php' : 'techcourse_home.php';
    $heroMockupStatus = $isKhmer ? 'Homepage ready · Live sections' : 'Homepage ready · Live sections';
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
        /* Keep the hero linked closely to the fixed header. */
        .home-hero {
            position: relative;
            overflow: hidden;
            width: 100vw;
            margin-left: calc(50% - 50vw);
            margin-top: 0;
            min-height: 540px;
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

        .home-hero__pattern {
            position: absolute;
            inset: 0;
            opacity: 0.32;
            background-image: radial-gradient(circle at 1px 1px, rgba(59, 130, 246, 0.1) 1px, transparent 0);
            background-size: 28px 28px;
        }

        .home-hero__inner {
            position: relative;
            z-index: 1;
            width: min(1180px, calc(100% - 36px));
            margin: 0 auto;
            min-height: calc(540px - 76px);
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

        .home-hero__eyebrow-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #2563eb;
            box-shadow: 0 0 0 6px rgba(37, 99, 235, 0.12);
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

        .home-hero__window {
            position: relative;
            overflow: hidden;
            border-radius: 22px;
            background: linear-gradient(180deg, #1a2331 0%, #111827 100%);
            border: 1px solid rgba(148, 163, 184, 0.2);
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.14);
            transition: transform 0.14s ease-out, box-shadow 0.14s ease-out;
            will-change: transform;
        }

        .home-hero__window-bar {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 9px 11px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.12);
            background: rgba(15, 23, 42, 0.88);
        }

        .home-hero__window-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
        }

        .home-hero__window-dot.is-red { background: #fb7185; }
        .home-hero__window-dot.is-yellow { background: #fbbf24; }
        .home-hero__window-dot.is-green { background: #34d399; }

        .home-hero__window-label {
            margin-left: 8px;
            color: #94a3b8;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: none;
        }

        .home-hero__window-tag {
            margin-left: auto;
            min-height: 22px;
            padding: 0 8px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(99, 102, 241, 0.12);
            color: #93c5fd;
            font-size: 0.64rem;
            font-weight: 800;
            letter-spacing: 0.04em;
        }

        .home-hero__window-body {
            padding: 12px 14px 10px;
            display: grid;
            gap: 10px;
        }

        .home-hero__window-copy {
            display: grid;
            gap: 8px;
        }

        .home-hero__window-title {
            margin: 0;
            color: #f8fafc;
            font-size: 0.86rem;
            line-height: 1.3;
            font-weight: 800;
        }

        .home-hero__window-text {
            margin: 0;
            color: rgba(226, 232, 240, 0.76);
            font-size: 0.7rem;
            line-height: 1.52;
        }

        .home-hero__code {
            margin: 0;
            padding: 10px 12px 14px;
            border-radius: 0;
            background: rgba(15, 23, 42, 0.16);
            border: 0;
            color: #e2e8f0;
            font-size: 0.64rem;
            line-height: 1.62;
            overflow-x: auto;
        }

        .home-hero__code code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }

        .home-hero__code .is-keyword {
            color: #93c5fd;
        }

        .home-hero__code .is-string {
            color: #86efac;
        }

        .home-hero__code .is-number {
            color: #f9a8d4;
        }

        .home-hero__window-status {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0 14px 5px;
            color: #86efac;
            font-size: 0.64rem;
            font-weight: 700;
            border-top: 1px solid rgba(148, 163, 184, 0.12);
        }

        .home-hero__window-status-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #4ade80;
            box-shadow: 0 0 0 6px rgba(74, 222, 128, 0.12);
        }

        .home-hero__window-glare {
            pointer-events: none;
            position: absolute;
            inset: 0;
            z-index: 6;
            opacity: 0;
            transition: opacity 0.28s ease;
            border-radius: inherit;
            background: linear-gradient(168deg, rgba(255, 255, 255, 0.18) 0%, transparent 52%);
        }

        .home-hero__floating {
            position: absolute;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(219, 234, 254, 0.96);
            box-shadow: 0 18px 30px rgba(15, 23, 42, 0.12);
            backdrop-filter: blur(10px);
            transition: transform 0.2s ease-out;
            will-change: transform;
        }

        .home-hero__floating.is-top {
            top: -10px;
            left: -16px;
            padding: 8px 12px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .home-hero__floating.is-bottom {
            right: -8px;
            bottom: -12px;
            padding: 10px 12px;
            min-width: 164px;
        }

        .home-hero__floating-icon {
            width: 30px;
            height: 30px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(219, 234, 254, 0.94);
            color: #2563eb;
            flex-shrink: 0;
        }

        .home-hero__floating-title {
            margin: 0;
            color: #0f172a;
            font-size: 0.82rem;
            font-weight: 800;
            line-height: 1.25;
        }

        .home-hero__floating-copy {
            margin: 4px 0 0;
            color: #64748b;
            font-size: 0.74rem;
            line-height: 1.5;
        }

        .home-tracking {
            position: relative;
            width: 100vw;
            margin-left: calc(50% - 50vw);
            padding: 0 0 24px;
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

        .home-tracking-card {
            position: relative;
            min-height: 154px;
            padding: 18px 14px 16px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.06) 0%, rgba(255, 255, 255, 0.03) 100%);
            box-shadow: 0 18px 34px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            overflow: hidden;
        }

        .home-tracking-card:last-child {
            border-right: 1px solid rgba(255, 255, 255, 0.12);
        }

        .home-tracking-card::before {
            content: "";
            position: absolute;
            inset: 0;
            opacity: 1;
            pointer-events: none;
        }

        .home-tracking-card.is-blue::before {
            background:
                radial-gradient(circle at top left, rgba(59, 130, 246, 0.3), transparent 56%),
                linear-gradient(180deg, rgba(37, 99, 235, 0.08), transparent 70%);
        }

        .home-tracking-card.is-emerald::before {
            background:
                radial-gradient(circle at top left, rgba(16, 185, 129, 0.28), transparent 56%),
                linear-gradient(180deg, rgba(5, 150, 105, 0.08), transparent 70%);
        }

        .home-tracking-card.is-violet::before {
            background:
                radial-gradient(circle at top left, rgba(139, 92, 246, 0.28), transparent 56%),
                linear-gradient(180deg, rgba(124, 58, 237, 0.08), transparent 70%);
        }

        .home-tracking-card.is-rose::before {
            background:
                radial-gradient(circle at top left, rgba(244, 63, 94, 0.28), transparent 56%),
                linear-gradient(180deg, rgba(225, 29, 72, 0.08), transparent 70%);
        }

        .home-tracking-card__body {
            position: relative;
            z-index: 1;
            display: grid;
            justify-items: center;
            text-align: center;
            align-content: center;
            gap: 10px;
            height: 100%;
        }

        .home-tracking-card__top {
            display: grid;
            justify-items: center;
            gap: 8px;
        }

        .home-tracking-card__icon {
            width: 42px;
            height: 42px;
            border-radius: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #e5eefb;
            font-size: 1rem;
            box-shadow: none;
        }

        .home-tracking-card.is-blue .home-tracking-card__icon {
            color: #7dd3fc;
        }

        .home-tracking-card.is-emerald .home-tracking-card__icon {
            color: #6ee7b7;
        }

        .home-tracking-card.is-violet .home-tracking-card__icon {
            color: #c4b5fd;
        }

        .home-tracking-card.is-rose .home-tracking-card__icon {
            color: #fda4af;
        }

        .home-tracking-card__source {
            display: none;
        }

        .home-tracking-card__label {
            margin: 0;
            max-width: 180px;
            color: rgba(241, 245, 249, 0.82);
            font-size: 0.96rem;
            font-weight: 600;
            line-height: 1.7;
            text-align: center;
        }

        .home-tracking-card__value {
            margin: 0;
            color: #ffffff;
            font-family: var(--font-lato);
            font-size: clamp(1.85rem, 2.5vw, 2.5rem);
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.04em;
        }

        .home-tracking-card__text {
            display: none;
        }

        .home-tracking-card__meta {
            display: none;
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
            gap: 14px;
        }

        .home-featured-card {
            display: flex;
            flex-direction: column;
            text-decoration: none;
            min-height: 100%;
            border-radius: 20px;
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #dbe6f1;
            box-shadow: 0 12px 22px rgba(15, 23, 42, 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .home-featured-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 28px rgba(15, 23, 42, 0.1);
        }

        .home-featured-card__media {
            position: relative;
            height: 150px;
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
            font-size: 34px;
        }

        .home-featured-card__body {
            display: grid;
            gap: 8px;
            padding: 12px 12px 14px;
        }

        .home-featured-card__meta {
            color: #64748b;
            font-size: 10px;
        }

        .home-featured-card__title {
            margin: 0;
            color: #0f172a;
            font-size: 0.92rem;
            line-height: 1.4;
            font-family: var(--font-lato);
        }

        .home-featured-card__copy {
            margin: 0;
            color: #64748b;
            font-size: 0.82rem;
            line-height: 1.6;
            min-height: 40px;
        }

        .home-featured-card__footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            color: #64748b;
            font-size: 11px;
        }

        .home-featured-card__price {
            min-height: 22px;
            padding: 0 9px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eef4ff;
            color: #1d4ed8;
            font-size: 10px;
            font-weight: 700;
        }

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

            .home-hero__window-body {
                padding: 14px 12px 12px;
            }

            .home-hero__code {
                padding: 12px 12px 18px;
                font-size: 0.7rem;
                line-height: 1.74;
            }

            .home-hero__mockup,
            .home-hero__window,
            .home-hero__floating {
                transform: none !important;
            }

            .home-hero__floating.is-top {
                left: 10px;
                top: 12px;
            }

            .home-hero__floating.is-bottom {
                right: 10px;
                bottom: 12px;
            }

            .home-hero__floating.is-bottom,
            .home-hero__floating.is-top {
                position: static;
                margin-top: 12px;
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
                min-height: auto;
                padding: 18px 14px 16px;
                border-right: 1px solid rgba(255, 255, 255, 0.12);
                border-bottom: 0;
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.06) 0%, rgba(255, 255, 255, 0.03) 100%);
            }

            .home-tracking-card:last-child {
                border-right: 1px solid rgba(255, 255, 255, 0.12);
            }

            .home-tracking-card__icon {
                width: 38px;
                height: 38px;
                font-size: 0.92rem;
            }

            .home-tracking-card__value {
                font-size: 1.5rem;
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
                    <a href="#home-featured-section" class="home-hero__button is-primary">
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

            <div class="home-hero__mockup" data-hero-mockup>
                <div class="home-hero__window" data-hero-window>
                    <div class="home-hero__window-bar">
                        <span class="home-hero__window-dot is-red"></span>
                        <span class="home-hero__window-dot is-yellow"></span>
                        <span class="home-hero__window-dot is-green"></span>
                        <span class="home-hero__window-label">{{ $heroMockupFile }}</span>
                        <span class="home-hero__window-tag">PHP</span>
                    </div>

                    <div class="home-hero__window-body">
                        <div class="home-hero__window-copy">
                            <h2 class="home-hero__window-title">{{ $heroMockupTitle }}</h2>
                            <p class="home-hero__window-text">{{ $heroMockupCopy }}</p>
                        </div>

                        {{-- Hero mockup uses a compact code-style panel to stay visually close to the shared video reference. --}}
                        <pre class="home-hero__code"><code><span class="is-keyword">$courseList</span>   = <span class="is-string">'featured_courses'</span>;
<span class="is-keyword">$serviceSet</span>  = <span class="is-string">'web_mobile_uiux'</span>;
<span class="is-keyword">$audience</span>    = <span class="is-string">'learners_business'</span>;

<span class="is-string">'courses'</span>      =&gt; <span class="is-number">{{ (int) ($trackingStats['courses'] ?? 0) }}</span>,
<span class="is-string">'products'</span>     =&gt; <span class="is-number">{{ (int) ($trackingStats['products'] ?? 0) }}</span>,
<span class="is-string">'support'</span>      =&gt; <span class="is-string">'friendly_flow'</span>,
<span class="is-string">'platform'</span>     =&gt; <span class="is-string">'TechCourse'</span>;</code></pre>

                        <div class="home-hero__window-status">
                            <span class="home-hero__window-status-dot"></span>
                            <span>{{ $heroMockupStatus }}</span>
                        </div>
                    </div>

                    <div class="home-hero__window-glare" data-hero-glare></div>
                </div>

                <div class="home-hero__floating is-top" data-hero-badge="top">
                    <span class="home-hero__floating-icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </span>
                    <div>
                        <p class="home-hero__floating-title">{{ $isKhmer ? 'Platform Ready' : 'Platform Ready' }}</p>
                        <p class="home-hero__floating-copy">{{ $isKhmer ? 'Course និង service flow នៅ homepage' : 'Course and service flow on homepage' }}</p>
                    </div>
                </div>

                <div class="home-hero__floating is-bottom" data-hero-badge="bottom">
                    <p class="home-hero__floating-title">{{ $isKhmer ? 'Featured Flow' : 'Featured Flow' }}</p>
                    <p class="home-hero__floating-copy">{{ $isKhmer ? 'មើល courses និង services បានលឿន' : 'Open courses and services quickly' }}</p>
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
                        $lessonCount = (int) ($course->total_lessons ?: $course->lessons?->count() ?: 0);
                        $courseCategory = $course->category?->name ?: __('General');
                        $priceLabel = $course->is_free ? __('Free') : (($course->currency ?: 'USD') . ' ' . number_format((float) $course->price, 2));
                    @endphp
                    <a href="{{ route('courses.show', $course->slug ?: $course->id) }}" class="home-featured-card">
                        <div class="home-featured-card__media">
                            @if ($course->thumbnail_url)
                                <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}">
                            @else
                                <div class="home-featured-card__fallback">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                </div>
                            @endif
                        </div>

                        <div class="home-featured-card__body">
                            <div class="home-featured-card__meta">{{ $courseCategory }}</div>
                            <h3 class="home-featured-card__title">{{ $course->title }}</h3>
                            <p class="home-featured-card__copy">
                                {{ \Illuminate\Support\Str::limit($course->short_description ?: $course->description ?: ($isKhmer ? 'វគ្គសិក្សាដែលអាចចាប់ផ្តើមមើលបានភ្លាមពីទំព័រដើម។' : 'A course you can open quickly from the homepage.'), 90) }}
                            </p>
                            <div class="home-featured-card__footer">
                                <span>{{ $lessonCount }} {{ __('Lessons') }}</span>
                                <span class="home-featured-card__price">{{ $priceLabel }}</span>
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
            const heroWindow = document.querySelector('[data-hero-window]');
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

            // Add gentle pointer-follow motion so the hero mockup behaves closer to the shared reference video.
            if (heroMockup && heroWindow && window.matchMedia('(pointer: fine)').matches) {
                const resetHeroMotion = () => {
                    heroMockup.style.transform = 'translate3d(0, 0, 0)';
                    heroWindow.style.transform = 'rotateX(0deg) rotateY(0deg) scale(1)';
                    heroWindow.style.boxShadow = '0 32px 68px rgba(15, 23, 42, 0.18)';

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
                    heroWindow.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.01)`;
                    heroWindow.style.boxShadow = '0 36px 84px rgba(15, 23, 42, 0.24)';

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
