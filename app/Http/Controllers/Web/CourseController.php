<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseFavorite;
use App\Models\CourseReview;
use App\Models\CourseSave;
use App\Models\CourseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Course::query()
            ->with(['category', 'creator'])
            ->withCount(['lessons', 'resources']);

        if (Schema::hasColumn('courses', 'is_published')) {
            $query->where('is_published', true);
        }

        if (Schema::hasColumn('courses', 'status')) {
            $query->where('status', 'published');
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($builder) use ($request) {
                $builder->where('slug', $request->string('category'))
                    ->orWhere('name', $request->string('category'));
            });
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        match ($request->string('sort')->toString()) {
            'title' => $query->orderBy('title'),
            'price_low' => $query->orderBy('price'),
            'price_high' => $query->orderByDesc('price'),
            default => $query->latest('id'),
        };

        $courses = $query->paginate(8)->withQueryString();
        $courses->onEachSide(1);

        return view('web.pages.courses.index', [
            'courses' => $courses,
            'categories' => $this->categories(),
        ]);
    }

    public function show(string $course): View
    {
        $courseModel = $this->findCourse($course);
        $courseModel->load([
            'category',
            'creator',
            'resources' => fn ($query) => $query
                ->with('lesson')
                ->orderBy('sort_order')
                ->orderBy('id'),
            'lessons' => fn ($query) => $query
                ->when(Schema::hasColumn('course_lessons', 'is_published'), fn ($lessonQuery) => $lessonQuery->where('is_published', true))
                ->orderBy('sort_order')
                ->orderBy('id'),
        ]);

        $hasCourseAccess = $this->userHasCourseAccess($courseModel->id);
        $activeLesson = $this->resolvePreviewLesson($courseModel, $hasCourseAccess);

        return view('web.pages.courses.show', [
            'course' => $courseModel,
            'activeLesson' => $activeLesson,
            'hasCourseAccess' => $hasCourseAccess,
            'courseNeedsPayment' => $this->courseNeedsPayment($courseModel),
            'relatedCourses' => $this->relatedCourses($courseModel),
            'courseComments' => $this->courseComments($courseModel->id),
            'isLiked' => $this->isLiked($courseModel->id),
            'isSaved' => $this->isSaved($courseModel->id),
        ]);
    }

    protected function categories()
    {
        try {
            if (!Schema::hasTable('course_categories')) {
                return collect();
            }

            return CourseCategory::query()->orderBy('name')->get();
        } catch (Throwable) {
            return collect();
        }
    }

    protected function relatedCourses(Course $course)
    {
        try {
            $query = Course::query()
                ->with('category')
                ->whereKeyNot($course->id);

            if ($course->category_id) {
                $query->where('category_id', $course->category_id);
            }

            if (Schema::hasColumn('courses', 'is_published')) {
                $query->where('is_published', true);
            }

            if (Schema::hasColumn('courses', 'status')) {
                $query->where('status', 'published');
            }

            return $query->latest('id')->limit(4)->get();
        } catch (Throwable) {
            return collect();
        }
    }

    protected function findCourse(string $course): Course
    {
        $courseModel = Course::query()
            ->where('slug', $course)
            ->orWhere('id', $course)
            ->first();

        if (!$courseModel) {
            throw new NotFoundHttpException();
        }

        return $courseModel;
    }

    protected function resolvePreviewLesson(Course $course, bool $hasCourseAccess)
    {
        if ($course->lessons->isEmpty()) {
            return null;
        }

        if (! $this->courseNeedsPayment($course) || $hasCourseAccess) {
            return $course->lessons->first();
        }

        return $course->lessons->firstWhere('is_preview', true) ?: $course->lessons->first();
    }

    protected function userHasCourseAccess(int $courseId): bool
    {
        if (! Auth::check()) {
            return false;
        }

        return CourseEnrollment::query()
            ->where('user_id', Auth::id())
            ->where('course_id', $courseId)
            ->where('status', 'active')
            ->exists();
    }

    protected function courseNeedsPayment(Course $course): bool
    {
        return ! $course->is_free && (float) ($course->price ?? 0) > 0;
    }

    protected function courseComments(int $courseId)
    {
        if (! Schema::hasTable('course_reviews')) {
            return collect();
        }

        return CourseReview::query()
            ->with('user')
            ->where('course_id', $courseId)
            ->where('status', 'approved')
            ->latest('id')
            ->get();
    }

    protected function isLiked(int $courseId): bool
    {
        if (! Auth::check() || ! Schema::hasTable('course_favorites')) {
            return false;
        }

        return CourseFavorite::query()
            ->where('user_id', Auth::id())
            ->where('course_id', $courseId)
            ->exists();
    }

    protected function isSaved(int $courseId): bool
    {
        if (! Auth::check() || ! Schema::hasTable('course_saves')) {
            return false;
        }

        return CourseSave::query()
            ->where('user_id', Auth::id())
            ->where('course_id', $courseId)
            ->exists();
    }
}
