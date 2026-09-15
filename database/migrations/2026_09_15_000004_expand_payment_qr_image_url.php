<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payments') || ! Schema::hasColumn('payments', 'qr_image_url')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            // Store the complete Base64 KHQR image instead of truncating it at VARCHAR length.
            DB::statement('ALTER TABLE payments MODIFY qr_image_url LONGTEXT NULL');

            return;
        }

        // Keep non-MySQL environments compatible with the production column type.
        Schema::table('payments', function (Blueprint $table) {
            $table->longText('qr_image_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Never shrink stored QR images because that would truncate payment data.
    }
};
