@php
    $socialMediaFallback = [
        ['name' => 'Facebook', 'url' => 'https://www.facebook.com/your-page', 'icon' => 'fa-brands fa-facebook-f'],
        ['name' => 'YouTube', 'url' => 'https://www.youtube.com/@your-channel', 'icon' => 'fa-brands fa-youtube'],
        ['name' => 'Telegram', 'url' => 'https://t.me/your-channel', 'icon' => 'fa-brands fa-telegram'],
        ['name' => 'TikTok', 'url' => 'https://www.tiktok.com/@your-account', 'icon' => 'fa-brands fa-tiktok'],
        ['name' => 'X / Twitter', 'url' => 'https://x.com/your-account', 'icon' => 'fa-brands fa-x-twitter'],
    ];
@endphp

<footer>
    <div class="footer-content">
        <div class="fo">
            <h3>{{ __('About') }}</h3>
            <p>{{ __('TechCourse is a modern learning space for sharing knowledge, projects, and practical tech content for students.') }}</p>
        </div>

        <div class="fo1 footer-download-panel">
            <h3>{{ __('Download TechCourse App') }}</h3>
            <div class="footer-store-links footer-store-links--panel">
                <a href="#" aria-label="{{ __('Download on the App Store') }}">
                    <img src="{{ asset('ABA_Images/appstore.png') }}" alt="{{ __('Download on the App Store') }}">
                </a>
                <a href="#" aria-label="{{ __('Get it on Google Play') }}">
                    <img src="{{ asset('ABA_Images/Playstore.png') }}" alt="{{ __('Get it on Google Play') }}">
                </a>
            </div>
        </div>

        <div class="fo1">
            <h3>{{ __('Support') }}</h3>
            <ul>
                <li><a href="{{ route('faq') }}">{{ __('FAQ') }}</a></li>
                <li><a href="{{ route('privacy') }}">{{ __('Privacy Policy') }}</a></li>
                <li><a href="{{ route('terms') }}">{{ __('Terms & Conditions') }}</a></li>
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

            <ul class="sol" data-footer-social-list data-api-url="{{ route('api.social-media.index') }}">
                @foreach ($socialMediaFallback as $social)
                    <li>
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $social['name'] }}">
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

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const socialList = document.querySelector('[data-footer-social-list]');

        if (!socialList) {
            return;
        }

        const apiUrl = socialList.getAttribute('data-api-url');

        if (!apiUrl) {
            return;
        }

        try {
            const response = await fetch(`${apiUrl}?platform=web`, {
                headers: {
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                return;
            }

            const result = await response.json();
            const links = Array.isArray(result?.data) ? result.data : [];

            if (!links.length) {
                return;
            }

            socialList.innerHTML = '';

            links.forEach((item) => {
                const li = document.createElement('li');
                const anchor = document.createElement('a');
                const icon = document.createElement('i');

                anchor.href = item.url ?? '#';
                anchor.target = '_blank';
                anchor.rel = 'noopener noreferrer';
                anchor.setAttribute('aria-label', item.name ?? 'Social Media');

                icon.className = item.icon ?? 'fa-solid fa-globe';

                anchor.appendChild(icon);
                li.appendChild(anchor);
                socialList.appendChild(li);
            });
        } catch (error) {
            console.warn('Footer social media API load failed.', error);
        }
    });
</script>
