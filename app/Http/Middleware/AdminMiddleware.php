<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = $request->user();

        // Keep both administrator roles inside the protected admin area.
        if (!$user || !in_array($user->role, ['admin', 'super_admin'], true)) {
            abort(403, 'You do not have permission to access the admin panel.');
        }

        return $next($request);
    }
}
