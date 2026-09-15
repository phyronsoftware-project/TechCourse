<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaymentCheckoutSchemaMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Keep payment schema repair tests away from the configured project database.
        config([
            'database.default' => 'payment_schema_test',
            'database.connections.payment_schema_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('payment_schema_test');

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
        });
    }

    public function test_it_repairs_all_checkout_columns_and_can_run_again_safely(): void
    {
        // Apply the same migration twice to match repeated Render deployments.
        $migration = require database_path('migrations/2026_09_15_000002_repair_payment_checkout_schema.php');
        $migration->up();

        foreach ([
            'payment_no',
            'payment_provider',
            'transaction_id',
            'transaction_hash',
            'merchant_id',
            'req_time',
            'payment_option',
            'amount',
            'subtotal_amount',
            'discount_amount',
            'currency',
            'khqr_string',
            'khqr_md5',
            'khqr_deeplink',
            'qr_image_url',
            'response_payload',
            'callback_payload',
            'bakong_response',
            'status',
            'paid_at',
            'expired_at',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('payments', $column), "Missing payments.{$column} column.");
        }

        $orderColumn = collect(Schema::getColumns('payments'))->firstWhere('name', 'order_id');
        $this->assertTrue($orderColumn['nullable']);

        DB::table('payments')->insert([
            'payment_no' => 'PAY-SCHEMA-TEST',
            'payment_provider' => 'aba_payway',
            'amount' => 3,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $migration->up();

        $this->assertDatabaseHas('payments', ['payment_no' => 'PAY-SCHEMA-TEST']);
    }
}
