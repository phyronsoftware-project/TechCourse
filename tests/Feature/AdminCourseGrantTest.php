<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminCourseGrantTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Keep the grant test isolated from the configured project database.
        config([
            'database.default' => 'admin_grant_test',
            'database.connections.admin_grant_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'session.driver' => 'array',
        ]);
        DB::purge('admin_grant_test');

        // Create only the tables required by the admin grant flow.
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('user');
            $table->string('status')->default('active');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('access_type')->default('free');
            $table->string('status')->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'course_id']);
        });
    }

    protected function tearDown(): void
    {
        // Remove only the isolated in-memory test tables.
        Schema::dropIfExists('course_enrollments');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    // Grant access without payment and reactivate the same enrollment safely.
    public function test_admin_can_grant_and_reactivate_course_access(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ]);
        $user = User::query()->create([
            'name' => 'Student',
            'email' => 'student@example.com',
            'password' => 'password',
            'role' => 'user',
            'status' => 'active',
        ]);
        $course = Course::query()->create([
            'title' => 'Admin Grant Course',
            'slug' => 'admin-grant-course',
        ]);
        $payload = [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'access_type' => 'admin_grant',
            'status' => 'active',
        ];

        $this->withSession(['_token' => 'admin-grant-token'])
            ->actingAs($admin)
            ->post(route('admin.enrollments.store'), $payload, ['X-CSRF-TOKEN' => 'admin-grant-token'])
            ->assertRedirect(route('admin.enrollments.index'));

        $this->assertDatabaseHas('course_enrollments', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'access_type' => 'admin_grant',
            'status' => 'active',
            'order_id' => null,
        ]);

        DB::table('course_enrollments')
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->update(['status' => 'expired']);

        $this->actingAs($admin)
            ->post(route('admin.enrollments.store'), $payload, ['X-CSRF-TOKEN' => 'admin-grant-token'])
            ->assertRedirect(route('admin.enrollments.index'));

        $this->assertDatabaseCount('course_enrollments', 1);
        $this->assertDatabaseHas('course_enrollments', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);
    }
}
