@extends('web.layouts.app')

@section('title', __('សំណួរញឹកញាប់'))

@php
    $isKhmer = app()->getLocale() === 'km';

    $faqItems = $isKhmer
        ? [
            [
                'question' => 'TechCourse មានបង្រៀនអ្វីខ្លះ?',
                'answer' => 'TechCourse ផ្តោតលើការបង្រៀនជំនាញ IT ដែលអាចអនុវត្តបាន សម្រាប់ web development និង app development។ អ្នករៀនអាចសិក្សា HTML, CSS, JavaScript, PHP, Laravel, Flutter, API integration, authentication flow, database design និង project structure ដែលប្រើប្រាស់ក្នុងការងារពិត។',
            ],
            [
                'question' => 'អ្នកចាប់ផ្តើមពីសូន្យអាចរៀនបានទេ?',
                'answer' => 'អាចបាន។ ផ្លូវសិក្សាត្រូវបានរៀបចំពីមូលដ្ឋានទៅការអនុវត្តជាក់ស្តែងជាដំណាក់កាល ដើម្បីឲ្យអ្នកចាប់ផ្តើមថ្មីអាចយល់បានងាយ និងបង្កើនទំនុកចិត្តបន្តិចម្តងៗ។',
            ],
            [
                'question' => 'វគ្គសិក្សាមាន practice project ដែរឬទេ?',
                'answer' => 'មាន។ Platform នេះរៀបចំសម្រាប់ការរៀនបែបអនុវត្ត ដូច្នេះអ្នករៀននឹងបានធ្វើ mini project និង workflow ឧទាហរណ៍ពិតៗ ទាំងផ្នែក web system និង mobile app feature មិនមែនមានត្រឹម theory ប៉ុណ្ណោះទេ។',
            ],
            [
                'question' => 'ខ្ញុំអាចរៀន Web និង App development នៅពេលតែមួយបានទេ?',
                'answer' => 'បាន។ ប៉ុន្តែបើអ្នកទើបចាប់ផ្តើម គួរចាប់ពី track មួយជាមុនសិន ជាទូទៅគឺ web fundamentals បន្ទាប់មកទើបបន្តទៅ back-end ឬ mobile app development។ បើអ្នកមានមូលដ្ឋានខ្លះហើយ អាចរៀនទាំងពីរបានជាមួយគ្នា។',
            ],
            [
                'question' => 'តើមានបង្រៀន API និង Database ដែរឬទេ?',
                'answer' => 'មាន។ TechCourse គ្របដណ្តប់លើ database structure, CRUD flow, authentication logic, REST API usage, JSON response handling និងការភ្ជាប់រវាង front-end, back-end និង app client។',
            ],
            [
                'question' => 'Course content សមរម្យសម្រាប់ freelance ឬ job preparation ដែរឬទេ?',
                'answer' => 'សមរម្យ។ ខ្លឹមសារត្រូវបានរៀបចំដើម្បីជួយឲ្យអ្នករៀនពង្រឹងជំនាញអនុវត្ត សម្រាប់ internship, freelance project, portfolio building និងការត្រៀមខ្លួនសម្រាប់ junior developer ក្នុង app និង web development។',
            ],
            [
                'question' => 'ខ្ញុំត្រូវការកុំព្យូទ័រ spec ខ្ពស់ដែរឬទេ?',
                'answer' => 'មិនចាំបាច់ខ្លាំងពេកទេ។ សម្រាប់ web learning កុំព្យូទ័រធម្មតាក៏អាចប្រើបាន។ ចំណែក app development ជាពិសេសពេលប្រើ emulator បើ RAM និង CPU ល្អជាង នឹងជួយឲ្យការងាររលូនជាងមុន។',
            ],
            [
                'question' => 'បើជួបបញ្ហាក្នុងការរៀន តើមានវិធីណាសុំជំនួយ?',
                'answer' => 'អ្នកអាចមើល guidance, project example និង support page របស់ platform ដើម្បីយល់ flow ឲ្យច្បាស់ជាមុន។ ពេលមានបញ្ហា គួរប្រៀបធៀប output ម្តងមួយជំហាន ពិនិត្យ code ឲ្យបានលម្អិត និង debug តាមលំដាប់។',
            ],
        ]
        : [
            [
                'question' => 'What does TechCourse teach?',
                'answer' => 'TechCourse focuses on practical IT learning for web and app development. Students can learn HTML, CSS, JavaScript, PHP, Laravel, Flutter, API integration, authentication flow, database design, and real project structure used in modern development.',
            ],
            [
                'question' => 'Can complete beginners learn here?',
                'answer' => 'Yes. The learning path is suitable for beginners and grows step by step from foundation to real project practice. We aim to explain both concept and implementation clearly so learners can build confidence gradually.',
            ],
            [
                'question' => 'Do the courses include practice projects?',
                'answer' => 'Yes. The platform is designed around practical learning, so students are expected to build mini projects and real workflow examples for both web systems and mobile app features, not only theory.',
            ],
            [
                'question' => 'Can I study web and app development at the same time?',
                'answer' => 'Yes. If you are new, it is best to start with one track first, usually web fundamentals, then move into back-end or mobile app development. If you already have some basics, you can study both together in parallel.',
            ],
            [
                'question' => 'Does TechCourse teach API and database skills?',
                'answer' => 'Yes. TechCourse covers database structure, CRUD flow, authentication logic, REST API usage, JSON response handling, and connection between front-end, back-end, and app clients.',
            ],
            [
                'question' => 'Is the content suitable for freelance work or job preparation?',
                'answer' => 'Yes. The content is prepared to help learners improve practical skills for internships, freelance projects, portfolio building, and junior-level developer work in app and web development.',
            ],
            [
                'question' => 'Do I need a high-spec computer?',
                'answer' => 'Not necessarily. A normal development laptop can be enough for most web learning tasks. For app development, especially emulator usage, better RAM and CPU will help, but many lessons can still be followed on moderate hardware.',
            ],
            [
                'question' => 'What should I do if I get stuck while learning?',
                'answer' => 'You can use the platform guidance, project examples, and support pages to understand the expected learning flow. Learners should compare output carefully, review code step by step, and use structured debugging when something does not work.',
            ],
        ];

    $heroTitle = $isKhmer ? 'សំណួរញឹកញាប់' : 'Frequently Asked Questions';
    $heroCopy = $isKhmer
        ? 'ខាងក្រោមនេះគឺជាសំណួរដែលអ្នករៀនសួរញឹកញាប់អំពីការសិក្សាជំនាញ IT នៅលើ TechCourse។ ចម្លើយត្រូវបានរៀបចំឲ្យសមស្របសម្រាប់ការរៀន app development និង web development បែបអនុវត្តជាក់ស្តែង។'
        : 'Here are the common questions learners usually ask about studying IT skills on TechCourse. The answers focus on practical app and web development learning so students can understand the flow clearly before starting.';

    $miniCards = $isKhmer
        ? [
            [
                'title' => 'ការរៀនបែបអនុវត្ត',
                'body' => 'ផ្តោតលើការរៀនសម្រាប់ web development, mobile app, API integration និង real project workflow។',
            ],
            [
                'title' => 'រៀនជាដំណាក់កាល',
                'body' => 'សំណួរ និងចម្លើយត្រូវបានសរសេរឲ្យសមស្របទាំងសម្រាប់អ្នកចាប់ផ្តើម និងអ្នកដែលចង់ពង្រឹងទិសដៅជំនាញ។',
            ],
        ]
        : [
            [
                'title' => 'Practical Learning',
                'body' => 'Focus on hands-on learning for web development, mobile apps, API integration, and real project workflow.',
            ],
            [
                'title' => 'Step by Step',
                'body' => 'Questions and answers are written for both beginners and learners who want to improve their skill direction.',
            ],
        ];
