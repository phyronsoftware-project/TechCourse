<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->latest('id');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('admin.pages.users.index', [
            'pageTitle' => 'Users',
            'users' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search', 'role', 'status']),
        ]);
    }

    public function show(User $user): View
    {
        return view('admin.pages.users.show', [
            'pageTitle' => 'User Details',
            'user' => $user,
            'recordId' => $user->id,
        ]);
    }

    public function edit(User $user): View
    {
        return view('admin.pages.users.edit', [
            'pageTitle' => 'Edit User',
            'user' => $user,
            'recordId' => $user->id,
        ]);
    }

    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own admin account.');
        }

        try {
            DB::transaction(function () use ($user) {
                $orderIds = DB::table('orders')->where('user_id', $user->id)->pluck('id');

                DB::table('courses')->where('created_by', $user->id)->update(['created_by' => null]);
                DB::table('course_favorites')->where('user_id', $user->id)->delete();
                DB::table('lesson_progress')->where('user_id', $user->id)->delete();
                DB::table('course_reviews')->where('user_id', $user->id)->delete();
                DB::table('course_enrollments')->where('user_id', $user->id)->delete();
                DB::table('payments')->where('user_id', $user->id)->delete();

                if ($orderIds->isNotEmpty()) {
                    DB::table('payments')->whereIn('order_id', $orderIds)->delete();
                    DB::table('course_enrollments')->whereIn('order_id', $orderIds)->delete();
                    DB::table('order_items')->whereIn('order_id', $orderIds)->delete();
                    DB::table('orders')->whereIn('id', $orderIds)->delete();
                }

                $user->delete();
            });
        } catch (Throwable) {
            return back()->with('error', 'Unable to delete this user right now.');
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
