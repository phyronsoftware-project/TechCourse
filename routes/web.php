<?php

use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\CourseController;
use App\Http\Controllers\Web\CourseCheckoutController;
use App\Http\Controllers\Web\EngagementController;
use App\Http\Controllers\Web\LearningController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\ShopController;
use App\Http\Controllers\Web\ShopInteractionController;
use App\Http\Controllers\Web\UserAuthController;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/faq', [HomeController::class, 'faq'])->name('faq');
Route::get('/privacy-policy', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/terms-and-conditions', [HomeController::class, 'terms'])->name('terms');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{product}', [ShopController::class, 'show'])->name('shop.show');
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/learning/{course}/{lesson}', [LearningController::class, 'show'])->name('learning.show');

Route::middleware('auth')->group(function () {
    Route::get('/courses/{course}/checkout', [CourseCheckoutController::class, 'show'])->name('courses.checkout');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/courses/{course}/like', [EngagementController::class, 'toggleLike'])->name('courses.like');
    Route::post('/courses/{course}/save', [EngagementController::class, 'toggleSave'])->name('courses.save');
    Route::post('/courses/{course}/comments', [EngagementController::class, 'storeCourseComment'])->name('courses.comments.store');
    Route::put('/courses/{course}/comments/{comment}', [EngagementController::class, 'updateCourseComment'])->name('courses.comments.update');
    Route::delete('/courses/{course}/comments/{comment}', [EngagementController::class, 'destroyCourseComment'])->name('courses.comments.destroy');
    Route::post('/learning/{course}/{lesson}/comments', [EngagementController::class, 'storeLessonComment'])->name('learning.comments.store');
    Route::put('/learning/{course}/{lesson}/comments/{comment}', [EngagementController::class, 'updateLessonComment'])->name('learning.comments.update');
    Route::delete('/learning/{course}/{lesson}/comments/{comment}', [EngagementController::class, 'destroyLessonComment'])->name('learning.comments.destroy');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('web.notifications.read-all');
    Route::get('/shop-data/state', [ShopInteractionController::class, 'state'])->name('shop.state');
    Route::post('/shop-data/cart/toggle', [ShopInteractionController::class, 'toggleCart'])->middleware('throttle:shop-actions')->name('shop.cart.toggle');
    Route::post('/shop-data/cart/qty', [ShopInteractionController::class, 'updateCartQty'])->middleware('throttle:shop-actions')->name('shop.cart.qty');
    Route::post('/shop-data/favorite/toggle', [ShopInteractionController::class, 'toggleFavorite'])->middleware('throttle:shop-actions')->name('shop.favorite.toggle');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [UserAuthController::class, 'createLogin'])->name('web.login');
    Route::post('/login', [UserAuthController::class, 'login'])->middleware('throttle:auth')->name('web.login.store');
    Route::get('/register', [UserAuthController::class, 'createRegister'])->name('web.register');
    Route::post('/register', [UserAuthController::class, 'register'])->middleware('throttle:auth')->name('web.register.store');
    Route::get('/forgot-password', [UserAuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [UserAuthController::class, 'sendResetLink'])->middleware('throttle:auth')->name('password.email');
    Route::get('/reset-password', [UserAuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [UserAuthController::class, 'resetPassword'])->middleware('throttle:auth')->name('password.update');
    Route::get('/auth/google/redirect', [UserAuthController::class, 'redirectToGoogle'])->name('web.google.redirect');
    Route::get('/auth/google/callback', [UserAuthController::class, 'handleGoogleCallback'])->name('web.google.callback');
    Route::get('/auth/facebook/redirect', [UserAuthController::class, 'redirectToFacebook'])->name('web.facebook.redirect');
    Route::get('/auth/facebook/callback', [UserAuthController::class, 'handleFacebookCallback'])->name('web.facebook.callback');
    Route::get('/auth/telegram/callback', [UserAuthController::class, 'handleTelegramCallback'])->name('web.telegram.callback');
    Route::get('/verify-code', [UserAuthController::class, 'showVerifyCode'])->name('web.verify.code.notice');
    Route::post('/verify-code', [UserAuthController::class, 'verifyCode'])->middleware('throttle:otp')->name('web.verify.code.store');
    Route::post('/verify-code/resend', [UserAuthController::class, 'resendCode'])->middleware('throttle:otp')->name('web.verify.code.resend');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [UserAuthController::class, 'logout'])->name('web.logout');
});

Route::get('/language/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['en', 'km'], true), 404);

    session(['locale' => $locale]);

    return Redirect::back();
})->name('language.switch');
