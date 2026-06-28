@extends('web.layouts.app')

@section('title', __('លក្ខខណ្ឌប្រើប្រាស់'))

@php
    $isKhmer = app()->getLocale() === 'km';

    $termsSections = $isKhmer
        ? [
            [
                'title' => '1. ការយល់ព្រមក្នុងការប្រើប្រាស់',
                'body' => 'ដោយការចូលប្រើ និងប្រើប្រាស់ TechCourse អ្នករៀនយល់ព្រមប្រើ website នេះដោយទំនួលខុសត្រូវ និងស្របច្បាប់។ Platform នេះត្រូវបានបង្កើតឡើងសម្រាប់ការសិក្សាជំនាញ IT ជាពិសេសជំនាញអនុវត្តក្នុង web development, app development និង technical skill ពាក់ព័ន្ធផ្សេងៗ។',
            ],
            [
                'title' => '2. គណនីអ្នកប្រើប្រាស់',
                'body' => 'អ្នកប្រើត្រូវទទួលខុសត្រូវក្នុងការរក្សាព័ត៌មានគណនីឲ្យត្រឹមត្រូវ និងមានសុវត្ថិភាព។ Login credential, email access និង third-party login account ដូចជា Telegram, Google ឬ Facebook មិនគួរចែករំលែកឲ្យអ្នកដទៃប្រើប្រាស់ឡើយ។',
            ],
            [
                'title' => '3. ការប្រើប្រាស់មាតិកា',
                'body' => 'មាតិកាវគ្គសិក្សា lesson structure ឧទាហរណ៍ និង content នៅលើ website ត្រូវបានផ្តល់សម្រាប់គោលបំណងសិក្សា។ អ្នកប្រើមិនគួរយកមាតិកាទាំងនេះទៅ misuse, copy, redistribute ឬ resell ដោយគ្មានការអនុញ្ញាតពី TechCourse ទេ។',
            ],
            [
                'title' => '4. អាកប្បកិរិយាក្នុងការប្រើប្រាស់',
                'body' => 'អ្នកប្រើមិនត្រូវប្រើ platform ដើម្បី submit harmful code វាយប្រហារ system security ផ្ញើ spam impersonate អ្នកដទៃ abuse comment ឬរំខានដល់បទពិសោធន៍សិក្សារបស់អ្នករៀនដទៃទៀតឡើយ។',
            ],
            [
                'title' => '5. ការទូទាត់ និងសិទ្ធិចូលប្រើ',
                'body' => 'បើមាន paid learning feature សិទ្ធិចូលប្រើអាចអាស្រ័យលើការបញ្ជាក់ការទូទាត់ដោយជោគជ័យ និងគោលការណ៍ platform ទាក់ទងនឹង enrollment, activation ឬស្ថានភាពសេវាកម្ម។ TechCourse អាចកែសម្រួល access flow ដើម្បីរក្សាភាពត្រឹមត្រូវ និងសុវត្ថិភាពនៃការផ្តល់វគ្គសិក្សា។',
            ],
            [
                'title' => '6. ការធ្វើបច្ចុប្បន្នភាព Platform',
                'body' => 'TechCourse អាចកែលម្អ កែប្រែ ផ្អាក ឬរៀបចំឡើងវិញនូវផ្នែកខ្លះនៃ website, learning flow, design, feature ឬ integration នៅពេលចាំបាច់ សម្រាប់ maintenance, security, performance ឬបទពិសោធន៍សិស្សដែលប្រសើរជាងមុន។',
            ],
            [
                'title' => '7. កំណត់ការទទួលខុសត្រូវ',
                'body' => 'យើងខិតខំផ្តល់ learning content ដែលមានប្រយោជន៍ និងអាចអនុវត្តបាន ប៉ុន្តែមិនអាចធានាបានថា lesson, code sample, third-party integration ឬ technical service ទាំងអស់នឹងដំណើរការល្អជានិច្ចដោយគ្មានបញ្ហានៅគ្រប់ environment ទាំងអស់ឡើយ។',
            ],
            [
                'title' => '8. ការកែប្រែលក្ខខណ្ឌ',
                'body' => 'លក្ខខណ្ឌទាំងនេះអាចត្រូវបានធ្វើបច្ចុប្បន្នភាពជាបន្តបន្ទាប់ ដើម្បីឲ្យសមស្របនឹងការរីកចម្រើនរបស់ TechCourse, learning service និង technical feature របស់វា។ ការបន្តប្រើប្រាស់ platform មានន័យថាអ្នកយល់ព្រមលើលក្ខខណ្ឌដែលបានកែប្រែ។',
            ],
        ]
        : [
            [
                'title' => '1. Acceptance of Use',
                'body' => 'By accessing and using TechCourse, learners agree to use the website in a responsible and lawful way. The platform is intended for IT learning, especially practical knowledge in web development, app development, and related technical skills.',
            ],
            [
                'title' => '2. User Accounts',
                'body' => 'Users are responsible for keeping their account details accurate and secure. Login credentials, email access, and third-party login accounts such as Telegram, Google, or Facebook should not be shared with others.',
            ],
            [
                'title' => '3. Use of Content',
                'body' => 'Course materials, lesson structure, examples, and website content are provided for learning purposes. Users should not misuse, copy, redistribute, or resell protected learning content without permission from TechCourse.',
            ],
            [
                'title' => '4. Acceptable Behavior',
                'body' => 'Users must not use the platform to submit harmful code, attack system security, send spam, impersonate others, abuse comments, or disrupt the learning experience of other students.',
            ],
            [
                'title' => '5. Payment and Access',
                'body' => 'If paid learning features are offered, access may depend on successful payment verification and the platform rules related to enrollment, activation, or service availability. TechCourse may adjust access flow to keep course delivery accurate and secure.',
            ],
            [
                'title' => '6. Platform Updates',
                'body' => 'TechCourse may improve, modify, suspend, or reorganize parts of the website, learning flow, design, features, or integrations when necessary for maintenance, security, performance, or better student experience.',
            ],
            [
                'title' => '7. Liability Limitation',
                'body' => 'We aim to provide useful and practical learning content, but we cannot guarantee that every lesson, code sample, third-party integration, or technical service will always be uninterrupted or error-free in every environment.',
            ],
            [
                'title' => '8. Terms Updates',
                'body' => 'These Terms may be updated from time to time to match the growth of TechCourse, its learning services, and technical features. Continued use of the platform means the updated terms are accepted.',
            ],
        ];

    $heroTitle = $isKhmer ? 'លក្ខខណ្ឌប្រើប្រាស់' : 'Terms & Conditions';
    $heroCopy = $isKhmer
        ? 'លក្ខខណ្ឌទាំងនេះពិពណ៌នាអំពីច្បាប់មូលដ្ឋានសម្រាប់ការប្រើប្រាស់ TechCourse ជា website សិក្សាជំនាញ IT។ គោលបំណងគឺការពារ platform គាំទ្របរិយាកាសសិក្សាដែលគោរពគ្នា និងធានាថាការប្រើប្រាស់សមស្របនឹងគោលបំណងសិក្សា app និង web development។'
        : 'These terms describe the basic rules for using TechCourse as an IT learning website. The goal is to protect the platform, support a respectful learning environment, and make sure usage matches the purpose of learning app and web development skills.';

    $asideCards = $isKhmer
        ? [
            [
                'title' => 'គោលដៅការសិក្សា',
                'body' => 'TechCourse ត្រូវបានបង្កើតសម្រាប់ការសិក្សាបែបអនុវត្ត មិនមែនសម្រាប់ការប្រើប្រាស់ខុសបំណងលើ source code, service ឬ resource របស់ platform ទេ។',
            ],
            [
                'title' => 'ការប្រើប្រាស់ដោយទំនួលខុសត្រូវ',
                'body' => 'អ្នករៀនគ្រប់រូបគួរប្រើ platform នេះដោយការគោរព មានសុវត្ថិភាព និងសមស្របនឹងគោលបំណងសិក្សា។',
            ],
        ]
        : [
            [
                'title' => 'Learning Focus',
                'body' => 'TechCourse is built for practical study, not misuse of source code, services, or platform resources.',
            ],
            [
                'title' => 'Responsible Usage',
                'body' => 'Every learner should use the platform respectfully, securely, and according to the intended educational purpose.',
            ],
        ];
    $updatedLabel = $isKhmer ? 'កែប្រែចុងក្រោយ' : 'Last Updated';
