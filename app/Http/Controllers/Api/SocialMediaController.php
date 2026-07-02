<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialMediaLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Throwable;

class SocialMediaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            if (! Schema::hasTable('social_media_links')) {
                return response()->json([
                    'data' => [],
                ]);
            }

            $validated = validator($request->all(), [
                'platform' => ['nullable', Rule::in(['web', 'app', 'all'])],
            ])->validate();

            $platform = $validated['platform'] ?? ($request->routeIs('api.app.*') ? 'app' : 'web');

            $links = SocialMediaLink::query()
                ->where('is_active', true)
                ->whereIn('platform', [$platform, 'all'])
                ->orderBy('sort_order')
                ->latest('id')
                ->get()
                ->map(function (SocialMediaLink $link) {
                    return [
                        'id' => $link->id,
                        'platform' => $link->platform,
                        'name' => $link->name,
                        'icon' => $link->icon,
                        'url' => $link->url,
                        'sort_order' => $link->sort_order,
                    ];
                })
                ->values();

            return response()->json([
                'data' => $links,
            ]);
        } catch (Throwable) {
            return response()->json([
                'data' => [],
            ]);
        }
    }
}
