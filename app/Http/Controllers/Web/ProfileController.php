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

        return view('web.pages.profile.show', [
            'user' => $user,
            'orders' => $orders,
            'supportsPhone' => $supportsPhone,
            'supportsAvatar' => $supportsAvatar,
            'likedCourses' => $likedCourses,
            'savedCourses' => $savedCourses,
            'lessonComments' => $lessonComments,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $supportsPhone = Schema::hasColumn('users', 'phone');

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
}
