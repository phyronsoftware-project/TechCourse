<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payments')) {
            throw new RuntimeException('The payments table must exist before its checkout schema can be repaired.');
        }

        // Add every field shared by course and subscription KHQR checkout when missing.
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'order_id')) {
                $table->unsignedBigInteger('order_id')->nullable();
            }

            if (! Schema::hasColumn('payments', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable();
            }

            if (! Schema::hasColumn('payments', 'subscription_id')) {
                $table->unsignedBigInteger('subscription_id')->nullable();
            }

            if (! Schema::hasColumn('payments', 'coupon_id')) {
                $table->unsignedBigInteger('coupon_id')->nullable();
            }

            if (! Schema::hasColumn('payments', 'payment_no')) {
                $table->string('payment_no')->nullable();
            }

            if (! Schema::hasColumn('payments', 'payment_provider')) {
                $table->string('payment_provider')->default('aba_payway');
            }

            if (! Schema::hasColumn('payments', 'transaction_id')) {
                $table->string('transaction_id')->nullable();
            }

            if (! Schema::hasColumn('payments', 'transaction_hash')) {
                $table->string('transaction_hash')->nullable();
            }

            if (! Schema::hasColumn('payments', 'merchant_id')) {
                $table->string('merchant_id')->nullable();
            }

            if (! Schema::hasColumn('payments', 'req_time')) {
                $table->string('req_time', 32)->nullable();
            }

            if (! Schema::hasColumn('payments', 'payment_option')) {
                $table->string('payment_option')->nullable();
            }

            if (! Schema::hasColumn('payments', 'amount')) {
                $table->decimal('amount', 12, 2)->default(0);
            }

            if (! Schema::hasColumn('payments', 'subtotal_amount')) {
                $table->decimal('subtotal_amount', 12, 2)->nullable();
            }

            if (! Schema::hasColumn('payments', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0);
            }

            if (! Schema::hasColumn('payments', 'currency')) {
                $table->string('currency', 10)->default('USD');
            }

            if (! Schema::hasColumn('payments', 'khqr_string')) {
                $table->longText('khqr_string')->nullable();
            }

            if (! Schema::hasColumn('payments', 'khqr_md5')) {
                $table->string('khqr_md5')->nullable();
            }

            if (! Schema::hasColumn('payments', 'checkout_url')) {
                $table->text('checkout_url')->nullable();
            }

            if (! Schema::hasColumn('payments', 'abapay_deeplink')) {
                $table->longText('abapay_deeplink')->nullable();
            }

            if (! Schema::hasColumn('payments', 'khqr_deeplink')) {
                $table->longText('khqr_deeplink')->nullable();
            }

            if (! Schema::hasColumn('payments', 'qr_image_url')) {
                $table->longText('qr_image_url')->nullable();
            }

            if (! Schema::hasColumn('payments', 'response_payload')) {
                $table->json('response_payload')->nullable();
            }

            if (! Schema::hasColumn('payments', 'callback_payload')) {
                $table->json('callback_payload')->nullable();
            }

            if (! Schema::hasColumn('payments', 'bakong_response')) {
                $table->json('bakong_response')->nullable();
            }

            if (! Schema::hasColumn('payments', 'status')) {
                $table->string('status')->default('pending');
            }

            if (! Schema::hasColumn('payments', 'paid_at')) {
                $table->timestamp('paid_at')->nullable();
            }

            if (! Schema::hasColumn('payments', 'expired_at')) {
                $table->timestamp('expired_at')->nullable();
            }

            if (! Schema::hasColumn('payments', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('payments', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        // Subscription payments do not have a course order, so order_id must accept NULL.
        foreach (Schema::getColumns('payments') as $column) {
            if ($column['name'] === 'order_id' && ! $column['nullable']) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->unsignedBigInteger('order_id')->nullable()->change();
                });

                break;
            }
        }

        if (DB::getDriverName() === 'mysql') {
            // Keep both payment provider values accepted by the current checkout services.
            DB::statement("ALTER TABLE payments MODIFY payment_provider ENUM('aba_payway', 'bakong_open_api') NOT NULL DEFAULT 'aba_payway'");
        }

        try {
            if (! Schema::hasIndex('payments', 'payments_payment_no_unique')) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->unique('payment_no', 'payments_payment_no_unique');
                });
            }
        } catch (Throwable) {
            // Existing imported values remain usable even if they prevent a unique index.
        }
    }

    public function down(): void
    {
        // Never remove repaired production payment fields or historical payment data.
    }
};
