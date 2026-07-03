<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Allow the payments table to store the Bakong provider alongside the older ABA value.
        DB::statement("ALTER TABLE payments MODIFY payment_provider ENUM('aba_payway', 'bakong_open_api') NOT NULL DEFAULT 'aba_payway'");
    }

    public function down(): void
    {
        // Collapse the enum back to ABA only if Bakong rows are first mapped away manually.
        DB::statement("ALTER TABLE payments MODIFY payment_provider ENUM('aba_payway') NOT NULL DEFAULT 'aba_payway'");
    }
};
