<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\ShopOrder;
use App\Models\ShopOrderItem;
use App\Models\ShopPayment;
use App\Models\ShopProduct;
use App\Services\BakongKhqrService;
use App\Services\BakongPaymentService;
use App\Services\PaymentHistoryService;
use App\Services\ShopBakongPaymentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class BakongPaymentStatusTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Keep payment tests isolated even when the local application has cached MySQL config.
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge();
        DB::setDefaultConnection('sqlite');
        DB::reconnect('sqlite');

        config([
            'bakong.open_api_base_url' => 'https://api-bakong.test',
            'bakong.open_api_token' => 'test-token',
            'bakong.khqr_account_id' => 'merchant@bank',
        ]);

        $this->createPaymentTables();
        $this->createShopTables();
    }

    public function test_course_payment_not_found_remains_pending(): void
    {
        Http::fake([
            'https://api-bakong.test/v1/check_transaction_by_md5' => Http::response([
                'data' => null,
                'errorCode' => 1,
                'responseCode' => 1,
                'responseMessage' => 'Transaction could not be found. Please check and try again.',
            ]),
        ]);

        $payment = Payment::query()->create([
            'payment_no' => 'PAY-TEST-1',
            'payment_provider' => 'bakong_open_api',
            'amount' => 20,
            'currency' => 'USD',
            'khqr_md5' => 'test-md5',
            'status' => 'pending',
            'expired_at' => now()->addMinute(),
        ]);

        $service = new BakongPaymentService(
            Mockery::mock(BakongKhqrService::class),
            app(PaymentHistoryService::class),
        );

        $checked = $service->checkPaymentStatus($payment);

        $this->assertSame('pending', $checked->status);
        $this->assertDatabaseHas('payment_histories', [
            'payment_id' => $payment->id,
            'event' => 'status_checked',
        ]);
    }

    public function test_course_payment_quota_error_remains_pending_when_local_endpoint_is_forbidden(): void
    {
        Http::fake([
            'https://api-bakong.test/v1/check_transaction_by_md5' => Http::response([
                'data' => null,
                'errorCode' => 17,
                'responseCode' => 1,
                'responseMessage' => 'Daily request limit of 100 exceeded. Please try again tomorrow.',
            ]),
            'https://api-bakong.test/local/v1/check_transaction_by_md5' => Http::response(null, 403),
        ]);

        $payment = Payment::query()->create([
            'payment_no' => 'PAY-TEST-QUOTA',
            'payment_provider' => 'bakong_open_api',
            'amount' => 20,
            'currency' => 'USD',
            'khqr_md5' => 'test-md5',
            'status' => 'pending',
            'expired_at' => now()->subSecond(),
        ]);

        // Keep course checkout pending when Bakong quota prevents a conclusive verification.
        $checked = (new BakongPaymentService(
            Mockery::mock(BakongKhqrService::class),
            app(PaymentHistoryService::class),
        ))->checkPaymentStatus($payment);

        $this->assertSame('pending', $checked->status);
        $this->assertSame(17, data_get($checked->bakong_response, 'check_transaction_by_md5.errorCode'));
        Http::assertSentCount(2);
    }

    public function test_shop_payment_success_marks_order_paid_with_real_hash(): void
    {
        Http::fake([
            'https://api-bakong.test/v1/check_transaction_by_md5' => Http::response($this->paidResponse()),
        ]);

        [$payment, $order] = $this->createPendingShopPayment(now()->addMinute());
        $checked = $this->shopService()->checkStatus($payment);

        $this->assertSame('success', $checked->status);
        $this->assertSame(str_repeat('a', 64), $checked->transaction_hash);
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertNotNull($checked->paid_at);
        $this->assertSame(0, ShopProduct::query()->value('stock_qty'));
    }

    public function test_shop_payment_stays_pending_when_bakong_daily_limit_is_exceeded(): void
    {
        Http::fake([
            'https://api-bakong.test/v1/check_transaction_by_md5' => Http::response([
                'responseCode' => 1,
                'responseMessage' => 'Daily request limit of 100 exceeded. Please try again tomorrow.',
                'errorCode' => 17,
                'data' => null,
            ]),
            'https://api-bakong.test/local/v1/check_transaction_by_md5' => Http::response([
                'responseCode' => 1,
                'responseMessage' => 'Daily request limit of 100 exceeded. Please try again tomorrow.',
                'errorCode' => 17,
                'data' => null,
            ]),
        ]);

        [$payment, $order] = $this->createPendingShopPayment(now()->addMinute());

        // Keep an unverified bank payment pending when only the API quota is unavailable.
        $checked = $this->shopService()->checkStatus($payment);

        $this->assertSame('pending', $checked->status);
        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(0, ShopProduct::query()->value('stock_qty'));
    }

    public function test_shop_payment_uses_local_endpoint_when_primary_endpoint_quota_is_exceeded(): void
    {
        Http::fake([
            'https://api-bakong.test/v1/check_transaction_by_md5' => Http::response([
                'responseCode' => 1,
                'responseMessage' => 'Daily request limit of 100 exceeded. Please try again tomorrow.',
                'errorCode' => 17,
                'data' => null,
            ]),
            'https://api-bakong.test/local/v1/check_transaction_by_md5' => Http::response($this->paidResponse()),
        ]);

        [$payment, $order] = $this->createPendingShopPayment(now()->addMinute());

        // Confirm the product payment through the same fallback endpoint used by course payments.
        $checked = $this->shopService()->checkStatus($payment);

        $this->assertSame('success', $checked->status);
        $this->assertSame('paid', $order->fresh()->status);
        Http::assertSentCount(2);
    }

    public function test_shop_payment_recovers_after_an_old_quota_failure_is_confirmed(): void
    {
        Http::fake([
            'https://api-bakong.test/v1/check_transaction_by_md5' => Http::response($this->paidResponse()),
        ]);

        [$payment, $order] = $this->createPendingShopPayment(now()->addMinute());
        ShopProduct::query()->increment('stock_qty');
        $order->forceFill(['status' => 'failed'])->save();
        $payment->forceFill([
            'status' => 'failed',
            'bakong_response' => [
                'check_transaction_by_md5' => [
                    'responseCode' => 1,
                    'errorCode' => 17,
                    'responseMessage' => 'Daily request limit exceeded.',
                ],
            ],
        ])->save();

        // Recheck a falsely failed quota row and reserve its released stock after confirmation.
        $checked = $this->shopService()->checkStatus($payment->fresh());

        $this->assertSame('success', $checked->status);
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame(0, ShopProduct::query()->value('stock_qty'));
    }

    public function test_expired_unpaid_shop_payment_restores_reserved_stock_once(): void
    {
        Http::fake([
            'https://api-bakong.test/v1/check_transaction_by_md5' => Http::response([
                'data' => null,
                'errorCode' => 1,
                'responseCode' => 1,
                'responseMessage' => 'Transaction could not be found. Please check and try again.',
            ]),
        ]);

        [$payment, $order] = $this->createPendingShopPayment(now()->subSecond());
        $service = $this->shopService();

        $this->assertSame('expired', $service->checkStatus($payment)->status);
        $this->assertSame('expired', $order->fresh()->status);
        $this->assertSame(1, ShopProduct::query()->value('stock_qty'));

        $service->checkStatus($payment->fresh());
        $this->assertSame(1, ShopProduct::query()->value('stock_qty'));
    }

    protected function shopService(): ShopBakongPaymentService
    {
        return new ShopBakongPaymentService(Mockery::mock(BakongKhqrService::class));
    }

    protected function createPendingShopPayment($expiresAt): array
    {
        $product = ShopProduct::query()->create([
            'category_id' => 1,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sale_price' => 20,
            'stock_qty' => 0,
        ]);
        $order = ShopOrder::query()->create([
            'user_id' => 10,
            'order_no' => 'SHOP-TEST-1',
            'total_amount' => 20,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_method' => 'bakong_khqr',
        ]);
        ShopOrderItem::query()->create([
            'shop_order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'qty' => 1,
            'unit_price' => 20,
            'line_total' => 20,
        ]);
        $payment = ShopPayment::query()->create([
            'shop_order_id' => $order->id,
            'user_id' => 10,
            'payment_no' => 'SPAY-TEST-1',
            'payment_provider' => 'bakong_open_api',
            'amount' => 20,
            'currency' => 'USD',
            'khqr_md5' => 'test-md5',
            'status' => 'pending',
            'expired_at' => $expiresAt,
        ]);

        return [$payment, $order];
    }

    protected function paidResponse(): array
    {
        return [
            'responseCode' => 0,
            'responseMessage' => 'Getting transaction successfully.',
            'data' => [
                'hash' => str_repeat('a', 64),
                'fromAccountId' => 'customer@bank',
                'toAccountId' => 'merchant@bank',
                'currency' => 'USD',
                'amount' => 20,
                'description' => 'Shop purchase',
            ],
        ];
    }

    protected function createPaymentTables(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('payment_no')->nullable();
            $table->string('payment_provider')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('transaction_hash')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency');
            $table->longText('khqr_md5')->nullable();
            $table->json('bakong_response')->nullable();
            $table->string('status');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });
        Schema::create('payment_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('event');
            $table->string('payment_status')->nullable();
            $table->string('order_status')->nullable();
            $table->string('message')->nullable();
            $table->longText('payload')->nullable();
            $table->timestamps();
        });
    }

    protected function createShopTables(): void
    {
        Schema::create('shop_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('name');
            $table->string('slug');
            $table->decimal('sale_price', 12, 2);
            $table->unsignedInteger('stock_qty')->default(0);
            $table->timestamps();
        });
        Schema::create('shop_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('order_no');
            $table->decimal('total_amount', 12, 2);
            $table->string('currency');
            $table->string('status');
            $table->string('payment_method')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
        Schema::create('shop_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_order_id');
            $table->unsignedBigInteger('product_id');
            $table->string('product_name');
            $table->unsignedInteger('qty');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });
        Schema::create('shop_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_order_id');
            $table->unsignedBigInteger('user_id');
            $table->string('payment_no');
            $table->string('payment_provider');
            $table->string('transaction_id')->nullable();
            $table->string('transaction_hash')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency');
            $table->longText('khqr_string')->nullable();
            $table->string('khqr_md5');
            $table->json('bakong_response')->nullable();
            $table->string('status');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });
    }
}
