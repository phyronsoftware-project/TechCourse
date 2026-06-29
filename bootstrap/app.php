<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')->group(__DIR__.'/../routes/admin.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request): string {
            return $request->is('admin/phyron/v1*') ? '/admin/phyron/v1/login' : '/login';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TooManyRequestsHttpException $exception, Request $request) {
            $retryAfter = (int) ($exception->getHeaders()['Retry-After'] ?? 60);
            $message = __('Too many requests. Please wait a moment and try again.');

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => $message,
                    'retry_after' => $retryAfter,
                ], 429, [
                    'Retry-After' => $retryAfter,
                ]);
            }

            if (! $request->isMethod('GET')) {
                return redirect()
                    ->back()
                    ->withInput($request->except(['password', 'password_confirmation']))
                    ->with('warning', $message);
            }

            return response()->view('errors.429', [
                'retryAfter' => $retryAfter,
            ], 429, [
                'Retry-After' => $retryAfter,
            ]);
        });
    })->create();
