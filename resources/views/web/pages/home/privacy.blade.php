@extends('web.layouts.app')

@section('title', __('គោលការណ៍ឯកជនភាព'))

@php
    $isKhmer = app()->getLocale() === 'km';

    $privacySections = $isKhmer
        ? [
            [
                'title' => '1. ការប្រមូលព័ត៌មាន',
                'body' => 'TechCourse អាចប្រមូលព័ត៌មានផ្ទាល់ខ្លួនដែលអ្នករៀនផ្តល់ជូនដោយផ្ទាល់ ដូចជា ឈ្មោះ អ៊ីមែល ព័ត៌មានចូលគណនី រូបភាព profile និងសកម្មភាពសិក្សា។ យើងក៏អាចប្រមូលព័ត៌មានបច្ចេកទេសមួយចំនួន ដើម្បីជួយឲ្យ website ដំណើរការបានត្រឹមត្រូវ ពង្រឹងសុវត្ថិភាព និងយល់ពីបទពិសោធន៍អ្នកប្រើកាន់តែប្រសើរ។',
            ],
            [
                'title' => '2. របៀបប្រើប្រាស់ព័ត៌មាន',
                'body' => 'យើងប្រើព័ត៌មានដែលបានប្រមូល ដើម្បីបង្កើត និងគ្រប់គ្រងគណនីអ្នករៀន ផ្តល់សិទ្ធិចូលប្រើវគ្គសិក្សា app និង web development កែលម្អប្រសិទ្ធភាព website ផ្ញើការជូនដំណឹងពាក់ព័ន្ធនឹងការសិក្សា គាំទ្រ login និង security feature និងរក្សាគុណភាពសេវាកម្មរបស់ TechCourse។',
            ],
            [
                'title' => '3. ការការពារគណនី',
                'body' => 'យើងខិតខំការពារទិន្នន័យអ្នកប្រើ session គណនី និង authentication flow ដោយសមហេតុផល។ អ្នករៀនក៏ត្រូវទទួលខុសត្រូវក្នុងការរក្សាពាក្យសម្ងាត់ អ៊ីមែល Telegram login និងការចូលប្រើឧបករណ៍របស់ខ្លួនឲ្យមានសុវត្ថិភាពផងដែរ។',
            ],
            [
                'title' => '4. Cookies និង Session',
                'body' => 'TechCourse អាចប្រើ cookies, session storage និង authentication token ដើម្បីរក្សាស្ថានភាព login ចងចាំ preference ការពារ request និងកែលម្អ learning flow របស់ website ជាទូទៅ។',
            ],
            [
                'title' => '5. ការចែករំលែកព័ត៌មាន',
                'body' => 'យើងមិនមានគោលបំណងលក់ព័ត៌មានផ្ទាល់ខ្លួនរបស់អ្នករៀនទេ។ ព័ត៌មានអាចត្រូវបានចែករំលែកតែពេលចាំបាច់សម្រាប់ platform service សំខាន់ៗ សុវត្ថិភាព ការអនុលោមតាមច្បាប់ ឬ technical integration ដែលគាំទ្រដល់ការដំណើរការធម្មតារបស់ learning system ប៉ុណ្ណោះ។',
            ],
            [
                'title' => '6. ទិន្នន័យការសិក្សា',
                'body' => 'យើងអាចរក្សាទុកព័ត៌មានទាក់ទងនឹងការចុះឈ្មោះវគ្គសិក្សា ការរក្សាទុក progress comment និង notification ដើម្បីឲ្យអ្នករៀនបន្តការសិក្សាបានរលូន និងឲ្យ website អាចផ្តល់បទពិសោធន៍ផ្ទាល់ខ្លួនបានកាន់តែប្រសើរ។',
            ],
            [
                'title' => '7. តំណភ្ជាប់ទៅ third-party services',
                'body' => 'Feature មួយចំនួនអាចភ្ជាប់ទៅកាន់ third-party service ដូចជា Google, Facebook, Telegram, email system, payment gateway ឬ tool ផ្សេងៗ។ Platform ទាំងនោះអាចមាន privacy practice របស់ខ្លួន ដូច្នេះអ្នករៀនគួរតែពិនិត្យ policy របស់ពួកវាផងដែរ។',
            ],
            [
                'title' => '8. ការកែប្រែគោលការណ៍',
                'body' => 'TechCourse អាចធ្វើបច្ចុប្បន្នភាពលើគោលការណ៍ឯកជនភាពនេះ នៅពេល website, learning service, authentication flow ឬតម្រូវការផ្នែកច្បាប់ និងការគ្រប់គ្រងមានការផ្លាស់ប្តូរ។ ការបន្តប្រើប្រាស់ platform មានន័យថាអ្នករៀនយល់ព្រមលើ policy ដែលបានកែប្រែ។',
            ],
        ]
        : [
            [
                'title' => '1. Information Collection',
                'body' => 'TechCourse may collect personal information that learners provide directly, such as name, email address, login details, profile image, and learning activity. We also collect technical usage information that helps the website function correctly, improve security, and understand learner experience better.',
            ],
            [
                'title' => '2. How We Use Information',
                'body' => 'We use collected information to create and manage learner accounts, provide access to app and web development courses, improve site performance, send learning-related notifications, support login and security features, and maintain the quality of the TechCourse platform.',
            ],
            [
                'title' => '3. Account Protection',
                'body' => 'We take reasonable steps to protect user data, account sessions, and authentication flow. Learners are also responsible for keeping their password, email access, Telegram login, and device access secure.',
            ],
            [
                'title' => '4. Cookies and Sessions',
                'body' => 'TechCourse may use cookies, session storage, and authentication tokens to keep users signed in, remember preferences, secure requests, and improve the overall learning flow of the website.',
            ],
            [
                'title' => '5. Information Sharing',
                'body' => 'We do not intentionally sell learner personal information. Information may only be shared when needed for essential platform services, security, legal compliance, or technical integrations that support the normal operation of the learning system.',
            ],
            [
                'title' => '6. Learning Data',
                'body' => 'We may store information related to course enrollment, saved items, progress, comments, and notifications so learners can continue studying smoothly and the website can provide a better personalized experience.',
            ],
            [
                'title' => '7. Third-Party Services',
                'body' => 'Some features may connect to third-party services such as Google, Facebook, Telegram, email systems, payment gateways, or other tools. Their platforms may have their own privacy practices, so learners should also review those policies when using those services.',
            ],
            [
                'title' => '8. Policy Updates',
                'body' => 'TechCourse may update this Privacy Policy when the website, learning services, authentication flow, or legal and operational requirements change. Continued use of the platform means the learner accepts the updated policy.',
            ],
        ];

    $heroTitle = $isKhmer ? 'គោលការណ៍ឯកជនភាព' : 'Privacy Policy';
    $heroCopy = $isKhmer
        ? 'គោលការណ៍នេះពន្យល់ពីរបៀបដែល TechCourse អាចប្រមូល ប្រើប្រាស់ រក្សាទុក និងការពារព័ត៌មានពាក់ព័ន្ធនឹងអ្នករៀនដែលប្រើ platform សិក្សា IT របស់យើង សម្រាប់ web development និង mobile app development។ យើងចង់ឲ្យ policy នេះមានភាពច្បាស់ អនុវត្តបាន និងសមស្របនឹង feature ពិតរបស់ website។'
        : 'This Privacy Policy explains how TechCourse may collect, use, store, and protect information related to learners using our IT learning platform for web and mobile app development. We want the policy to stay clear, practical, and suitable for the real features of this website.';

    $importantTitle = $isKhmer ? 'ចំណាំសំខាន់' : 'Important Note';
    $importantBody = $isKhmer
        ? 'ដោយបន្តប្រើប្រាស់ TechCourse អ្នករៀនទទួលស្គាល់ថា ទិន្នន័យផ្ទាល់ខ្លួន និងទិន្នន័យបច្ចេកទេសមួយចំនួន អាចត្រូវបានដំណើរការសម្រាប់ account access, learning delivery, security និងការកែលម្អសេវាកម្ម។ បើ system integration សំខាន់ៗមានការផ្លាស់ប្តូរនាពេលអនាគត ទំព័រនេះអាចត្រូវបានធ្វើបច្ចុប្បន្នភាព។'
        : 'By continuing to use TechCourse, learners acknowledge that some personal and technical data may be processed for account access, learning delivery, security, and service improvement. If sensitive system integrations change in the future, this page may be updated to reflect those changes.';
    $updatedLabel = $isKhmer ? 'កែប្រែចុងក្រោយ' : 'Last Updated';
