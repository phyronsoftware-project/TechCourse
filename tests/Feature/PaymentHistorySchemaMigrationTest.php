<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaymentHistorySchemaMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Isolate schema repair verification from the configured project database.
        config([
            'database.default' => 'payment_history_schema_test',
            'database.connections.payment_history_schema_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('payment_history_schema_test');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
        });
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
        });
    }

    public function test_it_creates_the_payment_history_table_and_can_run_again(): void
    {
        // Re-run the repair to match repeated Render deployments.
        $migration = require database_path('migrations/2026_09_15_000003_repair_payment_histories_schema.php');
        $migration->up();
        $migration->up();

        $this->assertTrue(Schema::hasTable('payment_histories'));

        foreach ([
            'payment_id',
            'order_id',
            'user_id',
            'event',
            'payment_status',
            'order_status',
            'message',
            'payload',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('payment_histories', $column), "Missing payment_histories.{$column} column.");
        }

        // Seed valid parent records before verifying audit history insertion.
        DB::table('users')->insert(['id' => 1]);
        DB::table('orders')->insert(['id' => 1]);
        DB::table('payments')->insert(['id' => 1]);

        DB::table('payment_histories')->insert([
            'payment_id' => 1,
            'order_id' => 1,
            'user_id' => 1,
            'event' => 'checkout_created',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'message' => 'Course checkout order and payment created.',
            'payload' => json_encode(['course_id' => 107]),
        ]);

        $this->assertDatabaseHas('payment_histories', [
            'payment_id' => 1,
            'event' => 'checkout_created',
        ]);
    }
}
