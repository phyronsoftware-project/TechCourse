<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaymentQrImageSchemaMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Isolate QR column repair verification from the configured project database.
        config([
            'database.default' => 'payment_qr_schema_test',
            'database.connections.payment_qr_schema_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('payment_qr_schema_test');

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('qr_image_url')->nullable();
        });
    }

    public function test_it_expands_qr_image_storage_without_truncating_base64_data(): void
    {
        // Apply the production repair before storing a complete QR data URI.
        $migration = require database_path('migrations/2026_09_15_000004_expand_payment_qr_image_url.php');
        $migration->up();

        $imageDataUri = 'data:image/png;base64,' . str_repeat('A', 8000);

        DB::table('payments')->insert([
            'qr_image_url' => $imageDataUri,
        ]);

        $this->assertSame(
            $imageDataUri,
            DB::table('payments')->value('qr_image_url')
        );
    }
}
