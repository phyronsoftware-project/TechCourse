<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseEnrollment;
use App\Models\CourseFavorite;
use App\Models\CourseReview;
use App\Models\CourseSave;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class CourseController extends Controller
{
    public function home(Request $request): JsonResponse
    {
        try {
            $user = $request->user('sanctum');
            $now = now('Asia/Phnom_Penh');

            $banners = Schema::hasTable('banners')
                ? Banner::query()
                    ->with('course')
                    ->where('platform', 'app')
                    ->where('is_active', true)
                    ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                    ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
                    ->orderBy('sort_order')
                    ->latest('id')
                    ->limit(8)
                    ->get()
                    ->map(fn (Banner $banner) => [
                        'id' => $banner->id,
                        'title' => $banner->title,
                        'subtitle' => $banner->subtitle,
                        'image_url' => $banner->image_url,
                        'button_text' => $banner->button_text,
                        'target_course_id' => $banner->target_course_id,
                    ])->values()
                : collect();

            $categories = $this->categoriesQuery()->limit(10)->get()->map(fn (CourseCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image_url' => $category->image_url,
            ])->values();

            return response()->json([
                'success' => true,
                'message' => 'Home data fetched successfully.',
                'data' => [
                    'banners' => $banners,
                    'categories' => $categories,
                    'featured_courses' => $this->courseCollection($this->baseCourseQuery()->latest('total_students')->limit(6)->get(), $user),
                    'free_courses' => $this->courseCollection((clone $this->baseCourseQuery())->where(fn ($query) => $query->where('is_free', true)->orWhere('price', '<=', 0))->latest('id')->limit(6)->get(), $user),
                    'paid_courses' => $this->courseCollection((clone $this->baseCourseQuery())->where('is_free', false)->where('price', '>', 0)->latest('id')->limit(6)->get(), $user),
                    'latest_courses' => $this->courseCollection((clone $this->baseCourseQuery())->latest('id')->limit(8)->get(), $user),
                ],
            ]);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch home data right now.',
                'data' => (object) [],
            ], 500);
        }
    }

    public function categories(): JsonResponse
    {
        try {
            $categories = $this->categoriesQuery()
                ->get()
                ->map(fn (CourseCategory $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'image_url' => $category->image_url,
                ])->values();

            return response()->json([
                'success' => true,
                'message' => 'Course categories fetched successfully.',
                'data' => [
                    'categories' => $categories,
                ],
            ]);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch course categories right now.',
                'data' => (object) [],
            ], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = $this->baseCourseQuery();
            $user = $request->user('sanctum');

            if ($request->filled('category')) {
                $category = trim($request->string('category')->toString());

                if (! $this->isAllFilterValue($category)) {
                    $query->whereHas('category', function ($builder) use ($category) {
                        $builder->where('slug', $category)
                            ->orWhere('name', $category);
                    });
                }
            }

            if ($request->filled('search')) {
                $search = $request->string('search')->toString();

                $query->where(function ($builder) use ($search) {
                    $builder->where('title', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            match ($request->string('sort')->toString()) {
                'title' => $query->orderBy('title'),
                'price_low' => $query->orderBy('price'),
                'price_high' => $query->orderByDesc('price'),
                'popular' => $query->orderByDesc('total_students'),
                default => $query->latest('id'),
            };

            $perPage = min(max((int) $request->integer('per_page', 10), 1), 30);
            $courses = $query->paginate($perPage)->withQueryString();

            return response()->json([
                'success' => true,
                'message' => 'Courses fetched successfully.',
                'data' => [
                    'items' => $this->courseCollection(collect($courses->items()), $user),
                    'pagination' => [
                        'current_page' => $courses->currentPage(),
                        'last_page' => $courses->lastPage(),
                        'per_page' => $courses->perPage(),
                        'total' => $courses->total(),
                    ],
                ],
            ]);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch courses right now.',
                'data' => (object) [],
            ], 500);
        }
    }

    public function show(Request $request, string $course): JsonResponse
    {
        try {
            $user = $request->user('sanctum');

            $courseModel = $this->baseCourseQuery()
                ->with([
                    'lessons' => fn ($query) => $query
                        ->when(Schema::hasColumn('course_lessons', 'is_published'), fn ($lessonQuery) => $lessonQuery->where('is_published', true))
                        ->orderBy('sort_order')
                        ->orderBy('id'),
                    'resources' => fn ($query) => $query
                        ->with('lesson')
                        ->orderBy('sort_order')
                        ->orderBy('id'),
                    'reviews' => fn ($query) => $query
                        ->with('user')
                        ->where('status', 'approved')
                        ->latest('id'),
                ])
                ->where(function ($query) use ($course) {
                    $query->where('slug', $course);

                    if (is_numeric($course)) {
                        $query->orWhere('id', (int) $course);
                    }
                })
                ->first();

            if (! $courseModel) {
                throw new NotFoundHttpException();
            }

            $hasAccess = $this->userHasCourseAccess($courseModel->id, $user?->id);

            return response()->json([
                'success' => true,
                'message' => 'Course detail fetched successfully.',
                'data' => [
                    'course' => $this->courseItem($courseModel, $user),
                    'lessons' => $courseModel->lessons->map(fn ($lesson) => [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'slug' => $lesson->slug,
                        'description' => $lesson->description,
                        'video_type' => $lesson->video_type,
                        'video_url' => $lesson->video_url,
                        'duration_seconds' => $lesson->duration_seconds,
                        'is_preview' => (bool) $lesson->is_preview,
                        'is_locked' => $this->courseNeedsPayment($courseModel) && ! $hasAccess && ! $lesson->is_preview,
                        'sort_order' => $lesson->sort_order,
                    ])->values(),
                    'resources' => $courseModel->resources->map(fn ($resource) => [
                        'id' => $resource->id,
                        'title' => $resource->title,
                        'lesson_id' => $resource->lesson_id,
                        'lesson_title' => $resource->lesson?->title,
                        'file_type' => $resource->file_type,
                        'file_size' => $resource->file_size,
                        'file_url' => $resource->file_url,
                        'is_free' => (bool) $resource->is_free,
                        'is_downloadable' => (bool) $resource->is_downloadable,
                    ])->values(),
                    'reviews' => $courseModel->reviews->map(fn (CourseReview $review) => [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'user' => [
                            'id' => $review->user?->id,
                            'name' => $review->user?->name,
                            'avatar_url' => $review->user?->avatar_url,
                        ],
                        'created_at' => optional($review->created_at)?->toIso8601String(),
                    ])->values(),
                    'has_access' => $hasAccess,
                ],
            ]);
        } catch (NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found.',
                'errors' => (object) [],
            ], 404);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch course detail right now.',
                'data' => (object) [],
            ], 500);
        }
    }

    public function toggleLike(Request $request, string $course): JsonResponse
    {
        try {
            $user = $request->user('sanctum');
            $courseModel = $this->resolveCourse($course);

            if (! Schema::hasTable('course_favorites')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course likes table is not ready yet.',
                    'data' => (object) [],
                ], 422);
            }

            $favorite = CourseFavorite::query()
                ->where('user_id', $user->id)
                ->where('course_id', $courseModel->id)
                ->first();

            if ($favorite) {
                $favorite->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Removed from liked courses.',
                    'data' => [
                        'is_liked' => false,
                        'course' => $this->courseItem($courseModel->fresh(['category', 'creator']), $user),
                    ],
                ]);
            }

            CourseFavorite::query()->create([
                'user_id' => $user->id,
                'course_id' => $courseModel->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Added to liked courses.',
                'data' => [
                    'is_liked' => true,
                    'course' => $this->courseItem($courseModel->fresh(['category', 'creator']), $user),
                ],
            ]);
        } catch (NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found.',
                'errors' => (object) [],
            ], 404);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to update liked courses right now.',
                'data' => (object) [],
            ], 500);
        }
    }

    public function toggleSave(Request $request, string $course): JsonResponse
    {
        try {
            $user = $request->user('sanctum');
            $courseModel = $this->resolveCourse($course);

            if (! Schema::hasTable('course_saves')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course saves table is not ready yet.',
                    'data' => (object) [],
                ], 422);
            }

            $saved = CourseSave::query()
                ->where('user_id', $user->id)
                ->where('course_id', $courseModel->id)
                ->first();

            if ($saved) {
                $saved->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Removed from saved courses.',
                    'data' => [
                        'is_saved' => false,
                        'course' => $this->courseItem($courseModel->fresh(['category', 'creator']), $user),
                    ],
                ]);
            }

            CourseSave::query()->create([
                'user_id' => $user->id,
                'course_id' => $courseModel->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Saved course successfully.',
                'data' => [
                    'is_saved' => true,
                    'course' => $this->courseItem($courseModel->fresh(['category', 'creator']), $user),
                ],
            ]);
        } catch (NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found.',
                'errors' => (object) [],
            ], 404);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to update saved courses right now.',
                'data' => (object) [],
            ], 500);
        }
    }

    protected function baseCourseQuery()
    {
        $query = Course::query()
            ->with(['category', 'creator'])
            ->withCount(['lessons', 'resources', 'reviews']);

        if (Schema::hasColumn('courses', 'is_published')) {
            $query->where('is_published', true);
        }

        if (Schema::hasColumn('courses', 'status')) {
            $query->where('status', 'published');
        }

        return $query;
    }

    protected function categoriesQuery()
    {
        $query = CourseCategory::query()->orderBy('sort_order')->orderBy('name');

        if (Schema::hasColumn('course_categories', 'status')) {
            $query->where('status', 'active');
        }

        return $query;
    }

    protected function courseCollection($courses, ?object $user)
    {
        return $courses->map(fn (Course $course) => $this->courseItem($course, $user))->values();
    }

    protected function courseItem(Course $course, ?object $user): array
    {
        $userId = $user?->id;

        return [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->slug,
            'short_description' => $course->short_description,
            'description' => $course->description,
            'thumbnail_url' => $course->thumbnail_url,
            'intro_video_url' => $course->intro_video_url,
            'level' => $course->level,
            'language' => $course->language,
            'price' => (float) $course->price,
            'currency' => $course->currency,
            'is_free' => (bool) $course->is_free,
            'duration_text' => $course->duration_text,
            'total_lessons' => $course->total_lessons ?: (int) ($course->lessons_count ?? 0),
            'total_students' => $course->total_students,
            'lessons_count' => (int) ($course->lessons_count ?? 0),
            'resources_count' => (int) ($course->resources_count ?? 0),
            'reviews_count' => (int) ($course->reviews_count ?? 0),
            'category' => $course->category ? [
                'id' => $course->category->id,
                'name' => $course->category->name,
                'slug' => $course->category->slug,
                'image_url' => $course->category->image_url,
            ] : null,
            'creator' => $course->creator ? [
                'id' => $course->creator->id,
                'name' => $course->creator->name,
            ] : null,
            'is_liked' => $this->userHasFavorite($course->id, $userId),
            'is_saved' => $this->userHasSave($course->id, $userId),
            'has_access' => $this->userHasCourseAccess($course->id, $userId),
        ];
    }

    protected function userHasCourseAccess(int $courseId, ?int $userId): bool
    {
        if (! $userId) {
            return false;
        }

        return CourseEnrollment::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('status', 'active')
            ->exists();
    }

    protected function userHasFavorite(int $courseId, ?int $userId): bool
    {
        if (! $userId || ! Schema::hasTable('course_favorites')) {
            return false;
        }

        return CourseFavorite::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->exists();
    }

    protected function userHasSave(int $courseId, ?int $userId): bool
    {
        if (! $userId || ! Schema::hasTable('course_saves')) {
            return false;
        }

        return CourseSave::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->exists();
    }

    protected function courseNeedsPayment(Course $course): bool
    {
        return ! $course->is_free && (float) ($course->price ?? 0) > 0;
    }

    protected function resolveCourse(string $course): Course
    {
        $courseModel = $this->baseCourseQuery()
            ->where(function ($query) use ($course) {
                $query->where('slug', $course);

                if (is_numeric($course)) {
                    $query->orWhere('id', (int) $course);
                }
            })
            ->first();

        if (! $courseModel) {
            throw new NotFoundHttpException();
        }

        return $courseModel;
    }

    protected function isAllFilterValue(string $value): bool
    {
        return in_array(mb_strtolower(trim($value)), ['all', 'all-courses', '*'], true);
    }
}
