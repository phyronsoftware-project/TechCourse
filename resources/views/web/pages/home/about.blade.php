@extends('web.layouts.app')

@section('title', __('About TechCourse'))

@php
    $isKhmer = app()->getLocale() === 'km';

    // Localize static content for the about page.
    $certificates = $isKhmer
        ? [
            'វិញ្ញាបនបត្រ Front-End Web Design',
            'វិញ្ញាបនបត្រ Internship Program',
            'វិញ្ញាបនបត្រ Microsoft Office (Word, Excel, PowerPoint)',
        ]
        : [
            'Certificate in Front-End Web Design',
            'Certificate in Internship Program',
            'Certificate in Microsoft Office (Word, Excel, PowerPoint)',
        ];

    // Translate intro content to match the current website locale.
    $aboutRole = $isKhmer ? 'អ្នកអភិវឌ្ឍន៍ Web / App' : 'Web / App Developer';
    $aboutExperience = $isKhmer ? 'បទពិសោធន៍ 1 ឆ្នាំ' : '1 Year Experience';
    $aboutKicker = $isKhmer ? 'អំពីខ្ញុំ' : 'About Me';
    $aboutObjectiveTitle = $isKhmer ? 'គោលបំណង' : 'Objective';
    $aboutObjectiveCopy = $isKhmer
        ? 'ខ្ញុំជា Web Developer ដែលមានបទពិសោធន៍ការងារ 1 ឆ្នាំ ក្នុងការអភិវឌ្ឍ web application ទាំងផ្នែក frontend និង backend។ ខ្ញុំស្រលាញ់ការរៀនបច្ចេកវិទ្យាថ្មីៗ និងបន្តអភិវឌ្ឍសមត្ថភាពផ្ទាល់ខ្លួនជានិច្ច ដើម្បីក្លាយជាអ្នកអភិវឌ្ឍន៍ដែលមានជំនាញខ្ពស់ និងមានវិជ្ជាជីវៈ។'
        : 'I am a Web Developer with 1 year of work experience in developing web applications on both the frontend and backend. I am passionate about learning new technologies and continuously improving my personal skills to become a highly skilled and professional developer.';
    $certificateTitle = $isKhmer ? 'វិញ្ញាបនបត្រ' : 'Certificates';

    $timelineItems = $isKhmer
        ? [
            [
                'period' => '2026 - បច្ចុប្បន្ន',
                'title' => 'អ្នកអភិវឌ្ឍន៍ App (Flutter)',
                'meta' => 'កំពុងរៀន mobile app development ជាមួយការអនុវត្តពិត',
                'side' => 'right',
                'points' => [
                    'បានចាប់ផ្តើមបង្កើត mobile application ដោយប្រើ Flutter ដោយផ្តោតលើ UI ស្អាត responsive layout និង reusable widgets។',
                    'បានអនុវត្ត state management, API integration, form handling និង navigation flow សម្រាប់ mobile screens។',
                    'បានរៀនភ្ជាប់ Flutter app ជាមួយ back-end services, JSON APIs និង authentication features។',
                    'បានអភិវឌ្ឍជំនាញ mobile development តាមរយៈ app structure, component organization និង user experience details។',
                ],
            ],
            [
                'period' => '2025 - បច្ចុប្បន្ន',
                'title' => 'Full Stack Developer',
                'meta' => 'Personal projects និងចំណេះដឹងថ្មីៗ',
                'side' => 'left',
                'points' => [
                    'បានបង្កើត project ពេញលេញជាចម្បងដោយប្រើ Laravel សម្រាប់ back end។',
                    'បានធ្វើការលើ QR Code payments (ABA/Bakong), webhooks និង Telegram Bot services។',
                    'បាន implement shopping cart design, OAuth, order systems, notification systems និង guards សម្រាប់ authentication management។',
                ],
            ],
            [
                'period' => '2024 - 2025',
                'title' => 'Back-End Development',
                'meta' => 'Self-Learning និង Project-Based Practice',
                'side' => 'right',
                'points' => [
                    'បានប្រើ Git និង GitHub សម្រាប់ code management និង deploy projects ទៅ Vercel។',
                    'បានបង្កើត website ដោយប្រើ AJAX និង JSON សម្រាប់ fetch និង send data ពី MySQL។',
                    'បានសរសេរ back-end APIs ដោយប្រើ PHP (MVC - OOP) និង Laravel framework។',
                    'បានភ្ជាប់ MySQL ជាមួយ Laravel សម្រាប់ CRUD operations និង data management។',
                    'បានអនុវត្ត authentication, session handling, form validation និង REST APIs ជាមួយ JSON។',
                ],
            ],
            [
                'period' => '2023 - 2024',
                'title' => 'Front-End Development',
                'meta' => 'បានសិក្សានៅ ETEC និង Self-Learning',
                'side' => 'left',
                'points' => [
                    'បានរៀន និងអនុវត្ត HTML, CSS, JavaScript, Bootstrap និង jQuery ដើម្បីបង្កើត smart user interfaces។',
                    'បានបង្កើត website ដែលមាន responsive design និង interactive functions។',
                ],
            ],
            [
                'period' => '2022 - 2026',
                'title' => 'បរិញ្ញាបត្រ Computer Science',
                'meta' => 'សាកលវិទ្យាល័យភូមិន្ទភ្នំពេញ',
                'side' => 'right',
                'points' => [
                    'បានសិក្សាមុខវិជ្ជាសំខាន់ៗក្នុង computer science ដូចជា Data Structures, Web Programming និង System Analysis។',
                    'បានផ្តោតលើជំនាញដែលសមស្របនឹងសមត្ថភាពខ្លួន គឺ Web Programming។',
                ],
            ],
            [
                'period' => '2019 - 2022',
                'title' => 'សញ្ញាបត្រមធ្យមសិក្សាទុតិយភូមិ',
                'meta' => 'វិទ្យាល័យអូរតាប៉ោង (ពោធិ៍សាត់)',
                'side' => 'left',
                'points' => [
                    'បានផ្តោតលើមុខវិជ្ជាវិទ្យាសាស្រ្ត រួមមាន physics, mathematics និង chemistry។',
                    'បានបញ្ចប់ការសិក្សាជោគជ័យក្នុងការប្រឡងជាតិថ្នាក់ទី 12 ឆ្នាំ 2022។',
                ],
            ],
        ]
        : [
            [
                'period' => '2026 - Present',
                'title' => 'App Developer (Flutter)',
                'meta' => 'Learning mobile app development with real practice',
                'side' => 'right',
                'points' => [
                    'Started building mobile applications using Flutter with focus on clean UI, responsive layouts, and reusable widgets.',
                    'Practiced state management, API integration, form handling, and navigation flow for mobile app screens.',
                    'Learned how to connect Flutter apps with back-end services, JSON APIs, and authentication features.',
                    'Improved mobile development skills by working on app structure, component organization, and user experience details.',
                ],
            ],
            [
                'period' => '2025 - Present',
                'title' => 'Full Stack Developer',
                'meta' => 'Personal projects and new knowledge',
                'side' => 'left',
                'points' => [
                    'Built complete projects mostly using Laravel for the back end.',
                    'Worked on QR Code payments (ABA/Bakong), webhooks, and Telegram Bot services.',
                    'Implemented shopping cart design, OAuth, order systems, notification systems, and guards for authentication management.',
                ],
            ],
            [
                'period' => '2024 - 2025',
                'title' => 'Back-End Development',
                'meta' => 'Self-Learning & Project-Based Practice',
                'side' => 'right',
                'points' => [
                    'Used Git and GitHub for code management and deploying projects to Vercel.',
                    'Built websites using AJAX and JSON to fetch and send data from MySQL.',
                    'Wrote back-end APIs using PHP (MVC - OOP) and the Laravel framework.',
                    'Connected MySQL with Laravel for CRUD operations and data management.',
                    'Practiced authentication, session handling, form validation, and REST APIs with JSON.',
                ],
            ],
            [
                'period' => '2023 - 2024',
                'title' => 'Front-End Development',
                'meta' => 'Have studied at ETEC and Self-Learning',
                'side' => 'left',
                'points' => [
                    'Learned and practiced HTML, CSS, JavaScript, Bootstrap, and jQuery to build smart user interfaces.',
                    'Built websites with responsive design and interactive functions.',
                ],
            ],
            [
                'period' => '2022 - 2026',
                'title' => 'Bachelor of Computer Science',
                'meta' => 'Royal University of Phnom Penh',
                'side' => 'right',
                'points' => [
                    'Studied major computer science subjects such as Data Structures, Web Programming, and System Analysis.',
                    'Focused on a skill that matched my ability, which is Web Programming.',
                ],
            ],
            [
                'period' => '2019 - 2022',
                'title' => 'High School Diploma',
                'meta' => 'O Ta Pong High School (Pursat)',
                'side' => 'left',
                'points' => [
                    'Focused on science subjects, physics, mathematics, and chemistry.',
                    'Successfully graduated in the 2022 high school national examination.',
                ],
            ],
        ];
