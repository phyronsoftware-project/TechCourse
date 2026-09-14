<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SubscriptionSchemaMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Keep migration verification away from the configured project database.
        config([
            'database.default' => 'subscription_schema_test',
            'database.connections.subscription_schema_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('subscription_schema_test');

        // Provide only the pre-existing tables that subscription schema references.
        Schema::create('users', fn (Blueprint $table) => $table->id());
        Schema::create('courses', fn (Blueprint $table) => $table->id());
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
        });
    }

    public function test_it_creates_missing_tables_and_preserves_them_when_reapplied(): void
    {
        // Running the migration twice must not replace existing plans or payment columns.
        $migration = require database_path('migrations/2026_09_14_000001_create_subscription_schema.php');
        $migration->up();

        foreach ([
            'subscription_plans',
            'subscription_plan_courses',
            'user_subscriptions',
            'subscription_coupons',
            'subscription_coupon_plans',
            'subscription_coupon_usages',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing {$table} table.");
        }

        foreach (['subscription_id', 'coupon_id', 'subtotal_amount', 'discount_amount'] as $column) {
            $this->assertTrue(Schema::hasColumn('payments', $column), "Missing payments.{$column} column.");
        }

        $orderColumn = collect(Schema::getColumns('payments'))->firstWhere('name', 'order_id');
        $this->assertTrue($orderColumn['nullable']);

        DB::table('subscription_plans')->insert([
            'name' => 'Existing plan',
            'slug' => 'existing-plan',
            'price' => 4.99,
            'duration_days' => 30,
        ]);

        $migration->up();

        $this->assertDatabaseHas('subscription_plans', ['slug' => 'existing-plan']);
        $this->assertSame(1, DB::table('subscription_plans')->count());
    }
}
