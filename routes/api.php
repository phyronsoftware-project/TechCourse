<?php

use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\SocialMediaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('app')->group(function () {
    Route::middleware('throttle:api-public')->group(function () {
        Route::post('/auth/login', [AuthController::class, 'login'])->name('api.app.auth.login');
        Route::post('/auth/register', [AuthController::class, 'register'])->name('api.app.auth.register');
        Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->name('api.app.auth.forgot-password');
        Route::get('/auth/social-providers', [AuthController::class, 'socialProviders'])->name('api.app.auth.social-providers');
        Route::post('/auth/social/telegram/login', [AuthController::class, 'telegramLogin'])->name('api.app.auth.social.telegram.login');
        Route::get('/home', [CourseController::class, 'home'])->name('api.app.home');
        Route::get('/shop/home', [ShopController::class, 'home'])->name('api.app.shop.home');
        Route::get('/shop/categories', [ShopController::class, 'categories'])->name('api.app.shop.categories');
        Route::get('/shop/products', [ShopController::class, 'index'])->name('api.app.shop.products.index');
        Route::get('/shop/products/{product}', [ShopController::class, 'show'])->name('api.app.shop.products.show');
        Route::get('/course-categories', [CourseController::class, 'categories'])->name('api.app.course-categories');
        Route::get('/courses', [CourseController::class, 'index'])->name('api.app.courses.index');
        Route::get('/courses/{course}', [CourseController::class, 'show'])->name('api.app.courses.show');
        Route::get('/banners', [BannerController::class, 'index'])->name('api.app.banners.index');
        Route::get('/social-media', [SocialMediaController::class, 'index'])->name('api.app.social-media.index');
    });

    Route::middleware(['web', 'throttle:api-public'])->group(function () {
        Route::get('/auth/social/{provider}/start', [AuthController::class, 'socialStart'])->name('api.app.auth.social.start');
        Route::get('/auth/social/{provider}/callback', [AuthController::class, 'socialCallback'])->name('api.app.auth.social.callback');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me'])->name('api.app.auth.me');
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.app.auth.logout');
        Route::post('/courses/{course}/like', [CourseController::class, 'toggleLike'])->name('api.app.courses.like');
        Route::post('/courses/{course}/save', [CourseController::class, 'toggleSave'])->name('api.app.courses.save');
        Route::get('/shop/cart', [ShopController::class, 'cart'])->name('api.app.shop.cart');
        Route::post('/shop/cart', [ShopController::class, 'storeCartItem'])->name('api.app.shop.cart.store');
        Route::post('/shop/cart/{product}/quantity', [ShopController::class, 'updateCartQuantity'])->name('api.app.shop.cart.quantity');
        Route::get('/shop/favorites', [ShopController::class, 'favorites'])->name('api.app.shop.favorites');
        Route::post('/shop/favorites/toggle', [ShopController::class, 'toggleFavorite'])->name('api.app.shop.favorites.toggle');
        Route::get('/profile', [ProfileController::class, 'show'])->name('api.app.profile.show');
        Route::post('/profile', [ProfileController::class, 'update'])->name('api.app.profile.update');
        Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('api.app.profile.password.update');
        Route::get('/profile/my-courses', [ProfileController::class, 'myCourses'])->name('api.app.profile.my-courses');
        Route::get('/profile/liked-courses', [ProfileController::class, 'likedCourses'])->name('api.app.profile.liked-courses');
        Route::get('/profile/saved-courses', [ProfileController::class, 'savedCourses'])->name('api.app.profile.saved-courses');
        Route::get('/profile/lesson-comments', [ProfileController::class, 'lessonComments'])->name('api.app.profile.lesson-comments');
        Route::get('/profile/course-orders', [ProfileController::class, 'courseOrders'])->name('api.app.profile.course-orders');
        Route::get('/profile/shop-orders', [ProfileController::class, 'shopOrders'])->name('api.app.profile.shop-orders');
        Route::get('/profile/notifications', [ProfileController::class, 'notifications'])->name('api.app.profile.notifications');
        Route::post('/profile/notifications/read-all', [ProfileController::class, 'markNotificationsRead'])->name('api.app.profile.notifications.read-all');
        Route::get('/profile/settings/notifications', [ProfileController::class, 'notificationSettings'])->name('api.app.profile.settings.notifications');
        Route::post('/profile/settings/notifications', [ProfileController::class, 'updateNotificationSettings'])->name('api.app.profile.settings.notifications.update');
        Route::get('/profile/settings/preferences', [ProfileController::class, 'preferenceSettings'])->name('api.app.profile.settings.preferences');
        Route::post('/profile/settings/preferences', [ProfileController::class, 'updatePreferenceSettings'])->name('api.app.profile.settings.preferences.update');
    });
});

Route::middleware('throttle:api-public')->group(function () {
    Route::get('/web/banners', [BannerController::class, 'index'])->name('api.web.banners.index');
    Route::get('/social-media', [SocialMediaController::class, 'index'])->name('api.social-media.index');
});
