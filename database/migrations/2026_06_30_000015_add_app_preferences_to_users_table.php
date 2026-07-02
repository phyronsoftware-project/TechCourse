<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'app_language')) {
                $table->string('app_language', 10)->default('km')->after('notification_muted');
            }

            if (! Schema::hasColumn('users', 'app_sound_enabled')) {
                $table->boolean('app_sound_enabled')->default(true)->after('app_language');
            }

            if (! Schema::hasColumn('users', 'app_vibrate_enabled')) {
                $table->boolean('app_vibrate_enabled')->default(true)->after('app_sound_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $dropColumns = [];

            foreach (['app_language', 'app_sound_enabled', 'app_vibrate_enabled'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
