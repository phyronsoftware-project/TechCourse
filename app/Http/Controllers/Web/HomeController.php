<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('web.pages.home.welcome', [
            'featuredCourses' => $this->featuredCourses(),
            'categories' => $this->categories(),
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
        ]);
    }

    public function contact(): View
    {
        return view('web.pages.home.contact');
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
