<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseFavorite;
use App\Models\CourseLesson;
use App\Models\CourseSave;
use App\Models\LessonComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LearningController extends Controller
{
    public function show(string $course, string $lesson): View|RedirectResponse
    {
        $courseModel = Course::query()
            ->with([
                'category',
                'resources' => fn ($query) => $query
                    ->with('lesson')
                    ->orderBy('sort_order')
                    ->orderBy('id'),
                'lessons' => fn ($query) => $query
                    ->when(Schema::hasColumn('course_lessons', 'is_published'), fn ($lessonQuery) => $lessonQuery->where('is_published', true))
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->where('slug', $course)
            ->orWhere('id', $course)
            ->first();

        if (! $courseModel) {
            throw new NotFoundHttpException();
        }

        $lessonModel = $courseModel->lessons
            ->first(fn (CourseLesson $item) => (string) $item->id === $lesson || $item->slug === $lesson);

        if (! $lessonModel) {
            throw new NotFoundHttpException();
        }

        if ($this->lessonNeedsPayment($courseModel, $lessonModel) && ! $this->userHasCourseAccess($courseModel->id)) {
            if (! Auth::check()) {
                return redirect()
                    ->route('web.login', ['redirect' => route('courses.checkout', $courseModel->slug ?: $courseModel->id)])
                    ->with('warning', __('Please login first to continue to payment for this lesson.'));
            }

            return redirect()
                ->route('courses.checkout', $courseModel->slug ?: $courseModel->id)
                ->with('warning', __('This lesson is locked. Please pay for this course to continue.'));
        }

        return view('web.pages.learning.show', [
            'course' => $courseModel,
            'activeLesson' => $lessonModel,
            'hasCourseAccess' => $this->userHasCourseAccess($courseModel->id),
            'courseNeedsPayment' => $this->courseNeedsPayment($courseModel),
            'lessonComments' => $this->lessonComments($lessonModel->id),
            'isLiked' => $this->isLiked($courseModel->id),
            'isSaved' => $this->isSaved($courseModel->id),
        ]);
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

    protected function lessonNeedsPayment(Course $course, CourseLesson $lesson): bool
    {
        return $this->courseNeedsPayment($course) && ! $lesson->is_preview;
    }

    protected function courseNeedsPayment(Course $course): bool
    {
        return ! $course->is_free && (float) ($course->price ?? 0) > 0;
    }

    protected function lessonComments(int $lessonId)
    {
        if (! Schema::hasTable('lesson_comments')) {
            return collect();
        }

        return LessonComment::query()
            ->with('user')
            ->where('lesson_id', $lessonId)
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
