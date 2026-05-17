<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SettingController;
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
        Route::resource('courses', CourseController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
        Route::resource('banners', BannerController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        Route::resource('courses.lessons', LessonController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('courses.resources', ResourceController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        Route::resource('enrollments', EnrollmentController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
        Route::resource('orders', OrderController::class)->only(['index', 'show', 'destroy']);
        Route::resource('payments', PaymentController::class)->only(['index', 'show', 'destroy']);
        Route::resource('reviews', ReviewController::class)->only(['index', 'show', 'destroy']);

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    });
