<?php

namespace App\Providers;

use App\Models\CourseCategory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
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
        View::composer('web.components.header', function ($view): void {
            $categories = collect();

            try {
                if (Schema::hasTable('course_categories')) {
                    $categories = CourseCategory::query()
                        ->select(['id', 'name', 'slug'])
                        ->orderBy('name')
                        ->limit(6)
                        ->get();
                }
            } catch (Throwable) {
                $categories = collect();
            }

            $view->with('headerCategories', $categories);
        });
    }
}
