<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\UserController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('users', UserController::class)->only(['index', 'show', 'edit']);
        Route::resource('categories', CategoryController::class)->only(['index', 'create', 'edit']);
        Route::resource('courses', CourseController::class)->only(['index', 'create', 'show', 'edit']);

        Route::resource('courses.lessons', LessonController::class)->only(['index', 'create', 'edit']);
        Route::resource('courses.resources', ResourceController::class)->only(['index', 'create', 'edit']);

        Route::resource('enrollments', EnrollmentController::class)->only(['index', 'create', 'show']);
        Route::resource('orders', OrderController::class)->only(['index', 'show']);
        Route::resource('payments', PaymentController::class)->only(['index', 'show']);
        Route::resource('reviews', ReviewController::class)->only(['index', 'show']);

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    });
