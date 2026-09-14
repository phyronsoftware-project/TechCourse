<?php

namespace App\Services;

use App\Models\CourseEnrollment;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Schema;

class CourseAccessService
{
    // Preserve direct enrollments while also accepting active plan access.
    public function userHasCourseAccess(?User $user, int $courseId): bool
    {
        if (! $user) {
            return false;
        }

        $hasEnrollment = CourseEnrollment::query()
            ->where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->where('status', 'active')
            ->exists();

        if ($hasEnrollment) {
            return true;
        }

        if (! Schema::hasTable('user_subscriptions')
            || ! Schema::hasTable('subscription_plans')
            || ! Schema::hasTable('subscription_plan_courses')) {
            return false;
        }

        return UserSubscription::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->whereHas('plan', function ($planQuery) use ($courseId) {
                $planQuery
                    ->where('status', 'active')
                    ->whereHas('courses', fn ($courseQuery) => $courseQuery->where('courses.id', $courseId));
            })
            ->exists();
    }
}
