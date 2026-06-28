<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CourseFavorite;
use App\Models\CourseSave;
use App\Models\LessonComment;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = Auth::user();

        $orders = Order::query()
            ->with([
                'items.course.category',
                'payments' => fn ($query) => $query->latest('id'),
            ])
            ->where('user_id', $user->id)
            ->latest('id')
            ->get();

        $supportsPhone = Schema::hasColumn('users', 'phone');
        $supportsAvatar = Schema::hasColumn('users', 'avatar');
        $supportsAddress = Schema::hasColumn('users', 'address');
        $supportsCity = Schema::hasColumn('users', 'city');
        $supportsProvince = Schema::hasColumn('users', 'province');
        $supportsPostalCode = Schema::hasColumn('users', 'postal_code');

        $likedCourses = Schema::hasTable('course_favorites')
            ? CourseFavorite::query()
                ->with('course.category')
                ->where('user_id', $user->id)
                ->latest('id')
                ->get()
            : collect();

        $savedCourses = Schema::hasTable('course_saves')
            ? CourseSave::query()
                ->with('course.category')
                ->where('user_id', $user->id)
                ->latest('id')
                ->get()
            : collect();

        $lessonComments = Schema::hasTable('lesson_comments')
            ? LessonComment::query()
                ->with(['course', 'lesson'])
                ->where('user_id', $user->id)
                ->latest('id')
                ->get()
            : collect();

        $shopOrders = collect();

        if (Schema::hasTable('shop_orders') && Schema::hasTable('shop_order_items')) {
            $shopOrderRows = DB::table('shop_orders')
                ->leftJoin('shop_order_items', 'shop_orders.id', '=', 'shop_order_items.shop_order_id')
                ->leftJoin('shop_products', 'shop_order_items.product_id', '=', 'shop_products.id')
                ->where('shop_orders.user_id', $user->id)
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

            $shopPayments = collect();

            if ($shopOrderRows->isNotEmpty() && Schema::hasTable('shop_payments')) {
                $shopPayments = DB::table('shop_payments')
                    ->whereIn('shop_order_id', $shopOrderRows->pluck('id')->filter()->unique())
                    ->orderByDesc('id')
                    ->get()
                    ->groupBy('shop_order_id');
            }

            $shopOrders = $shopOrderRows
                ->groupBy('id')
                ->map(function ($rows) use ($shopPayments) {
                    $firstRow = $rows->first();
                    $latestPayment = $shopPayments->get($firstRow->id)?->first();

                    return (object) [
                        'id' => $firstRow->id,
                        'order_no' => $firstRow->order_no,
                        'total_amount' => (float) $firstRow->total_amount,
                        'currency' => $firstRow->currency ?: 'USD',
                        'status' => $latestPayment->status ?? $firstRow->status ?? 'pending',
                        'payment_method' => $latestPayment->payment_provider ?? $firstRow->payment_method,
                        'created_at' => $firstRow->created_at,
                        'items' => $rows
                            ->filter(fn ($row) => ! is_null($row->item_id))
                            ->map(function ($row) {
                                return (object) [
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

        return view('web.pages.profile.show', [
            'user' => $user,
            'orders' => $orders,
            'shopOrders' => $shopOrders,
            'supportsPhone' => $supportsPhone,
            'supportsAvatar' => $supportsAvatar,
            'supportsAddress' => $supportsAddress,
            'supportsCity' => $supportsCity,
            'supportsProvince' => $supportsProvince,
            'supportsPostalCode' => $supportsPostalCode,
            'likedCourses' => $likedCourses,
            'savedCourses' => $savedCourses,
            'lessonComments' => $lessonComments,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $supportsPhone = Schema::hasColumn('users', 'phone');
        $supportsAddress = Schema::hasColumn('users', 'address');
        $supportsCity = Schema::hasColumn('users', 'city');
        $supportsProvince = Schema::hasColumn('users', 'province');
        $supportsPostalCode = Schema::hasColumn('users', 'postal_code');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ];

        if ($supportsPhone) {
            $rules['phone'] = ['nullable', 'string', 'max:50'];
        }

        if ($supportsAddress) {
            $rules['address'] = ['nullable', 'string', 'max:500'];
        }

        if ($supportsCity) {
            $rules['city'] = ['nullable', 'string', 'max:120'];
        }

        if ($supportsProvince) {
            $rules['province'] = ['nullable', 'string', 'max:120'];
        }

        if ($supportsPostalCode) {
            $rules['postal_code'] = ['nullable', 'string', 'max:40'];
        }

        if (Schema::hasColumn('users', 'avatar')) {
            $rules['avatar'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];
        }

        $data = $request->validateWithBag('profile', $rules);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if ($supportsPhone) {
            $payload['phone'] = $data['phone'] ?? null;
        }

        if ($supportsAddress) {
            $payload['address'] = $data['address'] ?? null;
        }

        if ($supportsCity) {
            $payload['city'] = $data['city'] ?? null;
        }

        if ($supportsProvince) {
            $payload['province'] = $data['province'] ?? null;
        }

        if ($supportsPostalCode) {
            $payload['postal_code'] = $data['postal_code'] ?? null;
        }

        if (Schema::hasColumn('users', 'avatar') && $request->hasFile('avatar')) {
            if ($user->avatar && ! str_starts_with($user->avatar, 'http://') && ! str_starts_with($user->avatar, 'https://') && ! str_starts_with($user->avatar, 'storage/')) {
                Storage::disk('public')->delete($user->avatar);
            }

            $payload['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($payload);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Your profile has been updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('passwordUpdate', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update([
            'password' => $data['password'],
        ]);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Your password has been updated successfully.');
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
}
