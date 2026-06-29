<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check() && $this->isAdmin(Auth::user()?->role)) {
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

        if (!Auth::attempt($credentials)) {
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

        $user = $request->user();

        if (!$user || !$this->isAdmin($user->role)) {
            Log::channel('security')->warning('Admin dashboard access blocked for non-admin account.', [
                'user_id' => $user?->id,
                'email' => $user?->email,
                'role' => $user?->role,
                'ip' => $request->ip(),
            ]);

            Auth::logout();
            $request->session()->invalidate();
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

    public function destroy(Request $request): RedirectResponse
    {
        Log::channel('security')->info('Admin logout successful.', [
            'user_id' => $request->user()?->id,
            'email' => $request->user()?->email,
            'ip' => $request->ip(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function isAdmin(?string $role): bool
    {
        return in_array($role, ['admin', 'super_admin'], true);
    }
}