@endphp

@section('content')
    <style>
        .support-page {
            width: min(1080px, calc(100% - 28px));
            margin: 0 auto;
            padding: 24px 0 70px;
            color: #0f172a;
        }

        .support-hero {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(260px, 0.9fr);
            gap: 26px;
            padding: 34px 34px 30px;
            border-radius: 30px;
            border: 1px solid #e0eaf4;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.14), transparent 30%),
                linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
            box-shadow: 0 22px 50px rgba(15, 23, 42, 0.06);
        }

        .support-hero__kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
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

        .support-hero__title {
            margin: 16px 0 10px;
            font-size: clamp(1.9rem, 3vw, 2.7rem);
            line-height: 1.12;
            letter-spacing: -0.04em;
            font-weight: 850;
        }

        .support-hero__copy {
            margin: 0;
            max-width: 640px;
            color: #60738c;
            font-size: 0.98rem;
            line-height: 1.8;
        }

        .support-hero__mini {
            display: grid;
            gap: 14px;
            align-self: end;
        }

        .support-mini-card {
            padding: 18px;
            border-radius: 22px;
            border: 1px solid #dfebf7;
            background: rgba(255, 255, 255, 0.86);
            box-shadow: 0 14px 26px rgba(15, 23, 42, 0.04);
        }

        .support-mini-card strong {
            display: block;
            margin-bottom: 6px;
            font-size: 0.96rem;
        }

        .support-mini-card p {
            margin: 0;
            color: #667892;
            font-size: 0.84rem;
            line-height: 1.7;
        }

        .faq-shell {
            margin-top: 28px;
            display: grid;
            gap: 16px;
        }

        .faq-item {
            border: 1px solid #dfe9f3;
            border-radius: 22px;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.04);
            overflow: hidden;
            transition: border-color 0.25s ease, box-shadow 0.25s ease, transform 0.25s ease;
        }

        .faq-item:hover {
            border-color: #cddff1;
            box-shadow: 0 18px 34px rgba(15, 23, 42, 0.06);
            transform: translateY(-1px);
        }

        .faq-item.is-open {
            border-color: #bfd8f0;
            box-shadow: 0 20px 36px rgba(37, 99, 235, 0.08);
        }

        .faq-toggle {
            width: 100%;
            border: 0;
            background: transparent;
            padding: 22px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            text-align: left;
            cursor: pointer;
            color: #0f172a;
        }

        .faq-toggle__question {
            font-size: 1rem;
            line-height: 1.65;
            font-weight: 800;
        }

        .faq-toggle__icon {
            width: 40px;
            height: 40px;
            border-radius: 14px;
            border: 1px solid #d7e5f2;
            background: #f7fbff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #2563eb;
            flex-shrink: 0;
            transition: transform 0.25s ease, background 0.25s ease, color 0.25s ease;
        }

        .faq-item.is-open .faq-toggle__icon {
            transform: rotate(180deg);
            background: #2563eb;
            color: #ffffff;
        }

        .faq-answer-wrap {
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows 0.28s ease;
        }

        .faq-item.is-open .faq-answer-wrap {
            grid-template-rows: 1fr;
        }

        .faq-answer {
            min-height: 0;
            overflow: hidden;
        }

        .faq-answer__inner {
            padding: 0 24px 22px 24px;
            color: #63758d;
            font-size: 0.93rem;
            line-height: 1.85;
        }

        .faq-answer__inner p {
            margin: 0;
        }

        @media (max-width: 900px) {
            .support-hero {
                grid-template-columns: 1fr;
                padding: 26px 22px;
            }
        }

        @media (max-width: 768px) {
            .support-page {
                width: min(100%, calc(100% - 18px));
                padding: 16px 0 56px;
            }

            .support-hero {
                border-radius: 24px;
                gap: 20px;
            }

            .faq-toggle {
                padding: 18px 18px;
                gap: 14px;
            }

            .faq-toggle__question {
                font-size: 0.94rem;
            }

            .faq-toggle__icon {
                width: 36px;
                height: 36px;
                border-radius: 12px;
            }

            .faq-answer__inner {
                padding: 0 18px 18px;
                font-size: 0.88rem;
            }
        }
    </style>

    <section class="support-page">
        <div class="support-hero">
            <div>
                <span class="support-hero__kicker">{{ __('FAQ') }}</span>
                <h1 class="support-hero__title">{{ $heroTitle }}</h1>
                <p class="support-hero__copy">{{ $heroCopy }}</p>
            </div>

            <div class="support-hero__mini">
                @foreach ($miniCards as $card)
                    <div class="support-mini-card">
                        <strong>{{ $card['title'] }}</strong>
                        <p>{{ $card['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="faq-shell" data-faq-shell>
            @foreach ($faqItems as $index => $item)
                <article class="faq-item {{ $index === 0 ? 'is-open' : '' }}" data-faq-item>
                    <button type="button" class="faq-toggle" data-faq-toggle aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">
                        <span class="faq-toggle__question">{{ $item['question'] }}</span>
                        <span class="faq-toggle__icon" aria-hidden="true">
                            <i class="fa-solid fa-chevron-down"></i>
                        </span>
                    </button>

                    <div class="faq-answer-wrap">
                        <div class="faq-answer">
                            <div class="faq-answer__inner">
                                <p>{{ $item['answer'] }}</p>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endsection

@push('web_scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const faqShell = document.querySelector('[data-faq-shell]');

            if (!faqShell) {
                return;
            }

            faqShell.querySelectorAll('[data-faq-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const item = button.closest('[data-faq-item]');

                    if (!item) {
                        return;
                    }

                    const isOpen = item.classList.contains('is-open');

                    faqShell.querySelectorAll('[data-faq-item]').forEach((faqItem) => {
                        faqItem.classList.remove('is-open');
                        const toggle = faqItem.querySelector('[data-faq-toggle]');

                        if (toggle) {
                            toggle.setAttribute('aria-expanded', 'false');
                        }
                    });

                    if (!isOpen) {
                        item.classList.add('is-open');
                        button.setAttribute('aria-expanded', 'true');
                    }
                });
            });
        });
    </script>
@endpush
