<?php

namespace App\Http\Controllers\Web;

use App\Mail\AuthOtpMail;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class UserAuthController extends Controller
{
    public function __construct(protected NotificationService $notificationService)
    {
    }

    public function createLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }

        return view('web.pages.auth.user-login', [
            'activeTab' => 'login',
            'redirectTo' => request('redirect'),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($recaptchaRedirect = $this->validateRecaptcha($request)) {
            return $recaptchaRedirect;
        }

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            Log::channel('security')->warning('Login failed due to invalid credentials.', [
                'email' => $credentials['email'],
                'ip' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ]);

            return back()
                ->withErrors(['email' => 'The provided credentials do not match our records.'])
                ->onlyInput('email');
        }

        if (($user->status ?? 'active') !== 'active') {
            Log::channel('security')->warning('Login attempt blocked for inactive account.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            return back()
                ->withErrors(['email' => 'This account is not active.'])
                ->onlyInput('email');
        }

        try {
            $this->startEmailVerification(
                request: $request,
                user: $user,
                mode: 'login',
                remember: $request->boolean('remember'),
                redirectTo: $this->validatedRedirect($request->string('redirect')->toString()),
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withErrors(['email' => 'Unable to send verification code right now. Please check mail configuration and try again.'])
                ->onlyInput('email');
        }

        return redirect()
            ->route('web.verify.code.notice')
            ->with('success', 'We sent a verification code to your email.');
    }

    public function createRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }

        return view('web.pages.auth.user-login', [
            'activeTab' => 'register',
            'redirectTo' => request('redirect'),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if ($recaptchaRedirect = $this->validateRecaptcha($request, true)) {
            return $recaptchaRedirect;
        }

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ];

        if (Schema::hasColumn('users', 'phone')) {
            $payload['phone'] = $data['phone'] ?? null;
        }

        if (Schema::hasColumn('users', 'role')) {
            $payload['role'] = 'user';
        }

        if (Schema::hasColumn('users', 'status')) {
            $payload['status'] = 'active';
        }

        $user = User::create($payload);

        try {
            $this->startEmailVerification(
                request: $request,
                user: $user,
                mode: 'register',
                remember: false,
                redirectTo: $this->validatedRedirect($request->string('redirect')->toString()),
            );
        } catch (Throwable $exception) {
            report($exception);
            $user->delete();

            return back()
                ->withErrors(['email' => 'Unable to send verification code right now. Please check mail configuration and try again.'])
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        return redirect()
            ->route('web.verify.code.notice')
            ->with('success', 'Your account has been created successfully. Please enter the verification code from your email.');
    }

    public function redirectToGoogle(Request $request): RedirectResponse
    {
        return $this->redirectToSocialProvider('google');
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        return $this->handleSocialProviderCallback('google');
    }

    public function redirectToFacebook(Request $request): RedirectResponse
    {
        return $this->redirectToSocialProvider('facebook');
    }

    public function handleFacebookCallback(Request $request): RedirectResponse
    {
        return $this->handleSocialProviderCallback('facebook');
    }

    public function handleTelegramCallback(Request $request): RedirectResponse
    {
        $redirectTo = $this->validatedRedirect($request->string('redirect')->toString());
        $botToken = trim((string) config('services.telegram.bot_token'));

        if ($botToken === '') {
            return redirect()
                ->route('web.login', array_filter(['redirect' => $redirectTo]))
                ->with('warning', 'Telegram login is not configured yet. Please add your Telegram bot settings first.');
        }

        if (! Schema::hasColumn('users', 'telegram_id')) {
            return redirect()
                ->route('web.login', array_filter(['redirect' => $redirectTo]))
                ->with('warning', 'Telegram login database columns are missing. Please run the Telegram SQL or migration first.');
        }

        $telegramUser = $this->validatedTelegramUser($request, $botToken);

        if ($telegramUser === null) {
            return redirect()
                ->route('web.login', array_filter(['redirect' => $redirectTo]))
                ->with('warning', 'Telegram login failed. Please try again.');
        }

        $user = User::query()
            ->where('telegram_id', $telegramUser['id'])
            ->orWhere('email', $this->telegramPlaceholderEmail($telegramUser['id']))
            ->first();

        if ($user && in_array($user->role, ['admin', 'super_admin'], true)) {
            return redirect()
                ->route('login')
                ->with('warning', 'This Telegram account belongs to an admin account. Please continue from the admin login form.');
        }

        if ($user && ($user->status ?? 'active') !== 'active') {
            return redirect()
                ->route('web.login', array_filter(['redirect' => $redirectTo]))
                ->with('warning', 'This account is not active.');
        }

        $registeredNow = false;
        $payload = [
            'name' => $telegramUser['name'],
            'email' => $this->telegramPlaceholderEmail($telegramUser['id']),
            'password' => Hash::make(Str::random(40)),
            'email_verified_at' => now(),
        ];

        if (Schema::hasColumn('users', 'telegram_id')) {
            $payload['telegram_id'] = $telegramUser['id'];
        }

        if (Schema::hasColumn('users', 'telegram_username')) {
            $payload['telegram_username'] = $telegramUser['username'];
        }

        if (Schema::hasColumn('users', 'telegram_photo_url')) {
            $payload['telegram_photo_url'] = $telegramUser['photo_url'];
        }

        if (Schema::hasColumn('users', 'avatar')) {
            $payload['avatar'] = $telegramUser['photo_url'];
        }

        if (! $user) {
            $registeredNow = true;

            if (Schema::hasColumn('users', 'role')) {
                $payload['role'] = 'user';
            }

            if (Schema::hasColumn('users', 'status')) {
                $payload['status'] = 'active';
            }

            $user = User::create($payload);
        } else {
            unset($payload['email'], $payload['password']);

            if (! $user->email_verified_at) {
                $payload['email_verified_at'] = now();
            }

            $user->forceFill(array_filter($payload, static fn ($value) => filled($value) || $value === null))->save();
        }

        Auth::login($user, true);
        $request->session()->regenerate();
        $this->notificationService->flashPopupNotification($user, $registeredNow ? 'register' : 'login');

        if ($redirectTo) {
            return redirect()->to($redirectTo);
        }

        return $this->redirectByRole();
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function showForgotPassword(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }

        return view('web.pages.auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();

        if (! $user) {
            return back()
                ->withErrors(['email' => 'We could not find a user with that email address.'])
                ->onlyInput('email');
        }

        if (($user->status ?? 'active') !== 'active') {
            return back()
                ->withErrors(['email' => 'This account is not active.'])
                ->onlyInput('email');
        }

        try {
            $this->startEmailVerification(
                request: $request,
                user: $user,
                mode: 'password_reset',
                remember: false,
                redirectTo: null,
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withErrors(['email' => 'Unable to send verification code right now. Please check mail configuration and try again.'])
                ->onlyInput('email');
        }

        return redirect()
            ->route('web.verify.code.notice')
            ->with('success', 'We sent a verification code to your email.');
    }

    public function showResetPassword(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }

        $pending = $request->session()->get('password_reset_verified');

        if (! is_array($pending) || empty($pending['user_id']) || empty($pending['email'])) {
            return redirect()->route('password.request');
        }

        return view('web.pages.auth.reset-password', [
            'email' => (string) $pending['email'],
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('password_reset_verified');

        if (! is_array($pending) || empty($pending['user_id']) || empty($pending['email'])) {
            return redirect()->route('password.request');
        }

        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (now()->timestamp > (int) ($pending['expires_at'] ?? 0)) {
            $request->session()->forget('password_reset_verified');

            return back()
                ->withErrors(['email' => 'Your password reset session has expired. Please try again.'])
                ->withInput($request->except('password', 'password_confirmation'));
        }

        if ((string) $pending['email'] !== $data['email']) {
            return back()
                ->withErrors(['email' => 'The email does not match the verified reset request.'])
                ->withInput($request->except('password', 'password_confirmation'));
        }

        $user = User::query()->find($pending['user_id']);

        if (! $user) {
            $request->session()->forget('password_reset_verified');

            return redirect()->route('password.request');
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'remember_token' => Str::random(60),
        ])->save();

        Auth::login($user, true);
        $request->session()->regenerate();
        $request->session()->forget('password_reset_verified');
        $request->session()->forget('auth_email_otp');

        return redirect()
            ->route('home')
            ->with('success', __('Your password has been reset successfully.'));
    }

    public function showVerifyCode(Request $request): View|RedirectResponse
    {
        $pending = $request->session()->get('auth_email_otp');

        if (! is_array($pending) || empty($pending['user_id']) || empty($pending['email'])) {
            return redirect()->route('web.login');
        }

        return view('web.pages.auth.verify-code', [
            'emailMask' => $this->maskEmail((string) $pending['email']),
            'mode' => (string) ($pending['mode'] ?? 'login'),
        ]);
    }

    public function verifyCode(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('auth_email_otp');

        if (! is_array($pending) || empty($pending['user_id']) || empty($pending['code'])) {
            return redirect()->route('web.login');
        }

        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        if (now()->timestamp > (int) ($pending['expires_at'] ?? 0)) {
            $request->session()->forget('auth_email_otp');

            $redirectRoute = ($pending['mode'] ?? 'login') === 'password_reset'
                ? 'password.request'
                : 'web.login';

            return redirect()
                ->route($redirectRoute)
                ->with('warning', ($pending['mode'] ?? 'login') === 'password_reset'
                    ? 'Your verification code has expired. Please try forgot password again.'
                    : 'Your verification code has expired. Please login again.');
        }

        if (! Hash::check($data['code'], (string) $pending['code'])) {
            return back()->withErrors(['code' => 'The verification code is incorrect.']);
        }

        $user = User::query()->find($pending['user_id']);

        if (! $user) {
            $request->session()->forget('auth_email_otp');

            return redirect()->route('web.login');
        }

        if (($pending['mode'] ?? 'login') !== 'password_reset' && ! $user->email_verified_at) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }

        $mode = (string) ($pending['mode'] ?? 'login');

        if ($mode === 'password_reset') {
            $request->session()->put('password_reset_verified', [
                'user_id' => $user->id,
                'email' => $user->email,
                'expires_at' => now()->addMinutes(15)->timestamp,
            ]);
            $request->session()->forget('auth_email_otp');

            return redirect()
                ->route('password.reset')
                ->with('success', 'Verification completed successfully. Please set your new password.');
        }

        Auth::login($user, (bool) ($pending['remember'] ?? false));
        $request->session()->regenerate();
        $this->notificationService->flashPopupNotification($user, $mode === 'register' ? 'register' : 'login');

        $redirectTo = $this->validatedRedirect((string) ($pending['redirect_to'] ?? ''));
        $request->session()->forget('auth_email_otp');

        if ($redirectTo) {
            return redirect()->to($redirectTo);
        }

        return $this->redirectByRole();
    }

    public function resendCode(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('auth_email_otp');

        if (! is_array($pending) || empty($pending['user_id'])) {
            return redirect()->route('web.login');
        }

        $user = User::query()->find($pending['user_id']);

        if (! $user) {
            $request->session()->forget('auth_email_otp');

            return redirect()->route('web.login');
        }

        try {
            $this->startEmailVerification(
                request: $request,
                user: $user,
                mode: (string) ($pending['mode'] ?? 'login'),
                remember: (bool) ($pending['remember'] ?? false),
                redirectTo: $this->validatedRedirect((string) ($pending['redirect_to'] ?? '')),
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['code' => 'Unable to resend verification code right now. Please try again.']);
        }

        return back()->with('success', 'A new verification code has been sent to your email.');
    }

    protected function redirectByRole(): RedirectResponse
    {
        $role = Auth::user()?->role;

        if (in_array($role, ['admin', 'super_admin'], true)) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('home');
    }

    protected function validateRecaptcha(Request $request, bool $excludePasswordFields = false): ?RedirectResponse
    {
        if (! $this->recaptchaEnabled()) {
            return null;
        }

        $request->validate([
            'g-recaptcha-response' => ['required', 'string'],
        ], [
            'g-recaptcha-response.required' => __('Please complete reCAPTCHA verification first.'),
        ]);

        try {
            $response = Http::asForm()->timeout(10)->post(
                (string) config('services.recaptcha.verify_url'),
                [
                    'secret' => (string) config('services.recaptcha.secret_key'),
                    'response' => (string) $request->input('g-recaptcha-response'),
                    'remoteip' => (string) $request->ip(),
                ],
            );

            $payload = $response->json();
            $verified = is_array($payload) && (bool) ($payload['success'] ?? false);

            if ($verified) {
                return null;
            }
        } catch (Throwable $exception) {
            report($exception);
        }

        Log::channel('security')->warning('reCAPTCHA verification failed.', [
            'ip' => $request->ip(),
            'email' => (string) $request->input('email', ''),
            'route' => optional($request->route())->getName(),
        ]);

        $except = $excludePasswordFields
            ? ['password', 'password_confirmation', 'g-recaptcha-response']
            : ['password', 'g-recaptcha-response'];

        return back()
            ->withErrors(['captcha' => __('reCAPTCHA verification failed. Please try again.')])
            ->withInput($request->except($except));
    }

    protected function recaptchaEnabled(): bool
    {
        $siteKey = trim((string) config('services.recaptcha.site_key'));
        $secretKey = trim((string) config('services.recaptcha.secret_key'));

        if ($siteKey === '' || $secretKey === '') {
            return false;
        }

        return $siteKey !== 'your_site_key' && $secretKey !== 'your_secret_key';
    }

    protected function redirectToSocialProvider(string $provider): RedirectResponse
    {
        $driver = Socialite::driver($provider);

        if (in_array($provider, ['google', 'facebook'], true)) {
            $driver->scopes(['email']);
        }

        if ($provider === 'google') {
            $driver->with([
                'prompt' => 'select_account',
            ]);
        }

        return $driver->redirect();
    }

    protected function handleSocialProviderCallback(string $provider): RedirectResponse
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('web.login')
                ->with('warning', ucfirst($provider) . ' login failed. Please try again.');
        }

        $email = (string) ($socialUser->getEmail() ?? '');

        if (! filled($email)) {
            return redirect()
                ->route('web.login')
                ->with('warning', ucfirst($provider) . ' account email is not available.');
        }

        $user = User::query()->firstWhere('email', $email);

        if ($user && in_array($user->role, ['admin', 'super_admin'], true)) {
            return redirect()
                ->route('login')
                ->with('warning', 'This ' . ucfirst($provider) . ' email belongs to an admin account. Please continue from the admin login form.');
        }

        if ($user && ($user->status ?? 'active') !== 'active') {
            return redirect()
                ->route('web.login')
                ->with('warning', 'This account is not active.');
        }

        if (! $user) {
            $registeredNow = true;
            $payload = [
                'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: Str::before($email, '@'),
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
            ];

            if (Schema::hasColumn('users', 'avatar')) {
                $payload['avatar'] = $socialUser->getAvatar();
            }

            if (Schema::hasColumn('users', 'role')) {
                $payload['role'] = 'user';
            }

            if (Schema::hasColumn('users', 'status')) {
                $payload['status'] = 'active';
            }

            $user = User::create($payload);
        } else {
            $registeredNow = false;
            $updates = [];

            if (! $user->email_verified_at) {
                $updates['email_verified_at'] = now();
            }

            if (Schema::hasColumn('users', 'avatar') && filled($socialUser->getAvatar())) {
                $updates['avatar'] = $socialUser->getAvatar();
            }

            if ($updates !== []) {
                $user->forceFill($updates)->save();
            }
        }

        Auth::login($user, true);
        request()->session()->regenerate();
        $this->notificationService->flashPopupNotification($user, $registeredNow ? 'register' : 'login');

        return redirect()->route('home');
    }

    protected function validatedTelegramUser(Request $request, string $botToken): ?array
    {
        $authData = $request->only([
            'id',
            'first_name',
            'last_name',
            'username',
            'photo_url',
            'auth_date',
            'hash',
        ]);

        if (
            ! filled($authData['id'] ?? null)
            || ! filled($authData['auth_date'] ?? null)
            || ! filled($authData['hash'] ?? null)
        ) {
            return null;
        }

        $receivedHash = (string) $authData['hash'];
        unset($authData['hash']);
        ksort($authData);

        $checkString = collect($authData)
            ->filter(static fn ($value) => filled($value))
            ->map(static fn ($value, $key) => $key . '=' . $value)
            ->implode("\n");

        $secretKey = hash('sha256', $botToken, true);
        $calculatedHash = hash_hmac('sha256', $checkString, $secretKey);

        if (! hash_equals($calculatedHash, $receivedHash)) {
            return null;
        }

        if ((int) $authData['auth_date'] < now()->subMinutes(10)->timestamp) {
            return null;
        }

        $fullName = trim(implode(' ', array_filter([
            $authData['first_name'] ?? null,
            $authData['last_name'] ?? null,
        ])));

        return [
            'id' => (int) $authData['id'],
            'name' => $fullName !== '' ? $fullName : ((string) ($authData['username'] ?? 'Telegram User')),
            'username' => filled($authData['username'] ?? null) ? (string) $authData['username'] : null,
            'photo_url' => filled($authData['photo_url'] ?? null) ? (string) $authData['photo_url'] : null,
        ];
    }

    protected function telegramPlaceholderEmail(int $telegramId): string
    {
        return 'telegram_' . $telegramId . '@telegram.local';
    }

    protected function validatedRedirect(?string $redirectTo): ?string
    {
        if (! filled($redirectTo)) {
            return null;
        }

        if (str_starts_with($redirectTo, '/')) {
            return $redirectTo;
        }

        $appUrl = rtrim((string) config('app.url'), '/');

        if ($appUrl !== '' && str_starts_with($redirectTo, $appUrl)) {
            return substr($redirectTo, strlen($appUrl)) ?: '/';
        }

        return null;
    }

    protected function startEmailVerification(Request $request, User $user, string $mode, bool $remember, ?string $redirectTo): void
    {
        $code = (string) random_int(100000, 999999);

        $request->session()->put('auth_email_otp', [
            'user_id' => $user->id,
            'email' => $user->email,
            'code' => Hash::make($code),
            'mode' => $mode,
            'remember' => $remember,
            'redirect_to' => $redirectTo,
            'expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        Mail::to($user->email)->send(new AuthOtpMail(
            name: (string) $user->name,
            code: $code,
            mode: $mode,
        ));
    }

    protected function maskEmail(string $email): string
    {
        if (! str_contains($email, '@')) {
            return $email;
        }

        [$name, $domain] = explode('@', $email, 2);
        $visible = mb_substr($name, 0, 2);
        $hidden = str_repeat('*', max(mb_strlen($name) - 2, 2));

        return $visible . $hidden . '@' . $domain;
    }
}
