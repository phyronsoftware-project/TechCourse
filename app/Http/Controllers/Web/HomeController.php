<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\TechCategory;
use App\Models\TechDetail;
use App\Services\GoogleAnalyticsRealtimeService;
use App\Services\TechnologyPdfService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class HomeController extends Controller
{
    public function __construct(
        protected GoogleAnalyticsRealtimeService $googleAnalyticsRealtimeService,
        protected TechnologyPdfService $technologyPdfService,
    )
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
        return view('web.pages.home.service', [
            'techCategories' => $this->technologyCategories(),
        ]);
    }

    public function technologyCategoryShow(string $categorySlug): View
    {
        $techCategory = $this->technologyCategories()
            ->first(fn ($category) => Str::slug((string) $category->name) === $categorySlug);

        abort_unless($techCategory, 404);

        // Load detail rows only when the tech_details table exists.
        if (Schema::hasTable('tech_details')) {
            $techCategory->load([
                'technologies' => fn ($query) => $query
                    ->where('status', 'active')
                    ->orderBy('sort_order')
                    ->orderBy('name'),
            ]);
        } else {
            $techCategory->setRelation('technologies', collect());
        }

        return view('web.pages.home.technology-category-detail', [
            'category' => $techCategory,
        ]);
    }

    public function technologyCategoryDownload(string $categorySlug)
    {
        $techCategory = $this->technologyCategories()
            ->first(fn ($category) => Str::slug((string) $category->name) === $categorySlug);

        abort_unless($techCategory, 404);

        if (Schema::hasTable('tech_details')) {
            $techCategory->load([
                'technologies' => fn ($query) => $query
                    ->where('status', 'active')
                    ->orderBy('sort_order')
                    ->orderBy('name'),
            ]);
        } else {
            $techCategory->setRelation('technologies', collect());
        }

        return $this->technologyPdfService->downloadCategoryPdf($techCategory);
    }

    public function technologyShow(TechDetail $technology): View
    {
        return view('web.pages.home.technology-detail', [
            'technology' => $technology->load('category'),
            'relatedTechnologies' => $this->relatedTechnologyItems($technology),
        ]);
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

    protected function technologyCategories()
    {
        try {
            // Load active technology categories and include details only when that table exists.
            if (!Schema::hasTable('tech_categories')) {
                return collect();
            }

            $query = TechCategory::query()
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name');

            if (Schema::hasTable('tech_details')) {
                $query->with([
                    'technologies' => fn ($builder) => $builder
                        ->where('status', 'active')
                        ->orderBy('sort_order')
                        ->orderBy('name'),
                ]);
            }

            return $query->get();
        } catch (Throwable) {
            return collect();
        }
    }

    protected function relatedTechnologyItems(TechDetail $technology)
    {
        try {
            // Show small related cards from the same category on detail page.
            if (!Schema::hasTable('tech_details')) {
                return collect();
            }

            return TechDetail::query()
                ->with('category')
                ->where('status', 'active')
                ->where('category_id', $technology->category_id)
                ->whereKeyNot($technology->id)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(6)
                ->get();
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
