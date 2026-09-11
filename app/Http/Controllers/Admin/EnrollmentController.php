<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = CourseEnrollment::query()
            ->with(['user', 'course', 'order'])
            ->latest('id');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search) {
                $builder
                    ->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('course', fn ($courseQuery) => $courseQuery->where('title', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('admin.pages.enrollments.index', [
            'pageTitle' => 'Enrollments',
            'enrollments' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(Request $request): View
    {
        // Preselect a user when access is granted from the user management list.
        $selectedUserId = $request->integer('user_id') ?: null;

        return view('admin.pages.enrollments.create', [
            'pageTitle' => 'Grant Course Access',
            'users' => User::query()->orderBy('name')->get(),
            'courses' => Course::query()->orderBy('title')->get(),
            'orders' => Order::query()->orderByDesc('id')->get(),
            'selectedUserId' => $selectedUserId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'course_id' => ['required', 'exists:courses,id'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'access_type' => ['required', 'in:free,paid,admin_grant'],
            'status' => ['required', 'in:active,expired,cancelled'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
        ]);

        $data['started_at'] = $data['started_at'] ?? now();

        // Grant new access or safely reactivate an existing user-course enrollment.
        CourseEnrollment::query()->updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'course_id' => $data['course_id'],
            ],
            [
                'order_id' => $data['order_id'] ?? null,
                'access_type' => $data['access_type'],
                'status' => $data['status'],
                'started_at' => $data['started_at'],
                'completed_at' => $data['completed_at'] ?? null,
            ],
        );

        return redirect()
            ->route('admin.enrollments.index')
            ->with('success', 'Course access granted successfully.');
    }

    public function show(CourseEnrollment $enrollment): View
    {
        $enrollment->load(['user', 'course', 'order']);

        return view('admin.pages.enrollments.show', [
            'pageTitle' => 'Enrollment Details',
            'enrollment' => $enrollment,
            'recordId' => $enrollment->id,
        ]);
    }

    public function destroy(CourseEnrollment $enrollment): RedirectResponse
    {
        $enrollment->delete();

        return redirect()
            ->route('admin.enrollments.index')
            ->with('success', 'Enrollment deleted successfully.');
    }
}
