<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Services\GoogleAnalyticsRealtimeService;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class HomeController extends Controller
{
    public function __construct(protected GoogleAnalyticsRealtimeService $googleAnalyticsRealtimeService)
    {
    }

    public function index(): View
    {
        $ga4ActiveUsers = $this->googleAnalyticsRealtimeService->activeUsers();
        $ga4HomeViews = $this->googleAnalyticsRealtimeService->pageViewsByPath(
            parse_url(route('home'), PHP_URL_PATH) ?: '/'
        );
        $guestViewsSource = $ga4HomeViews !== null ? __('GA4 Report') : __('Laravel DB');
        $guestViewsLabel = $ga4HomeViews !== null ? __('Website Views') : __('Guest Website Views');
        $guestViewsDescription = $ga4HomeViews !== null
            ? __('Total homepage views from Google Analytics. Active users in the last 30 minutes: :count', [
                'count' => number_format((int) ($ga4ActiveUsers ?? 0)),
            ])
            : __('Guest visitor total from DB tracking table if that table is available.');

        return view('web.pages.home.welcome', [
            'featuredCourses' => $this->featuredCourses(),
            'categories' => $this->categories(),
            // Prefer GA4 report totals for homepage views and fall back to DB when setup is incomplete.
            'trackingStats' => [
                'login_users' => $this->safeCount('users'),
                'guest_views' => $ga4HomeViews ?? $this->safeCount('website_guest_views'),
                'guest_live_views' => (int) ($ga4ActiveUsers ?? 0),
                'guest_views_label' => $guestViewsLabel,
                'guest_views_description' => $guestViewsDescription,
                'guest_views_source' => $guestViewsSource,
                'courses' => $this->safeCount('courses'),
                'products' => $this->safeCount('shop_products'),
            ],
        ]);
    }

    public function about(): View
    {
        return view('web.pages.home.about', [
            'stats' => [
                ['label' => 'Courses', 'value' => $this->safeCount('courses')],
                ['label' => 'Categories', 'value' => $this->safeCount('course_categories')],
                ['label' => 'Lessons', 'value' => $this->safeCount('course_lessons')],
            ],
            'featuredCourses' => $this->featuredCourses()->take(3),
        ]);
    }

    public function service(): View
    {
        return view('web.pages.home.service');
    }

    public function faq(): View
    {
        return view('web.pages.home.faq');
    }

    public function privacy(): View
    {
        return view('web.pages.home.privacy');
    }

    public function terms(): View
    {
        return view('web.pages.home.terms');
    }

    protected function featuredCourses()
    {
        try {
            if (!Schema::hasTable('courses')) {
                return collect();
            }

            $query = Course::query()->with('category');

            if (Schema::hasColumn('courses', 'is_published')) {
                $query->where('is_published', true);
            }

            if (Schema::hasColumn('courses', 'status')) {
                $query->where('status', 'published');
            }

            return $query->latest('id')->limit(6)->get();
        } catch (Throwable) {
            return collect();
        }
    }

    protected function categories()
    {
        try {
            if (!Schema::hasTable('course_categories')) {
                return collect();
            }

            return CourseCategory::query()->orderBy('name')->limit(8)->get();
        } catch (Throwable) {
            return collect();
        }
    }

    protected function safeCount(string $table): int
    {
        try {
            return Schema::hasTable($table) ? \DB::table($table)->count() : 0;
        } catch (Throwable) {
            return 0;
        }
    }
}
