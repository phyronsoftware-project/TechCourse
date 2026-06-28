<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\DashboardController;
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
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Web\AuthController;
use Illuminate\Support\Facades\Route;

$adminPrefix = 'admin/phyron/v1';

Route::prefix($adminPrefix)->middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
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
        Route::resource('shop-categories', ShopCategoryController::class)
            ->parameters(['shop-categories' => 'shopCategory'])
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('shop-products', ShopProductController::class)
            ->parameters(['shop-products' => 'shopProduct'])
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::get('shop-orders', [ShopOrderController::class, 'index'])->name('shop-orders.index');
        Route::get('shop-payments', [ShopPaymentController::class, 'index'])->name('shop-payments.index');
        Route::resource('courses', CourseController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
        Route::resource('banners', BannerController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('social-media', SocialMediaController::class)->parameters(['social-media' => 'social_medium'])->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        Route::resource('courses.lessons', LessonController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('courses.resources', ResourceController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        Route::resource('enrollments', EnrollmentController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
        Route::resource('orders', OrderController::class)->only(['index', 'show', 'destroy']);
        Route::resource('payments', PaymentController::class)->only(['index', 'show', 'destroy']);
        Route::resource('reviews', ReviewController::class)->only(['index', 'show', 'destroy']);
        Route::resource('notifications', NotificationController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('tools/sound', [SoundToolController::class, 'index'])->name('tools.sound');
        Route::get('tools/sound/audio', [SoundToolController::class, 'audio'])->name('tools.sound.audio');
        Route::get('tools/sound/voices', [SoundToolController::class, 'voices'])->name('tools.sound.voices');
        Route::post('tools/sound/extract-audio', [SoundToolController::class, 'extractAudio'])->name('tools.sound.extract-audio');
        Route::post('tools/sound/clone-voice', [SoundToolController::class, 'cloneVoice'])->name('tools.sound.clone-voice');
    });
