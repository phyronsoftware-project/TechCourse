<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class AuthController extends Controller
{
    public function create(): View|RedirectResponse
    {
        // Check only the independent administrator session.
        $guard = Auth::guard('admin');

        if ($guard->check() && $this->isAdmin($guard->user()?->role)) {
            return redirect()->route('admin.dashboard');
        }

        return view('web.pages.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Sign in without replacing the public website account.
        $guard = Auth::guard('admin');

        if (!$guard->attempt($credentials)) {
            Log::channel('security')->warning('Admin login failed due to invalid credentials.', [
                'email' => $credentials['email'],
                'ip' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ]);

            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();

        $user = $guard->user();

        if (!$user || !$this->isAdmin($user->role)) {
            Log::channel('security')->warning('Admin dashboard access blocked for non-admin account.', [
                'user_id' => $user?->id,
                'email' => $user?->email,
                'role' => $user?->role,
                'ip' => $request->ip(),
            ]);

            // Clear only the rejected admin identity and preserve the public session.
            $guard->logout();
            $request->session()->regenerate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('error', 'This account is not allowed to access the admin dashboard.');
        }

        Log::channel('security')->info('Admin login successful.', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'ip' => $request->ip(),
        ]);

        return redirect()->intended(route('admin.dashboard'));
    }

    // Start Google OAuth with the private admin callback URL.
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')
            ->redirectUrl($this->googleAdminRedirectUri())
            ->scopes(['email'])
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    // Allow Google sign-in only for an existing active administrator.
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl($this->googleAdminRedirectUri())
                ->user();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('login')
                ->with('error', 'Google admin login failed. Please try again.');
        }

        $email = trim((string) ($googleUser->getEmail() ?? ''));
        $user = $email !== '' ? User::query()->firstWhere('email', $email) : null;

        if (! $user || ! $this->isAdmin($user->role)) {
            Log::channel('security')->warning('Admin Google login blocked for an unauthorized account.', [
                'email' => $email,
                'ip' => $request->ip(),
            ]);

            return redirect()
                ->route('login')
                ->with('error', 'This Google account is not allowed to access the admin dashboard.');
        }

        if (($user->status ?? 'active') !== 'active') {
            return redirect()
                ->route('login')
                ->with('error', 'This admin account is not active.');
        }

        // Store the approved Google account only in the administrator guard.
        Auth::guard('admin')->login($user, true);
        $request->session()->regenerate();

        Log::channel('security')->info('Admin Google login successful.', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'ip' => $request->ip(),
        ]);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        // Log the administrator out without ending the public website session.
        $guard = Auth::guard('admin');
        $user = $guard->user();

        Log::channel('security')->info('Admin logout successful.', [
            'user_id' => $user?->id,
            'email' => $user?->email,
            'ip' => $request->ip(),
        ]);

        $guard->logout();
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function isAdmin(?string $role): bool
    {
        return in_array($role, ['admin', 'super_admin'], true);
    }

    // Resolve the admin callback from environment configuration or the named route.
    protected function googleAdminRedirectUri(): string
    {
        return (string) (config('services.google.admin_redirect') ?: route('admin.google.callback'));
    }
}