@endphp

@section('content')
    <style>
        .terms-page {
            width: 100%;
            margin: 0 auto;
            margin-top: -102px;
            padding: 0 0 90px;
            color: #0f172a;
            background: #ffffff;
        }

        .terms-hero {
            min-height: 460px;
            padding: 172px 24px 84px;
            background: #111111;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            width: 100vw;
            margin-left: calc(50% - 50vw);
        }

        .terms-hero__title {
            margin: 0;
            color: #ffffff;
            font-size: clamp(2.3rem, 4.8vw, 4.6rem);
            line-height: 0.98;
            letter-spacing: 0.02em;
            font-weight: 900;
            text-transform: uppercase;
            font-family: 'Gagalin', var(--font-lato);
        }

        .terms-hero__copy {
            margin: 26px 0 0;
            max-width: 700px;
            color: rgba(255, 255, 255, 0.68);
            font-size: 0.96rem;
            line-height: 1.7;
        }

        .terms-content {
            width: min(900px, calc(100% - 44px));
            margin: 0 auto;
        }

        .terms-meta {
            padding: 54px 0 8px;
            color: #666666;
            font-size: 0.98rem;
            font-weight: 600;
            border-bottom: 2px solid #e5e5e5;
        }

        .terms-aside {
            padding-top: 52px;
            display: grid;
            gap: 0;
        }

        .terms-aside__card {
            padding: 0 0 40px;
            border: 0;
            border-bottom: 1px solid #e7e7e7;
            background: transparent;
            box-shadow: none;
        }

        .terms-aside__card strong {
            display: block;
            margin-bottom: 12px;
            color: #111111;
            font-size: 0.98rem;
            font-weight: 900;
            text-transform: uppercase;
            font-family: 'Gagalin', var(--font-lato);
        }

        .terms-aside__card p {
            margin: 0;
            color: #4a4a4a;
            font-size: 0.92rem;
            line-height: 1.85;
        }

        .terms-grid {
            padding-top: 18px;
            display: grid;
            gap: 0;
        }

        .terms-card {
            padding: 56px 0 54px;
            border-top: 1px solid #e7e7e7;
            background: transparent;
        }

        .terms-card h2 {
            margin: 0 0 26px;
            color: #111111;
            font-size: 1.5rem;
            line-height: 1;
            font-weight: 900;
            text-transform: uppercase;
            font-family: 'Gagalin', var(--font-lato);
        }

        .terms-card p {
            margin: 0;
            color: #3f3f3f;
            font-size: 0.95rem;
            line-height: 1.7;
        }

        @media (max-width: 900px) {
            .terms-aside {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .terms-page {
                margin-top: -84px;
                padding-bottom: 62px;
            }

            .terms-hero {
                min-height: 360px;
                padding: 136px 18px 64px;
            }

            .terms-content {
                width: min(100%, calc(100% - 28px));
            }

            .terms-aside {
                padding-top: 38px;
            }

            .terms-card {
                padding: 40px 0;
            }
        }
    </style>

    <section class="terms-page">
        <div class="terms-hero">
            <div>
                <h1 class="terms-hero__title">{{ $heroTitle }}</h1>
                <p class="terms-hero__copy">{{ $heroCopy }}</p>
            </div>
        </div>

        <div class="terms-content">
            <div class="terms-meta">{{ $updatedLabel }}: {{ now()->format('F d, Y') }}</div>

            <div class="terms-aside">
                @foreach ($asideCards as $card)
                    <div class="terms-aside__card">
                        <strong>{{ $card['title'] }}</strong>
                        <p>{{ $card['body'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="terms-grid">
                @foreach ($termsSections as $section)
                    <article class="terms-card">
                        <h2>{{ $section['title'] }}</h2>
                        <p>{{ $section['body'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
