<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notificationService)
    {
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $count = $this->notificationService->markAllVisibleAsRead($request->user(), 'web');

        return response()->json([
            'status' => 'ok',
            'marked' => $count,
        ]);
    }
}
