<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Subscription records depend on these existing application tables.
        foreach (['users', 'courses', 'payments'] as $table) {
            if (! Schema::hasTable($table)) {
                throw new RuntimeException("The {$table} table must exist before subscription tables can be created.");
            }
        }

        // Create only missing tables so imported subscription data stays untouched.
        if (! Schema::hasTable('subscription_plans')) {
            Schema::create('subscription_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2)->default(0);
                $table->char('currency', 3)->default('USD');
                $table->unsignedInteger('duration_days');
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->boolean('is_featured')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->index(['status', 'sort_order']);
            });
        }

        if (! Schema::hasTable('subscription_coupons')) {
            Schema::create('subscription_coupons', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code', 80)->unique();
                $table->enum('discount_type', ['percentage', 'fixed']);
                $table->decimal('discount_value', 10, 2);
                $table->decimal('maximum_discount', 10, 2)->nullable();
                $table->decimal('minimum_amount', 10, 2)->default(0);
                $table->unsignedInteger('usage_limit')->nullable();
                $table->unsignedInteger('per_user_limit')->nullable()->default(1);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
                $table->index(['status', 'starts_at', 'expires_at']);
            });
        }

        if (! Schema::hasTable('subscription_plan_courses')) {
            Schema::create('subscription_plan_courses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('plan_id')->constrained('subscription_plans')->cascadeOnDelete();
                $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['plan_id', 'course_id'], 'subscription_plan_course_unique');
            });
        }

        if (! Schema::hasTable('user_subscriptions')) {
            Schema::create('user_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('plan_id')->constrained('subscription_plans')->cascadeOnDelete();
                $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
                $table->enum('source', ['admin', 'payment'])->default('admin');
                $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'status', 'starts_at', 'expires_at'], 'user_subscriptions_access_index');
                $table->index(['plan_id', 'status'], 'user_subscriptions_plan_index');
            });
        }

        if (! Schema::hasTable('subscription_coupon_plans')) {
            Schema::create('subscription_coupon_plans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->constrained('subscription_coupons')->cascadeOnDelete();
                $table->foreignId('plan_id')->constrained('subscription_plans')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['coupon_id', 'plan_id'], 'subscription_coupon_plan_unique');
            });
        }

        // Add only the payment fields required by plan checkout and coupons.
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'subscription_id')) {
                $table->foreignId('subscription_id')->nullable()->constrained('user_subscriptions')->nullOnDelete();
            }

            if (! Schema::hasColumn('payments', 'coupon_id')) {
                $table->foreignId('coupon_id')->nullable()->constrained('subscription_coupons')->nullOnDelete();
            }

            if (! Schema::hasColumn('payments', 'subtotal_amount')) {
                $table->decimal('subtotal_amount', 12, 2)->nullable();
            }

            if (! Schema::hasColumn('payments', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0);
            }
        });

        // Subscription payments have no order, so an existing order_id must allow NULL.
        foreach (Schema::getColumns('payments') as $column) {
            if ($column['name'] === 'order_id' && ! $column['nullable']) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->unsignedBigInteger('order_id')->nullable()->change();
                });

                break;
            }
        }

        if (! Schema::hasTable('subscription_coupon_usages')) {
            Schema::create('subscription_coupon_usages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->constrained('subscription_coupons');
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('subscription_id')->constrained('user_subscriptions')->cascadeOnDelete();
                $table->foreignId('payment_id')->unique('subscription_coupon_usage_payment_unique')->constrained('payments')->cascadeOnDelete();
                $table->decimal('discount_amount', 10, 2);
                $table->timestamp('used_at')->useCurrent();
                $table->timestamps();
                $table->index(['coupon_id', 'user_id'], 'subscription_coupon_usage_limit_index');
            });
        }
    }

    public function down(): void
    {
        // Existing tables may have been imported before this migration; never drop their data on rollback.
    }
};
