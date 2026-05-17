<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseFavorite;
use App\Models\CourseLesson;
use App\Models\CourseReview;
use App\Models\CourseSave;
use App\Models\LessonComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EngagementController extends Controller
{
    public function toggleLike(Request $request, string $course): RedirectResponse
    {
        $courseModel = $this->findCourse($course);

        if (! Schema::hasTable('course_favorites')) {
            return back()->with('warning', __('Course likes table is not ready yet.'));
        }

        $favorite = CourseFavorite::query()
            ->where('user_id', $request->user()->id)
            ->where('course_id', $courseModel->id)
            ->first();

        if ($favorite) {
            $favorite->delete();

            return back()->with('success', __('Removed from liked courses.'));
        }

        CourseFavorite::query()->create([
            'user_id' => $request->user()->id,
            'course_id' => $courseModel->id,
        ]);

        return back()->with('success', __('Added to liked courses.'));
    }

    public function toggleSave(Request $request, string $course): RedirectResponse
    {
        $courseModel = $this->findCourse($course);

        if (! Schema::hasTable('course_saves')) {
            return back()->with('warning', __('Course saves table is not ready yet.'));
        }

        $saved = CourseSave::query()
            ->where('user_id', $request->user()->id)
            ->where('course_id', $courseModel->id)
            ->first();

        if ($saved) {
            $saved->delete();

            return back()->with('success', __('Removed from saved courses.'));
        }

        CourseSave::query()->create([
            'user_id' => $request->user()->id,
            'course_id' => $courseModel->id,
        ]);

        return back()->with('success', __('Saved course successfully.'));
    }

    public function storeCourseComment(Request $request, string $course): RedirectResponse
    {
        $courseModel = $this->findCourse($course);

        if (! Schema::hasTable('course_reviews')) {
            return back()->with('warning', __('Course comments table is not ready yet.'));
        }

        $data = $request->validate([
            'comment' => ['required', 'string', 'max:2000'],
        ]);

        CourseReview::query()->create([
            'user_id' => $request->user()->id,
            'course_id' => $courseModel->id,
            'rating' => 5,
            'comment' => $data['comment'],
            'status' => 'approved',
        ]);

        return back()->with('success', __('Your comment has been posted successfully.'));
    }

    public function updateCourseComment(Request $request, string $course, CourseReview $comment): RedirectResponse
    {
        $courseModel = $this->findCourse($course);

        abort_unless($comment->course_id === $courseModel->id, 404);
        abort_unless($comment->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'comment' => ['required', 'string', 'max:2000'],
        ]);

        $comment->update([
            'comment' => $data['comment'],
        ]);

        return back()->with('success', __('Your comment has been updated successfully.'));
    }

    public function destroyCourseComment(Request $request, string $course, CourseReview $comment): RedirectResponse
    {
        $courseModel = $this->findCourse($course);

        abort_unless($comment->course_id === $courseModel->id, 404);
        abort_unless($comment->user_id === $request->user()->id, 403);

        $comment->delete();

        return back()->with('success', __('Your comment has been deleted successfully.'));
    }

    public function storeLessonComment(Request $request, string $course, string $lesson): RedirectResponse
    {
        $courseModel = $this->findCourse($course);
        $lessonModel = $this->findLesson($courseModel, $lesson);

        if (! Schema::hasTable('lesson_comments')) {
            return back()->with('warning', __('Lesson comments table is not ready yet.'));
        }

        $data = $request->validate([
            'comment' => ['required', 'string', 'max:2000'],
        ]);

        LessonComment::query()->create([
            'user_id' => $request->user()->id,
            'course_id' => $courseModel->id,
            'lesson_id' => $lessonModel->id,
            'comment' => $data['comment'],
            'status' => 'approved',
        ]);

        return back()->with('success', __('Your comment has been posted successfully.'));
    }

    public function updateLessonComment(Request $request, string $course, string $lesson, LessonComment $comment): RedirectResponse
    {
        $courseModel = $this->findCourse($course);
        $lessonModel = $this->findLesson($courseModel, $lesson);

        abort_unless($comment->course_id === $courseModel->id && $comment->lesson_id === $lessonModel->id, 404);
        abort_unless($comment->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'comment' => ['required', 'string', 'max:2000'],
        ]);

        $comment->update([
            'comment' => $data['comment'],
        ]);

        return back()->with('success', __('Your comment has been updated successfully.'));
    }

    public function destroyLessonComment(Request $request, string $course, string $lesson, LessonComment $comment): RedirectResponse
    {
        $courseModel = $this->findCourse($course);
        $lessonModel = $this->findLesson($courseModel, $lesson);

        abort_unless($comment->course_id === $courseModel->id && $comment->lesson_id === $lessonModel->id, 404);
        abort_unless($comment->user_id === $request->user()->id, 403);

        $comment->delete();

        return back()->with('success', __('Your comment has been deleted successfully.'));
    }

    protected function findCourse(string $course): Course
    {
        $courseModel = Course::query()
            ->where('slug', $course)
            ->orWhere('id', $course)
            ->first();

        if (! $courseModel) {
            throw new NotFoundHttpException();
        }

        return $courseModel;
    }

    protected function findLesson(Course $course, string $lesson): CourseLesson
    {
        $lessonModel = CourseLesson::query()
            ->where('course_id', $course->id)
            ->where(function ($query) use ($lesson) {
                $query->where('slug', $lesson)->orWhere('id', $lesson);
            })
            ->first();

        if (! $lessonModel) {
            throw new NotFoundHttpException();
        }

        return $lessonModel;
    }
}
