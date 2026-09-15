<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_histories')) {
            return;
        }

        // Create the payment audit trail required by course and subscription checkout.
        Schema::create('payment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 120);
            $table->string('payment_status', 50)->nullable();
            $table->string('order_status', 50)->nullable();
            $table->string('message')->nullable();
            $table->longText('payload')->nullable();
            $table->timestamps();

            $table->index(['payment_id', 'id']);
            $table->index(['order_id', 'id']);
            $table->index('event');
        });
    }

    public function down(): void
    {
        // Preserve production payment audit records during a rollback.
    }
};
