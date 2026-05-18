@php
    $socialMedia = [
        ['url' => 'https://www.facebook.com/phyronU', 'icon' => 'fa-brands fa-facebook-f'],
        ['url' => 'https://www.youtube.com/@Programmers-SE/playlists', 'icon' => 'fa-brands fa-youtube'],
        ['url' => 'https://t.me/phyron100203', 'icon' => 'fa-brands fa-telegram'],
        ['url' => 'https://www.tiktok.com/@phyronu', 'icon' => 'fa-brands fa-tiktok'],
        ['url' => 'https://twitter.com/AllexPhyro63098', 'icon' => 'fa-brands fa-x-twitter'],
    ];

    $quickLinks = [
        ['label' => __('Home'), 'url' => route('home')],
        ['label' => __('Course'), 'url' => route('courses.index')],
        ['label' => __('About Us'), 'url' => route('about')],
        ['label' => __('Contact'), 'url' => route('contact')],
    ];
@endphp

<footer>
    <div class="footer-content">
        <div class="fo">
            <h3>{{ __('About') }}</h3>
            <p>{{ __('TechCourse is a modern learning space for sharing knowledge, projects, and practical tech content for students.') }}</p>
        </div>

        <div class="fo1">
            <h3>{{ __('Quick Link') }}</h3>
            <ul>
                @foreach ($quickLinks as $link)
                    <li>
                        <a href="{{ $link['url'] }}">
                            <span>{{ $link['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="fo1">
            <h3>{{ __('Support') }}</h3>
            <ul>
                <li><a href="#">{{ __('FAQ') }}</a></li>
                <li><a href="#">{{ __('Privacy Policy') }}</a></li>
                <li><a href="#">{{ __('Terms & Conditions') }}</a></li>
            </ul>
        </div>

        <div class="fo1">
            <h3>{{ __('Contact Us') }}</h3>

            <div class="contact-info-footer">
                <a href="mailto:phyronu@gmail.com">
                    <i class="fa-solid fa-envelope"></i>
                    <span>phyronu@gmail.com</span>
                </a>
                <a href="tel:+855975786200">
                    <i class="fa-solid fa-phone"></i>
                    <span>+855 975 786 200 (Telegram)</span>
                </a>
                <a href="https://maps.google.com/?q=Phnom Penh, Cambodia" target="_blank" rel="noopener noreferrer">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>{{ __('Phnom Penh, Cambodia') }}</span>
                </a>
            </div>

            <ul class="sol">
                @foreach ($socialMedia as $social)
                    <li>
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer">
                            <i class="{{ $social['icon'] }}"></i>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <p class="copyright-text">
        &copy; {{ now()->year }} {{ __('All rights reserved by') }} Tech<span style="color: #027bff; font-weight: 700;">Course</span>.
    </p>
</footer>
