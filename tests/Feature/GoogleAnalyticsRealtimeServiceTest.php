<?php

namespace Tests\Feature;

use App\Services\GoogleAnalyticsRealtimeService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleAnalyticsRealtimeServiceTest extends TestCase
{
    // Verify one GA4 timeout prevents repeated blocking requests during the cooldown.
    public function test_it_stops_repeated_requests_after_ga4_connection_failure(): void
    {
        config()->set('services.ga4.property_id', '123456');
        config()->set('services.ga4.service_account_json_path', 'storage/app/google/test.json');
        Cache::forget('ga4_temporarily_unavailable');
        Cache::put('ga4_service_account_access_token', 'test-token', now()->addMinutes(10));
        $requestCount = 0;

        Http::fake(function () use (&$requestCount) {
            $requestCount++;

            throw new ConnectionException('GA4 timed out.');
        });

        $service = app(GoogleAnalyticsRealtimeService::class);

        $this->assertNull($service->pageViewsByPath('/learning/test-course/test-lesson'));
        $this->assertNull($service->eventCountByPath('video_view', '/learning/test-course/test-lesson'));
        $this->assertSame(1, $requestCount);
    }
}
