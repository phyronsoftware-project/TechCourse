<?php

namespace App\Providers;

use App\Models\CourseCategory;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Schema;
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
