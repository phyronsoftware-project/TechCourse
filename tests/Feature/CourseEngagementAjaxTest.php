<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CourseEngagementAjaxTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Keep engagement tests isolated from the configured project database.
        config([
            'database.default' => 'engagement_test',
            'database.connections.engagement_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'session.driver' => 'array',
        ]);
        DB::purge('engagement_test');

        // Create only the isolated tables required by course engagement tests.
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('course_favorites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('course_id');
            $table->timestamps();
        });

        Schema::create('course_saves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('course_id');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        // Remove the isolated engagement test tables.
        Schema::dropIfExists('course_saves');
        Schema::dropIfExists('course_favorites');
        Schema::dropIfExists('courses');

        parent::tearDown();
    }

    // Toggle course likes through JSON without redirecting the page.
    public function test_authenticated_user_can_toggle_course_like_with_ajax(): void
    {
        [$user, $course] = $this->engagementRecords();

        $this->withSession(['_token' => 'engagement-test-token'])
            ->actingAs($user)
            ->postJson(route('courses.like', $course->slug), [], ['X-CSRF-TOKEN' => 'engagement-test-token'])
            ->assertOk()
            ->assertJsonPath('data.is_liked', true);

        $this->assertDatabaseHas('course_favorites', [
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        $this->actingAs($user)
            ->postJson(route('courses.like', $course->slug), [], ['X-CSRF-TOKEN' => 'engagement-test-token'])
            ->assertOk()
            ->assertJsonPath('data.is_liked', false);

        $this->assertDatabaseMissing('course_favorites', [
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);
    }

    // Toggle saved courses through JSON without redirecting the page.
    public function test_authenticated_user_can_toggle_course_save_with_ajax(): void
    {
        [$user, $course] = $this->engagementRecords();

        $this->withSession(['_token' => 'engagement-test-token'])
            ->actingAs($user)
            ->postJson(route('courses.save', $course->slug), [], ['X-CSRF-TOKEN' => 'engagement-test-token'])
            ->assertOk()
            ->assertJsonPath('data.is_saved', true);

        $this->assertDatabaseHas('course_saves', [
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        $this->actingAs($user)
            ->postJson(route('courses.save', $course->slug), [], ['X-CSRF-TOKEN' => 'engagement-test-token'])
            ->assertOk()
            ->assertJsonPath('data.is_saved', false);

        $this->assertDatabaseMissing('course_saves', [
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);
    }

    // Prepare a signed-in user and one course without touching project data.
    private function engagementRecords(): array
    {
        $user = new User(['name' => 'Course Tester', 'email' => 'course@example.com']);
        $user->id = 1001;

        $course = Course::query()->create([
            'title' => 'AJAX Course',
            'slug' => 'ajax-course',
        ]);

        return [$user, $course];
    }
}
