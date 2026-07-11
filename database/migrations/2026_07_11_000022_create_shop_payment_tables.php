<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('shop_orders')) {
            Schema::create('shop_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('order_no', 80)->unique();
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->enum('status', ['pending', 'paid', 'failed', 'cancelled', 'expired'])->default('pending')->index();
                $table->enum('delivery_status', ['pending', 'delivered'])->default('pending')->index();
                $table->string('payment_method', 50)->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('shop_order_items')) {
            Schema::create('shop_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shop_order_id')->constrained('shop_orders')->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('shop_products')->nullOnDelete();
                $table->string('product_name');
                $table->unsignedInteger('qty')->default(1);
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('line_total', 12, 2)->default(0);
                $table->timestamps();

                $table->index(['shop_order_id', 'product_id']);
            });
        }

        if (! Schema::hasTable('shop_payments')) {
            Schema::create('shop_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shop_order_id')->constrained('shop_orders')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('payment_no', 80)->unique();
                $table->string('payment_provider', 50)->default('bakong_open_api');
                $table->string('transaction_id', 120)->nullable();
                $table->string('transaction_hash', 255)->nullable()->unique();
                $table->decimal('amount', 12, 2)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->longText('khqr_string')->nullable();
                $table->string('khqr_md5', 64)->nullable()->index();
                $table->json('bakong_response')->nullable();
                $table->enum('status', ['pending', 'success', 'failed', 'cancelled', 'expired'])->default('pending')->index();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('expired_at')->nullable()->index();
                $table->timestamps();

                $table->index(['shop_order_id', 'status']);
                $table->index(['user_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_payments');
        Schema::dropIfExists('shop_order_items');
        Schema::dropIfExists('shop_orders');
    }
};
