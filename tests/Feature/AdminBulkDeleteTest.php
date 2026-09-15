<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBulkDeleteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Keep destructive bulk-delete tests isolated from the configured database.
        config([
            'database.default' => 'admin_bulk_delete_test',
            'database.connections.admin_bulk_delete_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'session.driver' => 'array',
        ]);
        DB::purge('admin_bulk_delete_test');
        Storage::fake('public');

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
            $table->string('thumbnail')->nullable();
            $table->timestamps();
        });

        foreach (['lesson_progress', 'course_favorites', 'course_reviews', 'course_enrollments', 'order_items', 'course_resources', 'course_lessons'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('course_id');
            });
        }

        Schema::create('shop_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('shop_product_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('image_path');
            $table->timestamps();
        });
    }

    public function test_admin_can_delete_multiple_courses_in_one_request(): void
    {
        $admin = $this->createAdmin();
        $first = Course::query()->create(['title' => 'First', 'slug' => 'first', 'thumbnail' => 'courses/first.jpg']);
        $second = Course::query()->create(['title' => 'Second', 'slug' => 'second', 'thumbnail' => 'courses/second.jpg']);
        $kept = Course::query()->create(['title' => 'Kept', 'slug' => 'kept']);
        Storage::disk('public')->put('courses/first.jpg', 'first');
        Storage::disk('public')->put('courses/second.jpg', 'second');
        DB::table('course_lessons')->insert(['course_id' => $first->id]);

        // Submit selected IDs once and retain every unselected course.
        $this->actingAs($admin)
            ->delete(route('admin.courses.bulk-destroy'), ['ids' => [$first->id, $second->id]])
            ->assertRedirect()
            ->assertSessionHas('success', '2 courses deleted successfully.');

        $this->assertDatabaseMissing('courses', ['id' => $first->id]);
        $this->assertDatabaseMissing('courses', ['id' => $second->id]);
        $this->assertDatabaseHas('courses', ['id' => $kept->id]);
        $this->assertDatabaseMissing('course_lessons', ['course_id' => $first->id]);
        Storage::disk('public')->assertMissing('courses/first.jpg');
        Storage::disk('public')->assertMissing('courses/second.jpg');
    }

    public function test_admin_can_delete_multiple_shop_products_in_one_request(): void
    {
        $admin = $this->createAdmin();
        $first = ShopProduct::query()->create(['name' => 'First product', 'image' => 'shop/products/first.jpg']);
        $second = ShopProduct::query()->create(['name' => 'Second product', 'image' => 'shop/products/second.jpg']);
        $kept = ShopProduct::query()->create(['name' => 'Kept product']);
        Storage::disk('public')->put('shop/products/first.jpg', 'first');
        Storage::disk('public')->put('shop/products/second.jpg', 'second');
        DB::table('shop_product_images')->insert([
            'product_id' => $first->id,
            'image_path' => 'shop/products/gallery/first.jpg',
        ]);
        Storage::disk('public')->put('shop/products/gallery/first.jpg', 'gallery');

        // Delete selected products and their gallery files through one endpoint.
        $this->actingAs($admin)
            ->delete(route('admin.shop-products.bulk-destroy'), ['ids' => [$first->id, $second->id]])
            ->assertRedirect()
            ->assertSessionHas('success', '2 shop products deleted successfully.');

        $this->assertDatabaseMissing('shop_products', ['id' => $first->id]);
        $this->assertDatabaseMissing('shop_products', ['id' => $second->id]);
        $this->assertDatabaseHas('shop_products', ['id' => $kept->id]);
        $this->assertDatabaseMissing('shop_product_images', ['product_id' => $first->id]);
        Storage::disk('public')->assertMissing('shop/products/first.jpg');
        Storage::disk('public')->assertMissing('shop/products/second.jpg');
        Storage::disk('public')->assertMissing('shop/products/gallery/first.jpg');
    }

    protected function createAdmin(): User
    {
        // Authenticate every request with the exact role required by admin middleware.
        return User::query()->create([
            'name' => 'Admin',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ]);
    }
}
