<?php

namespace App\Http\Controllers\Web;

use App\Mail\AuthOtpMail;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

class UserAuthController extends Controller
{
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

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'The provided credentials do not match our records.'])
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

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function showVerifyCode(Request $request): View|RedirectResponse
    {
        $pending = $request->session()->get('auth_email_otp');

        if (! is_array($pending) || empty($pending['user_id']) || empty($pending['email'])) {
            return redirect()->route('web.login');
        }

        return view('web.pages.auth.verify-code', [
            'emailMask' => $this->maskEmail((string) $pending['email']),
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

            return redirect()
                ->route('web.login')
                ->with('warning', 'Your verification code has expired. Please login again.');
        }

        if (! Hash::check($data['code'], (string) $pending['code'])) {
            return back()->withErrors(['code' => 'The verification code is incorrect.']);
        }

        $user = User::query()->find($pending['user_id']);

        if (! $user) {
            $request->session()->forget('auth_email_otp');

            return redirect()->route('web.login');
        }

        if (! $user->email_verified_at) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }

        Auth::login($user, (bool) ($pending['remember'] ?? false));
        $request->session()->regenerate();

        $redirectTo = $this->validatedRedirect((string) ($pending['redirect_to'] ?? ''));
        $request->session()->forget('auth_email_otp');

        if ($redirectTo) {
            return redirect()
                ->to($redirectTo)
                ->with('success', 'Verification completed successfully.');
        }

        return $this->redirectByRole()->with('success', 'Verification completed successfully.');
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
