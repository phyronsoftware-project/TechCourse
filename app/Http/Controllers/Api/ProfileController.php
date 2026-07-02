<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\CourseFavorite;
use App\Models\CourseSave;
use App\Models\LessonComment;
use App\Models\Order;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function __construct(protected NotificationService $notificationService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->successResponse('Profile fetched successfully.', [
            'user' => $this->userPayload($user),
            'supports' => [
                'phone' => Schema::hasColumn('users', 'phone'),
                'avatar' => Schema::hasColumn('users', 'avatar'),
                'address' => Schema::hasColumn('users', 'address'),
                'city' => Schema::hasColumn('users', 'city'),
                'province' => Schema::hasColumn('users', 'province'),
                'postal_code' => Schema::hasColumn('users', 'postal_code'),
                'notification_mute' => Schema::hasColumn('users', 'notification_muted'),
                'app_language' => Schema::hasColumn('users', 'app_language'),
                'app_sound_enabled' => Schema::hasColumn('users', 'app_sound_enabled'),
                'app_vibrate_enabled' => Schema::hasColumn('users', 'app_vibrate_enabled'),
            ],
            'settings' => $this->settingsPayload($user),
            'counts' => [
                'my_courses' => Schema::hasTable('course_enrollments')
                    ? CourseEnrollment::query()->where('user_id', $user->id)->where('status', 'active')->count()
                    : 0,
                'liked_courses' => Schema::hasTable('course_favorites')
                    ? CourseFavorite::query()->where('user_id', $user->id)->count()
                    : 0,
                'saved_courses' => Schema::hasTable('course_saves')
                    ? CourseSave::query()->where('user_id', $user->id)->count()
                    : 0,
                'lesson_comments' => Schema::hasTable('lesson_comments')
                    ? LessonComment::query()->where('user_id', $user->id)->count()
                    : 0,
                'course_orders' => Schema::hasTable('orders')
                    ? Order::query()->where('user_id', $user->id)->count()
                    : 0,
                'shop_orders' => Schema::hasTable('shop_orders')
                    ? DB::table('shop_orders')->where('user_id', $user->id)->count()
                    : 0,
                'notifications_unread' => $this->notificationService->getHeaderNotifications($user, 'app')['unreadCount'] ?? 0,
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ];

        if (Schema::hasColumn('users', 'phone')) {
            $rules['phone'] = ['nullable', 'string', 'max:50'];
        }

        if (Schema::hasColumn('users', 'address')) {
            $rules['address'] = ['nullable', 'string', 'max:500'];
        }

        if (Schema::hasColumn('users', 'city')) {
            $rules['city'] = ['nullable', 'string', 'max:120'];
        }

        if (Schema::hasColumn('users', 'province')) {
            $rules['province'] = ['nullable', 'string', 'max:120'];
        }

        if (Schema::hasColumn('users', 'postal_code')) {
            $rules['postal_code'] = ['nullable', 'string', 'max:40'];
        }

        if (Schema::hasColumn('users', 'avatar')) {
            $rules['avatar'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];
        }

        $data = $request->validate($rules);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        foreach (['phone', 'address', 'city', 'province', 'postal_code'] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        if (Schema::hasColumn('users', 'avatar') && $request->hasFile('avatar')) {
            if (
                $user->avatar
                && ! str_starts_with($user->avatar, 'http://')
                && ! str_starts_with($user->avatar, 'https://')
                && ! str_starts_with($user->avatar, 'storage/')
            ) {
                Storage::disk('public')->delete($user->avatar);
            }

            $payload['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($payload);

        return $this->successResponse('Profile updated successfully.', [
            'user' => $this->userPayload($user->fresh()),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update([
            'password' => $data['password'],
        ]);

        return $this->successResponse('Password updated successfully.');
    }

    public function myCourses(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $items = Schema::hasTable('course_enrollments')
            ? CourseEnrollment::query()
                ->with(['course.category'])
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->latest('id')
                ->get()
                ->map(fn (CourseEnrollment $enrollment) => [
                    'id' => $enrollment->id,
                    'access_type' => $enrollment->access_type,
                    'status' => $enrollment->status,
                    'started_at' => optional($enrollment->started_at)?->toIso8601String(),
                    'completed_at' => optional($enrollment->completed_at)?->toIso8601String(),
                    'course' => $enrollment->course ? $this->coursePayload($enrollment->course) : null,
                ])
                ->values()
            : collect();

        return $this->successResponse('My courses fetched successfully.', [
            'items' => $items,
        ]);
    }

    public function likedCourses(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $items = Schema::hasTable('course_favorites')
            ? CourseFavorite::query()
                ->with('course.category')
                ->where('user_id', $user->id)
                ->latest('id')
                ->get()
                ->map(fn (CourseFavorite $favorite) => [
                    'id' => $favorite->id,
                    'created_at' => optional($favorite->created_at)?->toIso8601String(),
                    'course' => $favorite->course ? $this->coursePayload($favorite->course) : null,
                ])
                ->values()
            : collect();

        return $this->successResponse('Liked courses fetched successfully.', [
            'items' => $items,
        ]);
    }

    public function savedCourses(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $items = Schema::hasTable('course_saves')
            ? CourseSave::query()
                ->with('course.category')
                ->where('user_id', $user->id)
                ->latest('id')
                ->get()
                ->map(fn (CourseSave $save) => [
                    'id' => $save->id,
                    'created_at' => optional($save->created_at)?->toIso8601String(),
                    'course' => $save->course ? $this->coursePayload($save->course) : null,
                ])
                ->values()
            : collect();

        return $this->successResponse('Saved courses fetched successfully.', [
            'items' => $items,
        ]);
    }

    public function lessonComments(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $items = Schema::hasTable('lesson_comments')
            ? LessonComment::query()
                ->with(['course.category', 'lesson'])
                ->where('user_id', $user->id)
                ->latest('id')
                ->get()
                ->map(fn (LessonComment $comment) => [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'status' => $comment->status,
                    'created_at' => optional($comment->created_at)?->toIso8601String(),
                    'course' => $comment->course ? $this->coursePayload($comment->course) : null,
                    'lesson' => $comment->lesson ? [
                        'id' => $comment->lesson->id,
                        'title' => $comment->lesson->title,
                        'slug' => $comment->lesson->slug,
                    ] : null,
                ])
                ->values()
            : collect();

        return $this->successResponse('Lesson comments fetched successfully.', [
            'items' => $items,
        ]);
    }

    public function courseOrders(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $items = Order::query()
            ->with([
                'items.course.category',
                'payments' => fn ($query) => $query->latest('id'),
            ])
            ->where('user_id', $user->id)
            ->latest('id')
            ->get()
            ->map(function (Order $order) {
                $latestPayment = $order->payments->first();

                return [
                    'id' => $order->id,
                    'order_no' => $order->order_no,
                    'total_amount' => (float) $order->total_amount,
                    'currency' => $order->currency,
                    'status' => $order->status,
                    'payment_method' => $order->payment_method,
                    'paid_at' => optional($order->paid_at)?->toIso8601String(),
                    'created_at' => optional($order->created_at)?->toIso8601String(),
                    'latest_payment' => $latestPayment ? [
                        'id' => $latestPayment->id,
                        'payment_provider' => $latestPayment->payment_provider,
                        'payment_option' => $latestPayment->payment_option,
                        'status' => $latestPayment->status,
                        'amount' => (float) $latestPayment->amount,
                        'currency' => $latestPayment->currency,
                        'transaction_id' => $latestPayment->transaction_id,
                        'paid_at' => optional($latestPayment->paid_at)?->toIso8601String(),
                    ] : null,
                    'items' => $order->items->map(fn ($item) => [
                        'id' => $item->id,
                        'course_id' => $item->course_id,
                        'course_title' => $item->course_title,
                        'price' => (float) $item->price,
                        'course' => $item->course ? $this->coursePayload($item->course) : null,
                    ])->values(),
                ];
            })
            ->values();

        return $this->successResponse('Course orders fetched successfully.', [
            'items' => $items,
        ]);
    }

    public function shopOrders(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->successResponse('Shop orders fetched successfully.', [
            'items' => $this->shopOrdersForUser($user->id),
        ]);
    }

    public function notifications(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $data = $this->notificationService->getHeaderNotifications($user, 'app', 30);

        return $this->successResponse('Notifications fetched successfully.', [
            'settings' => $this->settingsPayload($user),
            'unread_count' => $data['unreadCount'],
            'items' => $data['items']->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'link_url' => $notification->link_url,
                    'style' => $notification->style,
                    'trigger_event' => $notification->trigger_event,
                    'is_read' => (bool) ($notification->is_read ?? false),
                    'created_at' => optional($notification->created_at)?->toIso8601String(),
                ];
            })->values(),
        ]);
    }

    public function markNotificationsRead(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $count = $this->notificationService->markAllVisibleAsRead($user, 'app');

        return $this->successResponse('Notifications marked as read.', [
            'marked_count' => $count,
        ]);
    }

    public function notificationSettings(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->successResponse('Notification settings fetched successfully.', [
            'notification_muted' => (bool) ($user->notification_muted ?? false),
        ]);
    }

    public function updateNotificationSettings(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'notification_muted' => ['required', 'boolean'],
        ]);

        $user->forceFill([
            'notification_muted' => (bool) $data['notification_muted'],
        ])->save();

        return $this->successResponse('Notification settings updated successfully.', [
            'notification_muted' => (bool) $user->notification_muted,
        ]);
    }

    public function preferenceSettings(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->successResponse('App preferences fetched successfully.', $this->settingsPayload($user));
    }

    public function updatePreferenceSettings(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'app_language' => ['sometimes', 'string', 'in:km,en'],
            'app_sound_enabled' => ['sometimes', 'boolean'],
            'app_vibrate_enabled' => ['sometimes', 'boolean'],
            'notification_muted' => ['sometimes', 'boolean'],
        ]);

        $payload = [];

        foreach (['app_language', 'app_sound_enabled', 'app_vibrate_enabled', 'notification_muted'] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        if ($payload !== []) {
            $user->forceFill($payload)->save();
        }

        return $this->successResponse('App preferences updated successfully.', $this->settingsPayload($user->fresh()));
    }

    protected function shopOrdersForUser(int $userId)
    {
        if (! Schema::hasTable('shop_orders') || ! Schema::hasTable('shop_order_items')) {
            return collect();
        }

        $rows = DB::table('shop_orders')
            ->leftJoin('shop_order_items', 'shop_orders.id', '=', 'shop_order_items.shop_order_id')
            ->leftJoin('shop_products', 'shop_order_items.product_id', '=', 'shop_products.id')
            ->where('shop_orders.user_id', $userId)
            ->orderByDesc('shop_orders.id')
            ->orderBy('shop_order_items.id')
            ->select([
                'shop_orders.id',
                'shop_orders.order_no',
                'shop_orders.total_amount',
                'shop_orders.currency',
                'shop_orders.status',
                'shop_orders.payment_method',
                'shop_orders.created_at',
                'shop_order_items.id as item_id',
                'shop_order_items.product_id',
                'shop_order_items.qty',
                'shop_order_items.unit_price',
                'shop_order_items.line_total',
                'shop_products.name as product_name',
                'shop_products.slug as product_slug',
                'shop_products.sku as product_sku',
                'shop_products.image as product_image',
            ])
            ->get();

        $payments = collect();

        if ($rows->isNotEmpty() && Schema::hasTable('shop_payments')) {
            $payments = DB::table('shop_payments')
                ->whereIn('shop_order_id', $rows->pluck('id')->filter()->unique())
                ->orderByDesc('id')
                ->get()
                ->groupBy('shop_order_id');
        }

        return $rows
            ->groupBy('id')
            ->map(function ($groupedRows) use ($payments) {
                $first = $groupedRows->first();
                $latestPayment = $payments->get($first->id)?->first();

                return [
                    'id' => $first->id,
                    'order_no' => $first->order_no,
                    'total_amount' => (float) $first->total_amount,
                    'currency' => $first->currency ?: 'USD',
                    'status' => $latestPayment->status ?? $first->status ?? 'pending',
                    'payment_method' => $latestPayment->payment_provider ?? $first->payment_method,
                    'created_at' => $first->created_at,
                    'items' => $groupedRows
                        ->filter(fn ($row) => ! is_null($row->item_id))
                        ->map(function ($row) {
                            return [
                                'id' => $row->item_id,
                                'product_id' => $row->product_id,
                                'name' => $row->product_name ?: 'Product',
                                'slug' => $row->product_slug,
                                'sku' => $row->product_sku,
                                'qty' => (int) ($row->qty ?? 0),
                                'unit_price' => (float) ($row->unit_price ?? 0),
                                'line_total' => (float) ($row->line_total ?? 0),
                                'image_url' => $this->normalizeImagePath($row->product_image),
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();
    }

    protected function coursePayload($course): array
    {
        return [
            'id' => $course->id,
            'title' => $course->title,
            'slug' => $course->slug,
            'short_description' => $course->short_description,
            'thumbnail_url' => $course->thumbnail_url,
            'price' => (float) $course->price,
            'currency' => $course->currency,
            'is_free' => (bool) $course->is_free,
            'level' => $course->level,
            'duration_text' => $course->duration_text,
            'category' => $course->category ? [
                'id' => $course->category->id,
                'name' => $course->category->name,
                'slug' => $course->category->slug,
                'image_url' => $course->category->image_url,
            ] : null,
        ];
    }

    protected function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'city' => $user->city,
            'province' => $user->province,
            'postal_code' => $user->postal_code,
            'avatar' => $user->avatar,
            'avatar_url' => $user->avatar_url,
            'role' => $user->role ?? 'user',
            'status' => $user->status ?? 'active',
            'notification_muted' => (bool) ($user->notification_muted ?? false),
            'email_verified_at' => optional($user->email_verified_at)?->toIso8601String(),
        ];
    }

    protected function settingsPayload(User $user): array
    {
        return [
            'notification_muted' => (bool) ($user->notification_muted ?? false),
            'app_language' => (string) ($user->app_language ?: 'km'),
            'app_sound_enabled' => array_key_exists('app_sound_enabled', $user->getAttributes())
                ? (bool) $user->app_sound_enabled
                : true,
            'app_vibrate_enabled' => array_key_exists('app_vibrate_enabled', $user->getAttributes())
                ? (bool) $user->app_vibrate_enabled
                : true,
        ];
    }

    protected function normalizeImagePath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'storage/')) {
            return asset(ltrim($path, '/'));
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    protected function successResponse(string $message, array $data = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => (object) $data,
        ], $status);
    }
}
