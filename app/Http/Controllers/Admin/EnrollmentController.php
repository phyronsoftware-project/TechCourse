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

    public function create(): View
    {
        return view('admin.pages.enrollments.create', [
            'pageTitle' => 'Create Enrollment',
            'users' => User::query()->orderBy('name')->get(),
            'courses' => Course::query()->orderBy('title')->get(),
            'orders' => Order::query()->orderByDesc('id')->get(),
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

        CourseEnrollment::create($data);

        return redirect()
            ->route('admin.enrollments.index')
            ->with('success', 'Enrollment created successfully.');
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