@endphp

@section('content')
    <style>
        .about-profile {
            width: min(1120px, calc(100% - 28px));
            margin: 0 auto;
            padding: 34px 0 64px;
        }

        .about-profile__shell {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            gap: 40px;
            align-items: start;
        }

        .about-profile__identity {
            position: sticky;
            top: 110px;
            text-align: center;
        }

        .about-profile__avatar-wrap {
            position: relative;
            width: 230px;
            height: 270px;
            margin: 0 auto 18px;
            border-radius: 24px;
            overflow: hidden;
            padding: 3px;
            background: transparent;
        }

        .about-profile__avatar-wrap::after {
            content: "";
            position: absolute;
            inset: -30%;
            background: conic-gradient(
                from 0deg,
                transparent 0deg 230deg,
                rgba(10, 47, 107, 0.12) 230deg 252deg,
                #0a2f6b 252deg 282deg,
                #fe1707 282deg 318deg,
                rgba(254, 23, 7, 0.16) 318deg 336deg,
                transparent 336deg 360deg
            );
            animation: aboutProfileBorderLoop 2.8s linear infinite;
            z-index: 0;
        }

        .about-profile__avatar-wrap::before {
            content: "";
            position: absolute;
            inset: 3px;
            border-radius: 21px;
            background: #ffffff;
            z-index: 1;
        }

        .about-profile__avatar {
            position: relative;
            z-index: 2;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center top;
            border-radius: 21px;
            display: block;
        }

        @keyframes aboutProfileBorderLoop {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .about-profile__role {
            margin: 0;
            color: #526277;
            font-size: 0.98rem;
            font-weight: 600;
        }

        .about-profile__experience {
            margin: 16px auto 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 36px;
            padding: 0 16px;
            border-radius: 999px;
            background: #eef4ff;
            color: #1d4ed8;
            font-size: 0.82rem;
            font-weight: 800;
        }

        .about-profile__content {
            display: grid;
            gap: 24px;
        }

        .about-profile__intro {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(320px, 0.95fr);
            gap: 24px;
            align-items: start;
        }

        .about-profile__copy,
        .about-profile__summary-card {
            display: grid;
            gap: 14px;
        }

        .about-profile__kicker {
            color: #2563eb;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .about-profile__title {
            margin: 0;
            color: #0f172a;
            font-size: 1.48rem;
            font-weight: 850;
            line-height: 1.18;
            letter-spacing: -0.03em;
        }

        .about-profile__objective {
            margin: 0;
            color: #526277;
            font-size: 0.92rem;
            line-height: 1.78;
        }

        .about-profile__summary {
            display: grid;
            gap: 16px;
        }

        .about-profile__summary-card {
            padding: 18px 18px 17px;
            border-radius: 22px;
            border: 1px solid #dde8f3;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.04);
        }

        .about-profile__summary-card h3 {
            margin: 0;
            color: #0f172a;
            font-size: 1.02rem;
            font-weight: 800;
        }

        .about-profile__summary-card--objective {
            min-height: 100%;
        }

        .about-profile__cert-list {
            display: grid;
            gap: 8px;
        }

        .about-profile__cert-item {
            display: grid;
            grid-template-columns: 18px minmax(0, 1fr);
            gap: 10px;
            align-items: start;
            color: #526277;
            font-size: 0.84rem;
            line-height: 1.55;
        }

        .about-profile__cert-item i {
            color: #2563eb;
            margin-top: 4px;
        }

        .about-profile__timeline {
            position: relative;
            display: grid;
            gap: 16px;
            padding: 18px 0 8px;
        }

        .about-profile__timeline::before {
            content: "";
            position: absolute;
            top: 6px;
            bottom: 10px;
            left: 50%;
            width: 3px;
            border-radius: 999px;
            background: linear-gradient(180deg, rgba(180, 197, 223, 0.95), rgba(205, 217, 236, 0.65));
            transform: translateX(-50%);
        }

        .about-profile__timeline::after {
            content: "";
            position: absolute;
            top: 6px;
            bottom: 10px;
            left: 50%;
            width: 3px;
            border-radius: 999px;
            background:
                linear-gradient(
                    180deg,
                    rgba(0, 56, 226, 0) 0%,
                    rgba(0, 56, 226, 0.20) 12%,
                    rgba(1, 90, 255, 0.96) 34%,
                    rgba(8, 164, 255, 1) 56%,
                    rgba(2, 181, 255, 0.98) 72%,
                    rgba(2, 181, 255, 0.18) 88%,
                    rgba(2, 181, 255, 0) 100%
                );
            background-repeat: no-repeat;
            background-size: 100% 110px;
            background-position: 0 0;
            transform: translateX(-50%);
            animation: aboutTimelineLineFlow 3.8s ease-in-out infinite alternate;
            pointer-events: none;
        }

        @keyframes aboutTimelineLineFlow {
            0% {
                background-position: 0 0;
                opacity: 0.96;
            }

            100% {
                background-position: 0 calc(100% - 110px);
                opacity: 1;
            }
        }

        .about-profile__timeline-item {
            position: relative;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 30px;
            align-items: start;
        }

        .about-profile__timeline-card {
            position: relative;
            padding: 4px 0 0;
        }

        .about-profile__timeline-item--left .about-profile__timeline-card {
            grid-column: 1;
            text-align: left;
        }

        .about-profile__timeline-item--right .about-profile__timeline-card {
            grid-column: 2;
        }

        .about-profile__timeline-card::before {
            content: attr(data-period);
            position: absolute;
            top: -2px;
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 0 10px;
            background: #ffffff;
            font-size: 1.1rem;
            font-weight: 900;
            letter-spacing: -0.02em;
            color: #0f172a;
            z-index: 2;
        }

        .about-profile__timeline-item--left .about-profile__timeline-card::before {
            right: -172px;
        }

        .about-profile__timeline-item--right .about-profile__timeline-card::before {
            left: -172px;
        }

        .about-profile__timeline-dot {
            position: absolute;
            top: 8px;
            left: 50%;
            width: 14px;
            height: 14px;
            border-radius: 999px;
            background: #ffffff;
            border: 3px solid #2563eb;
            box-shadow: 0 0 0 6px rgba(255, 255, 255, 0.96);
            transform: translateX(-50%);
            z-index: 1;
        }

        .about-profile__timeline-title {
            margin: 34px 0 6px;
            color: #0f172a;
            font-size: 1.02rem;
            font-weight: 800;
            line-height: 1.3;
            text-align: left;
        }

        .about-profile__timeline-meta {
            margin: 0 0 12px;
            color: #64748b;
            font-size: 0.84rem;
            font-weight: 700;
            text-align: left;
        }

        .about-profile__timeline-points {
            margin: 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 9px;
        }

        .about-profile__timeline-points li {
            position: relative;
            padding-left: 18px;
            color: #526277;
            font-size: 0.87rem;
            line-height: 1.62;
            text-align: left;
            display: block;
        }

        .about-profile__timeline-points li::before {
            content: "";
            position: absolute;
            top: 10px;
            left: 0;
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: #60a5fa;
        }

        @media (max-width: 1080px) {
            .about-profile__shell {
                grid-template-columns: 1fr;
            }

            .about-profile__identity {
                position: static;
                max-width: 320px;
                margin: 0 auto;
            }

            .about-profile__intro {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 900px) {
            .about-profile__timeline::before {
                left: 8px;
                transform: none;
            }

            .about-profile__timeline::after {
                left: 8px;
                transform: none;
            }

            .about-profile__timeline-item {
                grid-template-columns: 1fr;
                gap: 0;
                padding-left: 34px;
            }

            .about-profile__timeline-item--left .about-profile__timeline-card,
            .about-profile__timeline-item--right .about-profile__timeline-card {
                grid-column: auto;
                text-align: left;
            }

            .about-profile__timeline-dot {
                left: 1px;
                transform: none;
            }

            .about-profile__timeline-card::before {
                position: static;
                display: block;
                margin-bottom: 8px;
            }
        }

        @media (max-width: 768px) {
            .about-profile {
                width: min(100%, calc(100% - 18px));
                padding: 24px 0 52px;
            }

            .about-profile__shell {
                gap: 28px;
            }

            .about-profile__avatar-wrap {
                width: 190px;
                height: 230px;
            }

            .about-profile__title {
                font-size: 1.36rem;
            }

            .about-profile__summary-card {
                padding: 16px;
                border-radius: 20px;
            }
        }
    </style>

    <section class="about-profile">
        <div class="about-profile__shell">
            <aside class="about-profile__identity">
                <div class="about-profile__avatar-wrap">
                    <img src="{{ asset('logo/aboutus.png') }}" alt="Phon Phyron" class="about-profile__avatar">
                </div>
                <p class="about-profile__role">{{ $aboutRole }}</p>
                <span class="about-profile__experience">{{ $aboutExperience }}</span>
            </aside>

            <div class="about-profile__content">
                <div class="about-profile__intro">
                    <div class="about-profile__summary-card about-profile__summary-card--objective">
                        <span class="about-profile__kicker">{{ $aboutKicker }}</span>
                        <h2 class="about-profile__title">{{ $aboutObjectiveTitle }}</h2>
                        <p class="about-profile__objective">{{ $aboutObjectiveCopy }}</p>
                    </div>

                    <div class="about-profile__summary">
                        <div class="about-profile__summary-card">
                            <h3>{{ $certificateTitle }}</h3>
                            <div class="about-profile__cert-list">
                                @foreach ($certificates as $certificate)
                                    <div class="about-profile__cert-item">
                                        <i class="fa-solid fa-award"></i>
                                        <span>{{ $certificate }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="about-profile__timeline">
                    @foreach ($timelineItems as $item)
                        <article class="about-profile__timeline-item about-profile__timeline-item--{{ $item['side'] }}">
                            <span class="about-profile__timeline-dot" aria-hidden="true"></span>
                            <div class="about-profile__timeline-card" data-period="{{ $item['period'] }}">
                                <h3 class="about-profile__timeline-title">{{ $item['title'] }}</h3>
                                <p class="about-profile__timeline-meta">{{ $item['meta'] }}</p>

                                <ul class="about-profile__timeline-points">
                                    @foreach ($item['points'] as $point)
                                        <li>{{ $point }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
