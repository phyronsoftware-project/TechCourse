<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payments') || ! Schema::hasColumn('payments', 'payment_provider')) {
            return;
        }

        // Allow the payments table to store the Bakong provider alongside the older ABA value.
        DB::statement("ALTER TABLE payments MODIFY payment_provider ENUM('aba_payway', 'bakong_open_api') NOT NULL DEFAULT 'aba_payway'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('payments') || ! Schema::hasColumn('payments', 'payment_provider')) {
            return;
        }

        // Collapse the enum back to ABA only if Bakong rows are first mapped away manually.
        DB::statement("ALTER TABLE payments MODIFY payment_provider ENUM('aba_payway') NOT NULL DEFAULT 'aba_payway'");
    }
};
