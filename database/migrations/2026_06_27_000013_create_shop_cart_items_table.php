<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->unsignedInteger('qty')->default(1);
            $table->timestamps();

            $table->unique(['user_id', 'product_id'], 'shop_cart_items_user_product_unique');
            $table->index('product_id', 'shop_cart_items_product_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_cart_items');
    }
};
