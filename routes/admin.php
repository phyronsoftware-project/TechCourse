<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryFeeController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ShopCategoryController;
use App\Http\Controllers\Admin\ShopOrderController;
use App\Http\Controllers\Admin\ShopPaymentController;
use App\Http\Controllers\Admin\ShopProductController;
use App\Http\Controllers\Admin\SocialMediaController;
use App\Http\Controllers\Admin\SoundToolController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\SubscriptionCouponController;
use App\Http\Controllers\Admin\TechCategoryController;
use App\Http\Controllers\Admin\TechDetailController;
use App\Http\Controllers\Admin\TechTableSetupController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserSubscriptionController;
use App\Http\Controllers\Web\AuthController;
use Illuminate\Support\Facades\Route;

$adminPrefix = 'admin/phyron/v1';
$adminLoginPrefix = 'phyron/100203/v1';

// Keep the admin sign-in entry separate from public user authentication.
Route::prefix($adminLoginPrefix)->middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:auth')->name('login.store');
});

Route::prefix($adminPrefix)->middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
});

Route::middleware(['auth', 'admin'])
    ->prefix($adminPrefix)
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('users', UserController::class)->only(['index', 'show', 'edit', 'destroy']);
        Route::resource('categories', CategoryController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('tech-categories', TechCategoryController::class)
            ->parameters(['tech-categories' => 'techCategory'])
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('tech-details', TechDetailController::class)
            ->parameters(['tech-details' => 'techDetail'])
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::post('tech-tables/setup', TechTableSetupController::class)->name('tech-tables.setup');
        Route::resource('shop-categories', ShopCategoryController::class)
            ->parameters(['shop-categories' => 'shopCategory'])
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        // Delete selected shop products in one validated admin request.
        Route::delete('shop-products/bulk-destroy', [ShopProductController::class, 'bulkDestroy'])->name('shop-products.bulk-destroy');
        Route::resource('shop-products', ShopProductController::class)
            ->parameters(['shop-products' => 'shopProduct'])
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::get('shop-orders', [ShopOrderController::class, 'index'])->name('shop-orders.index');
        Route::get('shop-orders/{shopOrder}', [ShopOrderController::class, 'show'])->name('shop-orders.show');
        Route::post('shop-orders/{shopOrder}/delivered', [ShopOrderController::class, 'markDelivered'])->name('shop-orders.delivered');
        Route::get('shop-payments', [ShopPaymentController::class, 'index'])->name('shop-payments.index');
        // Delete selected courses before resource binding handles a course identifier.
        Route::delete('courses/bulk-destroy', [CourseController::class, 'bulkDestroy'])->name('courses.bulk-destroy');
        Route::resource('courses', CourseController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
        Route::resource('banners', BannerController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('social-media', SocialMediaController::class)->parameters(['social-media' => 'social_medium'])->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        Route::resource('courses.lessons', LessonController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('courses.resources', ResourceController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        Route::resource('enrollments', EnrollmentController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
        // Manage subscription plans and direct student assignments separately from course grants.
        Route::resource('subscription-plans', SubscriptionPlanController::class)->except(['show']);
        Route::resource('subscription-coupons', SubscriptionCouponController::class)->except(['show']);
        Route::resource('user-subscriptions', UserSubscriptionController::class)->only(['index', 'create', 'store']);
        Route::post('user-subscriptions/{userSubscription}/cancel', [UserSubscriptionController::class, 'cancel'])->name('user-subscriptions.cancel');
        Route::resource('orders', OrderController::class)->only(['index', 'show', 'destroy']);
        Route::resource('payments', PaymentController::class)->only(['index', 'show', 'destroy']);
        Route::resource('reviews', ReviewController::class)->only(['index', 'show', 'destroy']);
        Route::resource('notifications', NotificationController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('system/delivery-fees', [DeliveryFeeController::class, 'index'])->name('delivery-fees.index');
        Route::put('system/delivery-fees', [DeliveryFeeController::class, 'update'])->name('delivery-fees.update');
        Route::get('tools/sound', [SoundToolController::class, 'index'])->name('tools.sound');
        Route::get('tools/sound/audio', [SoundToolController::class, 'audio'])->name('tools.sound.audio');
        Route::get('tools/sound/voices', [SoundToolController::class, 'voices'])->name('tools.sound.voices');
        Route::post('tools/sound/extract-audio', [SoundToolController::class, 'extractAudio'])->name('tools.sound.extract-audio');
        Route::post('tools/sound/clone-voice', [SoundToolController::class, 'cloneVoice'])->name('tools.sound.clone-voice');
    });
