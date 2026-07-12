<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('provinces')) {
            Schema::create('provinces', function (Blueprint $table) {
                $table->id();
                $table->string('code', 20)->unique();
                $table->string('name_en', 120);
                $table->string('name_km', 120);
                $table->decimal('delivery_fee', 10, 2)->default(2.00);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        // Seed the initial delivery zones; admin can change the fee later.
        $provinces = [
            ['code' => 'PP', 'name_en' => 'Phnom Penh', 'name_km' => 'ភ្នំពេញ', 'delivery_fee' => 1.50],
            ['code' => 'BM', 'name_en' => 'Banteay Meanchey', 'name_km' => 'បន្ទាយមានជ័យ', 'delivery_fee' => 2.00],
            ['code' => 'BTB', 'name_en' => 'Battambang', 'name_km' => 'បាត់ដំបង', 'delivery_fee' => 2.00],
            ['code' => 'KP', 'name_en' => 'Kampong Cham', 'name_km' => 'កំពង់ចាម', 'delivery_fee' => 2.00],
            ['code' => 'KC', 'name_en' => 'Kampong Chhnang', 'name_km' => 'កំពង់ឆ្នាំង', 'delivery_fee' => 2.00],
            ['code' => 'KS', 'name_en' => 'Kampong Speu', 'name_km' => 'កំពង់ស្ពឺ', 'delivery_fee' => 2.00],
            ['code' => 'KT', 'name_en' => 'Kampong Thom', 'name_km' => 'កំពង់ធំ', 'delivery_fee' => 2.00],
            ['code' => 'KPT', 'name_en' => 'Kampot', 'name_km' => 'កំពត', 'delivery_fee' => 2.00],
            ['code' => 'KDL', 'name_en' => 'Kandal', 'name_km' => 'កណ្ដាល', 'delivery_fee' => 2.00],
            ['code' => 'KK', 'name_en' => 'Koh Kong', 'name_km' => 'កោះកុង', 'delivery_fee' => 2.00],
            ['code' => 'KR', 'name_en' => 'Kratie', 'name_km' => 'ក្រចេះ', 'delivery_fee' => 2.00],
            ['code' => 'MB', 'name_en' => 'Mondulkiri', 'name_km' => 'មណ្ឌលគិរី', 'delivery_fee' => 2.00],
            ['code' => 'OD', 'name_en' => 'Oddar Meanchey', 'name_km' => 'ឧត្តរមានជ័យ', 'delivery_fee' => 2.00],
            ['code' => 'PV', 'name_en' => 'Pailin', 'name_km' => 'ប៉ៃលិន', 'delivery_fee' => 2.00],
            ['code' => 'PCH', 'name_en' => 'Preah Sihanouk', 'name_km' => 'ព្រះសីហនុ', 'delivery_fee' => 2.00],
            ['code' => 'PL', 'name_en' => 'Preah Vihear', 'name_km' => 'ព្រះវិហារ', 'delivery_fee' => 2.00],
            ['code' => 'PS', 'name_en' => 'Pursat', 'name_km' => 'ពោធិ៍សាត់', 'delivery_fee' => 2.00],
            ['code' => 'TK', 'name_en' => 'Prey Veng', 'name_km' => 'ព្រៃវែង', 'delivery_fee' => 2.00],
            ['code' => 'RTA', 'name_en' => 'Ratanakiri', 'name_km' => 'រតនគិរី', 'delivery_fee' => 2.00],
            ['code' => 'SR', 'name_en' => 'Siem Reap', 'name_km' => 'សៀមរាប', 'delivery_fee' => 2.00],
            ['code' => 'ST', 'name_en' => 'Stung Treng', 'name_km' => 'ស្ទឹងត្រែង', 'delivery_fee' => 2.00],
            ['code' => 'SV', 'name_en' => 'Svay Rieng', 'name_km' => 'ស្វាយរៀង', 'delivery_fee' => 2.00],
            ['code' => 'TKO', 'name_en' => 'Takeo', 'name_km' => 'តាកែវ', 'delivery_fee' => 2.00],
            ['code' => 'TB', 'name_en' => 'Tboung Khmum', 'name_km' => 'ត្បូងឃ្មុំ', 'delivery_fee' => 2.00],
            ['code' => 'KEP', 'name_en' => 'Kep', 'name_km' => 'កែប', 'delivery_fee' => 2.00],
        ];

        foreach ($provinces as $province) {
            DB::table('provinces')->updateOrInsert(
                ['code' => $province['code']],
                [...$province, 'updated_at' => now(), 'created_at' => now()],
            );
        }

        if (! Schema::hasColumn('users', 'province_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('province_id')->nullable()->after('province')->constrained('provinces')->nullOnDelete();
            });
        }

        // Preserve old free-text province values when they match the new catalog.
        DB::table('users')
            ->join('provinces', function ($join) {
                $join->on('users.province', '=', 'provinces.name_en')
                    ->orOn('users.province', '=', 'provinces.name_km');
            })
            ->whereNull('users.province_id')
            ->update(['users.province_id' => DB::raw('provinces.id')]);

        if (Schema::hasTable('shop_orders')) {
            Schema::table('shop_orders', function (Blueprint $table) {
                if (! Schema::hasColumn('shop_orders', 'subtotal_amount')) {
                    $table->decimal('subtotal_amount', 12, 2)->default(0)->after('total_amount');
                }

                if (! Schema::hasColumn('shop_orders', 'delivery_fee')) {
                    $table->decimal('delivery_fee', 10, 2)->default(0)->after('subtotal_amount');
                }

                if (! Schema::hasColumn('shop_orders', 'province_id')) {
                    $table->foreignId('province_id')->nullable()->after('user_id')->constrained('provinces')->nullOnDelete();
                }

                if (! Schema::hasColumn('shop_orders', 'province_name')) {
                    $table->string('province_name', 120)->nullable()->after('province_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('shop_orders')) {
            Schema::table('shop_orders', function (Blueprint $table) {
                foreach (['province_id', 'province_name', 'delivery_fee', 'subtotal_amount'] as $column) {
                    if (Schema::hasColumn('shop_orders', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasColumn('users', 'province_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['province_id']);
                $table->dropColumn('province_id');
            });
        }

        Schema::dropIfExists('provinces');
    }
};
