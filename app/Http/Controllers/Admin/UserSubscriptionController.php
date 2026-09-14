<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserSubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $query = UserSubscription::query()->with(['user', 'plan', 'assignedBy'])->latest('id');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(fn ($builder) => $builder
                ->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                ->orWhereHas('plan', fn ($planQuery) => $planQuery->where('name', 'like', "%{$search}%")));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('admin.pages.user-subscriptions.index', [
            'pageTitle' => 'Subscribers',
            'subscriptions' => $query->paginate(12)->withQueryString(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.pages.user-subscriptions.create', [
            'pageTitle' => 'Assign Subscription Plan',
            'users' => User::query()->where('status', 'active')->orderBy('name')->get(),
            'plans' => SubscriptionPlan::query()->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get(),
            'selectedUserId' => $request->integer('user_id') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'plan_id' => ['required', 'exists:subscription_plans,id'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $plan = SubscriptionPlan::query()->findOrFail($data['plan_id']);
        $startsAt = filled($data['starts_at'] ?? null) ? Carbon::parse($data['starts_at']) : now();
        $expiresAt = filled($data['expires_at'] ?? null)
            ? Carbon::parse($data['expires_at'])
            : $startsAt->copy()->addDays($plan->duration_days);

        if ($expiresAt->lessThanOrEqualTo($startsAt)) {
            throw ValidationException::withMessages([
                'expires_at' => 'Expiration must be after the subscription start time.',
            ]);
        }

        // Assign plan access directly without creating a payment.
        UserSubscription::query()->create([
            'user_id' => $data['user_id'],
            'plan_id' => $plan->id,
            'assigned_by' => Auth::id(),
            'source' => 'admin',
            'status' => 'active',
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('admin.user-subscriptions.index')->with('success', 'Subscription plan assigned successfully.');
    }

    public function cancel(UserSubscription $userSubscription): RedirectResponse
    {
        // Cancel future access while preserving the subscription audit record.
        $userSubscription->forceFill([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ])->save();

        return back()->with('success', 'Subscription cancelled successfully.');
    }
}
