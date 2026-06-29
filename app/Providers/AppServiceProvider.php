<?php

namespace App\Providers;

use App\Models\CourseCategory;
use App\Services\NotificationService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $appUrlHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $isLocalHost = in_array($appUrlHost, ['localhost', '127.0.0.1'], true);

        if (app()->environment('production') && ! $isLocalHost) {
            URL::forceScheme('https');
        }

        RateLimiter::for('auth', function (Request $request): array {
            $email = mb_strtolower((string) $request->input('email', 'guest'));

            return [
                Limit::perMinute(5)->by($email.'|'.$request->ip()),
                Limit::perMinute(20)->by($request->ip()),
            ];
        });

        RateLimiter::for('otp', function (Request $request): array {
            $pendingUserId = (string) ($request->session()->get('auth_email_otp.user_id') ?? 'guest');

            return [
                Limit::perMinute(6)->by($pendingUserId.'|'.$request->ip()),
            ];
        });

        RateLimiter::for('shop-actions', function (Request $request): array {
            $identity = (string) ($request->user()?->id ?: $request->ip());

            return [
                Limit::perMinute(50)->by($identity),
            ];
        });

        RateLimiter::for('api-public', function (Request $request): array {
            return [
                Limit::perMinute(80)->by($request->ip()),
            ];
        });

        View::composer('web.components.header', function ($view): void {
            $categories = collect();
            $notifications = collect();
            $notificationUnreadCount = 0;

            try {
                if (Schema::hasTable('course_categories')) {
                    $categories = CourseCategory::query()
                        ->select(['id', 'name', 'slug'])
                        ->orderBy('name')
                        ->limit(6)
                        ->get();
                }

                if (auth()->check()) {
                    $notificationPayload = app(NotificationService::class)->getHeaderNotifications(auth()->user(), 'web');
                    $notifications = $notificationPayload['items'];
                    $notificationUnreadCount = $notificationPayload['unreadCount'];
                }
            } catch (Throwable) {
                Log::channel('security')->warning('Header composer failed to load runtime data.', [
                    'user_id' => auth()->id(),
                    'ip' => request()->ip(),
                ]);
                $categories = collect();
                $notifications = collect();
                $notificationUnreadCount = 0;
            }

            $view->with('headerCategories', $categories);
            $view->with('headerNotifications', $notifications);
            $view->with('headerNotificationUnreadCount', $notificationUnreadCount);
        });
    }
}
