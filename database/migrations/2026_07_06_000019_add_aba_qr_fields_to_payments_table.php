<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payments')) {
            return;
        }

        // Add the ABA payment fields that the current checkout flow stores on each payment record.
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'merchant_id')) {
                $table->string('merchant_id')->nullable()->after('transaction_id');
            }

            if (! Schema::hasColumn('payments', 'req_time')) {
                $table->string('req_time', 32)->nullable()->after('merchant_id');
            }

            if (! Schema::hasColumn('payments', 'payment_option')) {
                $table->string('payment_option')->nullable()->after('req_time');
            }

            if (! Schema::hasColumn('payments', 'checkout_url')) {
                $table->text('checkout_url')->nullable()->after('status');
            }

            if (! Schema::hasColumn('payments', 'abapay_deeplink')) {
                $table->longText('abapay_deeplink')->nullable()->after('checkout_url');
            }

            if (! Schema::hasColumn('payments', 'khqr_deeplink')) {
                $table->longText('khqr_deeplink')->nullable()->after('abapay_deeplink');
            }

            if (! Schema::hasColumn('payments', 'qr_image_url')) {
                $table->longText('qr_image_url')->nullable()->after('khqr_deeplink');
            }

            if (! Schema::hasColumn('payments', 'callback_payload')) {
                $table->json('callback_payload')->nullable()->after('response_payload');
            }

            if (! Schema::hasColumn('payments', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('callback_payload');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('payments')) {
            return;
        }

        // Keep rollback limited to the ABA fields added by this migration.
        Schema::table('payments', function (Blueprint $table) {
            $columns = [
                'merchant_id',
                'req_time',
                'payment_option',
                'checkout_url',
                'abapay_deeplink',
                'khqr_deeplink',
                'qr_image_url',
                'callback_payload',
                'paid_at',
            ];

            $existingColumns = array_values(array_filter(
                $columns,
                static fn (string $column): bool => Schema::hasColumn('payments', $column)
            ));

            if ($existingColumns !== []) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
