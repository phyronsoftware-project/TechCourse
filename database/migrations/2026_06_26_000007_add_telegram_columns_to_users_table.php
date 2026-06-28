<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'telegram_id')) {
                $table->unsignedBigInteger('telegram_id')->nullable()->unique()->after('avatar');
            }

            if (! Schema::hasColumn('users', 'telegram_username')) {
                $table->string('telegram_username')->nullable()->after('telegram_id');
            }

            if (! Schema::hasColumn('users', 'telegram_photo_url')) {
                $table->string('telegram_photo_url')->nullable()->after('telegram_username');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('users', 'telegram_photo_url')) {
                $columns[] = 'telegram_photo_url';
            }

            if (Schema::hasColumn('users', 'telegram_username')) {
                $columns[] = 'telegram_username';
            }

            if (Schema::hasColumn('users', 'telegram_id')) {
                $columns[] = 'telegram_id';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
