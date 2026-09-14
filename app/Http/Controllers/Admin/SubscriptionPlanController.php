<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubscriptionPlanController extends Controller
{
    public function index(Request $request): View
    {
        $query = SubscriptionPlan::query()->withCount(['courses', 'subscriptions'])->orderBy('sort_order')->latest('id');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%"));
        }

        return view('admin.pages.subscription-plans.index', [
            'pageTitle' => 'Subscription Plans',
            'plans' => $query->paginate(10)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.subscription-plans.create', [
            'pageTitle' => 'Create Subscription Plan',
            'courses' => Course::query()->orderBy('title')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $courseIds = $data['course_ids'] ?? [];
        unset($data['course_ids']);
        $data['slug'] = filled($data['slug'] ?? null) ? Str::slug($data['slug']) : Str::slug($data['name']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        // Save the plan and its allowed courses together.
        $plan = SubscriptionPlan::query()->create($data);
        $plan->courses()->sync($courseIds);

        return redirect()->route('admin.subscription-plans.index')->with('success', 'Subscription plan created successfully.');
    }

    public function edit(SubscriptionPlan $subscriptionPlan): View
    {
        $subscriptionPlan->load('courses');

        return view('admin.pages.subscription-plans.edit', [
            'pageTitle' => 'Edit Subscription Plan',
            'plan' => $subscriptionPlan,
            'courses' => Course::query()->orderBy('title')->get(),
            'recordId' => $subscriptionPlan->id,
        ]);
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        $data = $this->validatedData($request, $subscriptionPlan);
        $courseIds = $data['course_ids'] ?? [];
        unset($data['course_ids']);
        $data['slug'] = filled($data['slug'] ?? null) ? Str::slug($data['slug']) : Str::slug($data['name']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        // Update plan details without changing subscriber history.
        $subscriptionPlan->update($data);
        $subscriptionPlan->courses()->sync($courseIds);

        return redirect()->route('admin.subscription-plans.index')->with('success', 'Subscription plan updated successfully.');
    }

    public function destroy(SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        if ($subscriptionPlan->subscriptions()->exists()) {
            return back()->with('warning', 'This plan has subscriber history. Set it inactive instead of deleting it.');
        }

        $subscriptionPlan->delete();

        return redirect()->route('admin.subscription-plans.index')->with('success', 'Subscription plan deleted successfully.');
    }

    protected function validatedData(Request $request, ?SubscriptionPlan $plan = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('subscription_plans', 'slug')->ignore($plan?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'currency' => ['required', 'in:USD'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'status' => ['required', 'in:active,inactive'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'course_ids' => ['nullable', 'array'],
            'course_ids.*' => ['integer', 'exists:courses,id'],
        ]);
    }
}
