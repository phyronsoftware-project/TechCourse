<?php

use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\CourseController;
use App\Http\Controllers\Web\CourseCheckoutController;
use App\Http\Controllers\Web\EngagementController;
use App\Http\Controllers\Web\LearningController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\UserAuthController;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
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
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [UserAuthController::class, 'createLogin'])->name('web.login');
    Route::post('/login', [UserAuthController::class, 'login'])->name('web.login.store');
    Route::get('/register', [UserAuthController::class, 'createRegister'])->name('web.register');
    Route::post('/register', [UserAuthController::class, 'register'])->name('web.register.store');
    Route::get('/forgot-password', [UserAuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [UserAuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password', [UserAuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [UserAuthController::class, 'resetPassword'])->name('password.update');
    Route::get('/auth/google/redirect', [UserAuthController::class, 'redirectToGoogle'])->name('web.google.redirect');
    Route::get('/auth/google/callback', [UserAuthController::class, 'handleGoogleCallback'])->name('web.google.callback');
    Route::get('/auth/facebook/redirect', [UserAuthController::class, 'redirectToFacebook'])->name('web.facebook.redirect');
    Route::get('/auth/facebook/callback', [UserAuthController::class, 'handleFacebookCallback'])->name('web.facebook.callback');
    Route::get('/auth/github/redirect', [UserAuthController::class, 'redirectToGithub'])->name('web.github.redirect');
    Route::get('/auth/github/callback', [UserAuthController::class, 'handleGithubCallback'])->name('web.github.callback');
    Route::get('/verify-code', [UserAuthController::class, 'showVerifyCode'])->name('web.verify.code.notice');
    Route::post('/verify-code', [UserAuthController::class, 'verifyCode'])->name('web.verify.code.store');
    Route::post('/verify-code/resend', [UserAuthController::class, 'resendCode'])->name('web.verify.code.resend');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [UserAuthController::class, 'logout'])->name('web.logout');
});

Route::get('/language/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['en', 'km'], true), 404);

    session(['locale' => $locale]);

    return Redirect::back();
})->name('language.switch');
