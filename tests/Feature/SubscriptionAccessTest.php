<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionCoupon;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\CourseAccessService;
use App\Services\SubscriptionCouponService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SubscriptionAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Keep subscription tests isolated from the configured project database.
        config([
            'database.default' => 'subscription_test',
            'database.connections.subscription_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'session.driver' => 'array',
        ]);
        DB::purge('subscription_test');

        Schema::create('users', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('email')->unique(); $table->string('password');
            $table->string('role')->default('user'); $table->string('status')->default('active'); $table->rememberToken(); $table->timestamps();
        });
        Schema::create('courses', function (Blueprint $table) {
            $table->id(); $table->string('title'); $table->string('slug')->unique(); $table->timestamps();
        });
        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id'); $table->unsignedBigInteger('course_id');
            $table->string('status')->default('active'); $table->timestamps();
        });
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('slug')->unique(); $table->text('description')->nullable();
            $table->decimal('price', 10, 2); $table->string('currency')->default('USD'); $table->unsignedInteger('duration_days');
            $table->string('status')->default('active'); $table->boolean('is_featured')->default(false); $table->unsignedInteger('sort_order')->default(0); $table->timestamps();
        });
        Schema::create('subscription_plan_courses', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('plan_id'); $table->unsignedBigInteger('course_id'); $table->timestamps();
        });
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id'); $table->unsignedBigInteger('plan_id'); $table->unsignedBigInteger('assigned_by')->nullable();
            $table->string('source'); $table->string('status'); $table->timestamp('starts_at')->nullable(); $table->timestamp('expires_at')->nullable();
            $table->timestamp('cancelled_at')->nullable(); $table->text('notes')->nullable(); $table->timestamps();
        });
        Schema::create('subscription_coupons', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('code')->unique(); $table->string('discount_type');
            $table->decimal('discount_value', 10, 2); $table->decimal('maximum_discount', 10, 2)->nullable();
            $table->decimal('minimum_amount', 10, 2)->default(0); $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('per_user_limit')->nullable(); $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable(); $table->string('status')->default('active'); $table->timestamps();
        });
        Schema::create('subscription_coupon_plans', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('coupon_id'); $table->unsignedBigInteger('plan_id'); $table->timestamps();
        });
        Schema::create('subscription_coupon_usages', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('coupon_id'); $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('subscription_id')->nullable(); $table->unsignedBigInteger('payment_id')->nullable();
            $table->decimal('discount_amount', 10, 2); $table->timestamp('used_at'); $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        foreach (['subscription_coupon_usages', 'subscription_coupon_plans', 'subscription_coupons', 'user_subscriptions', 'subscription_plan_courses', 'subscription_plans', 'course_enrollments', 'courses', 'users'] as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    // Active plans grant only their selected courses and stop at expiration.
    public function test_active_subscription_grants_selected_course_access(): void
    {
        $user = User::query()->create(['name' => 'Student', 'email' => 'student@example.com', 'password' => 'password']);
        $course = Course::query()->create(['title' => 'Plan Course', 'slug' => 'plan-course']);
        $plan = SubscriptionPlan::query()->create(['name' => 'Pro', 'slug' => 'pro', 'price' => 5, 'duration_days' => 30, 'status' => 'active']);
        $plan->courses()->attach($course->id);
        $subscription = UserSubscription::query()->create([
            'user_id' => $user->id, 'plan_id' => $plan->id, 'source' => 'admin', 'status' => 'active',
            'starts_at' => now()->subDay(), 'expires_at' => now()->addDays(29),
        ]);

        $this->assertTrue(app(CourseAccessService::class)->userHasCourseAccess($user, $course->id));

        $subscription->update(['expires_at' => now()->subMinute()]);
        $this->assertFalse(app(CourseAccessService::class)->userHasCourseAccess($user, $course->id));
    }

    // Admin can assign a selected plan to any active student without payment.
    public function test_admin_can_assign_plan_without_payment(): void
    {
        $admin = User::query()->create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'password', 'role' => 'admin']);
        $student = User::query()->create(['name' => 'Student', 'email' => 'student2@example.com', 'password' => 'password']);
        $plan = SubscriptionPlan::query()->create(['name' => 'Basic', 'slug' => 'basic', 'price' => 3, 'duration_days' => 30, 'status' => 'active']);

        $this->actingAs($admin)->post(route('admin.user-subscriptions.store'), [
            'user_id' => $student->id,
            'plan_id' => $plan->id,
        ])->assertRedirect(route('admin.user-subscriptions.index'));

        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $student->id,
            'plan_id' => $plan->id,
            'assigned_by' => $admin->id,
            'source' => 'admin',
            'status' => 'active',
        ]);
    }

    // Coupon rules calculate the final KHQR total only for allowed plans.
    public function test_subscription_coupon_calculates_percentage_discount(): void
    {
        $user = User::query()->create(['name' => 'Coupon Student', 'email' => 'coupon@example.com', 'password' => 'password']);
        $plan = SubscriptionPlan::query()->create(['name' => 'Pro', 'slug' => 'coupon-pro', 'price' => 10, 'duration_days' => 30, 'status' => 'active']);
        $coupon = SubscriptionCoupon::query()->create([
            'name' => 'Welcome', 'code' => 'WELCOME20', 'discount_type' => 'percentage',
            'discount_value' => 20, 'minimum_amount' => 0, 'per_user_limit' => 1, 'status' => 'active',
        ]);
        $coupon->plans()->attach($plan->id);

        $pricing = app(SubscriptionCouponService::class)->calculate('welcome20', $plan, $user);

        $this->assertSame(10.0, $pricing['subtotal']);
        $this->assertSame(2.0, $pricing['discount']);
        $this->assertSame(8.0, $pricing['total']);
        $this->assertTrue($coupon->is($pricing['coupon']));
    }
}
