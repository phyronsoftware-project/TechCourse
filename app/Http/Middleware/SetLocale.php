<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');

        if (! in_array($locale, ['en', 'km'], true)) {
            $locale = Config::get('app.locale', 'km');
        }

        App::setLocale(in_array($locale, ['en', 'km'], true) ? $locale : 'km');

        return $next($request);
    }
}
