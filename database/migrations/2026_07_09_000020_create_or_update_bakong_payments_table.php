<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keep the migration compatible with projects that already have a payments table.
        if (! Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('payment_no')->unique();
                $table->string('payment_provider')->default('bakong_open_api');
                $table->string('transaction_id')->nullable();
                $table->string('merchant_id')->nullable();
                $table->string('req_time', 32)->nullable();
                $table->string('payment_option')->nullable();
                $table->decimal('amount', 12, 2);
                $table->string('currency')->default('KHR');
                $table->longText('khqr_string')->nullable();
                $table->string('khqr_md5')->nullable()->index();
                $table->string('transaction_hash')->nullable()->index();
                $table->text('checkout_url')->nullable();
                $table->longText('abapay_deeplink')->nullable();
                $table->longText('khqr_deeplink')->nullable();
                $table->longText('qr_image_url')->nullable();
                $table->json('response_payload')->nullable();
                $table->json('callback_payload')->nullable();
                $table->json('bakong_response')->nullable();
                $table->string('status')->default('pending')->index();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('expired_at')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('payments', function (Blueprint $table) {
                // Add Bakong payment columns without disturbing the older ABA checkout schema.
                if (! Schema::hasColumn('payments', 'payment_no')) {
                    $table->string('payment_no')->nullable()->after('user_id');
                }

                if (! Schema::hasColumn('payments', 'khqr_string')) {
                    $table->longText('khqr_string')->nullable()->after('currency');
                }

                if (! Schema::hasColumn('payments', 'khqr_md5')) {
                    $table->string('khqr_md5')->nullable()->after('khqr_string');
                }

                if (! Schema::hasColumn('payments', 'transaction_hash')) {
                    $table->string('transaction_hash')->nullable()->after('khqr_md5');
                }

                if (! Schema::hasColumn('payments', 'bakong_response')) {
                    $table->json('bakong_response')->nullable()->after('transaction_hash');
                }

                if (! Schema::hasColumn('payments', 'expired_at')) {
                    $table->timestamp('expired_at')->nullable()->after('paid_at');
                }
            });
        }

        $this->applyIndexes();
        $this->applyForeignKeys();
    }

    public function down(): void
    {
        if (! Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            foreach (['payments_user_id_foreign', 'payments_order_id_foreign'] as $foreignKey) {
                try {
                    $table->dropForeign($foreignKey);
                } catch (Throwable) {
                }
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            foreach ([
                'payments_payment_no_unique',
                'payments_payment_no_index',
                'payments_khqr_md5_index',
                'payments_transaction_hash_index',
                'payments_status_index',
            ] as $indexName) {
                try {
                    $table->dropIndex($indexName);
                } catch (Throwable) {
                }
            }

            $columns = array_values(array_filter([
                Schema::hasColumn('payments', 'payment_no') ? 'payment_no' : null,
                Schema::hasColumn('payments', 'khqr_string') ? 'khqr_string' : null,
                Schema::hasColumn('payments', 'khqr_md5') ? 'khqr_md5' : null,
                Schema::hasColumn('payments', 'transaction_hash') ? 'transaction_hash' : null,
                Schema::hasColumn('payments', 'bakong_response') ? 'bakong_response' : null,
                Schema::hasColumn('payments', 'expired_at') ? 'expired_at' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    protected function applyIndexes(): void
    {
        if (! Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'payment_no')) {
                try {
                    $table->unique('payment_no', 'payments_payment_no_unique');
                } catch (Throwable) {
                    try {
                        $table->index('payment_no', 'payments_payment_no_index');
                    } catch (Throwable) {
                    }
                }
            }

            foreach ([
                'khqr_md5' => 'payments_khqr_md5_index',
                'transaction_hash' => 'payments_transaction_hash_index',
                'status' => 'payments_status_index',
            ] as $column => $indexName) {
                if (! Schema::hasColumn('payments', $column)) {
                    continue;
                }

                try {
                    $table->index($column, $indexName);
                } catch (Throwable) {
                }
            }
        });
    }

    protected function applyForeignKeys(): void
    {
        if (! Schema::hasTable('payments') || ! Schema::hasTable('users') || ! Schema::hasColumn('payments', 'user_id')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            try {
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            } catch (Throwable) {
            }
        });

        if (! Schema::hasTable('orders') || ! Schema::hasColumn('payments', 'order_id')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            try {
                $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            } catch (Throwable) {
            }
        });
    }
};
