<?php

use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\SocialMediaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('throttle:api-public')->group(function () {
    Route::get('/web/banners', [BannerController::class, 'index'])->name('api.web.banners.index');
    Route::get('/social-media', [SocialMediaController::class, 'index'])->name('api.social-media.index');
});
