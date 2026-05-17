<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Throwable;

class BannerController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            if (!Schema::hasTable('banners')) {
                return response()->json([
                    'data' => [],
                ]);
            }

            // Admin banner schedule is currently entered in Cambodia local time.
            // Compare in the same timezone so active slides are not filtered out unexpectedly.
            $now = now('Asia/Phnom_Penh');

            $banners = Banner::query()
                ->with('course')
                ->where('platform', 'web')
                ->where('is_active', true)
                ->where(function ($query) use ($now) {
                    $query->whereNull('starts_at')
                        ->orWhere('starts_at', '<=', $now);
                })
                ->where(function ($query) use ($now) {
                    $query->whereNull('ends_at')
                        ->orWhere('ends_at', '>=', $now);
                })
                ->orderBy('sort_order')
                ->latest('id')
                ->get()
                ->map(function (Banner $banner) {
                    return [
                        'id' => $banner->id,
                        'title' => $banner->title,
                        'subtitle' => $banner->subtitle,
                        'image_url' => $banner->image_url,
                        'button_text' => $banner->button_text ?: 'Open Course',
                        'target_course_id' => $banner->target_course_id,
                        'target_course_title' => $banner->course?->title,
                        'target_url' => $banner->target_course_id ? route('courses.show', $banner->course?->slug ?: $banner->target_course_id) : null,
                    ];
                })
                ->values();

            return response()->json([
                'data' => $banners,
            ]);
        } catch (Throwable) {
            return response()->json([
                'data' => [],
            ]);
        }
    }
}