@endphp

@section('content')
    <style>
        .policy-page {
            width: 100%;
            margin: 0 auto;
            margin-top: -102px;
            padding: 0 0 90px;
            color: #0f172a;
            background: #ffffff;
        }

        .policy-hero {
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

        .policy-hero__title {
            margin: 0;
            color: #ffffff;
            font-size: clamp(2.3rem, 4.8vw, 4.6rem);
            line-height: 0.98;
            letter-spacing: 0.02em;
            font-weight: 900;
            text-transform: uppercase;
            font-family: 'Gagalin', var(--font-lato);
        }

        .policy-hero__copy {
            margin: 26px 0 0;
            max-width: 700px;
            color: rgba(255, 255, 255, 0.68);
            font-size: 0.96rem;
            line-height: 1.7;
        }

        .policy-content {
            width: min(900px, calc(100% - 44px));
            margin: 0 auto;
        }

        .policy-meta {
            padding: 54px 0 8px;
            color: #666666;
            font-size: 0.98rem;
            font-weight: 600;
            border-bottom: 2px solid #e5e5e5;
        }

        .policy-grid {
            display: grid;
            gap: 0;
        }

        .policy-card {
            padding: 56px 0 54px;
            border-top: 1px solid #e7e7e7;
            background: transparent;
        }

        .policy-card h2 {
            margin: 0 0 26px;
            color: #111111;
            font-size: 1.5rem;
            font-weight: 900;
            line-height: 1;
            text-transform: uppercase;
            font-family: 'Gagalin', var(--font-lato);
        }

        .policy-card p,
        .policy-note p {
            margin: 0;
            color: #3f3f3f;
            font-size: 0.95rem;
            line-height: 1.7;
        }

        .policy-note {
            padding: 56px 0 0;
            border-top: 1px solid #e7e7e7;
            background: transparent;
        }

        .policy-note strong {
            display: block;
            margin-bottom: 22px;
            color: #111111;
            font-size: 1.5rem;
            line-height: 1;
            font-weight: 900;
            text-transform: uppercase;
            font-family: 'Gagalin', var(--font-lato);
        }

        @media (max-width: 768px) {
            .policy-page {
                margin-top: -84px;
                padding-bottom: 62px;
            }

            .policy-hero {
                min-height: 360px;
                padding: 136px 18px 64px;
            }

            .policy-grid,
            .policy-content {
                width: min(100%, calc(100% - 28px));
            }

            .policy-card,
            .policy-note {
                padding-top: 40px;
            }

            .policy-card {
                padding-bottom: 40px;
            }
        }
    </style>

    <section class="policy-page">
        <div class="policy-hero">
            <h1 class="policy-hero__title">{{ $heroTitle }}</h1>
            <p class="policy-hero__copy">{{ $heroCopy }}</p>
        </div>

        <div class="policy-content">
            <div class="policy-meta">{{ $updatedLabel }}: {{ now()->format('F d, Y') }}</div>

            <div class="policy-grid">
                @foreach ($privacySections as $section)
                    <article class="policy-card">
                        <h2>{{ $section['title'] }}</h2>
                        <p>{{ $section['body'] }}</p>
                    </article>
                @endforeach
            </div>

            <div class="policy-note">
                <strong>{{ $importantTitle }}</strong>
                <p>{{ $importantBody }}</p>
            </div>
        </div>
    </section>
@endsection
