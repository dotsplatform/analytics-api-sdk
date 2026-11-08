<?php

declare(strict_types=1);

/*
 * Published as config/analytics-api.php in the consumer. The token is sent as X-Internal-Gateway-Token; the analytics
 * service answers 401 without it.
 */

return [
    // Base URL of the analytics service, e.g. https://e.dots.live (the SDK adds "api/v1/...").
    'host' => env('ANALYTICS_API_URL', ''),
    'token' => env('ANALYTICS_API_GATEWAY_TOKEN', ''),
    // "" = the real HTTP client, "success" = SuccessAnalyticsStubClient (sample data), "fail" = FailAnalyticsStubClient.
    'stub' => env('ANALYTICS_API_STUB', ''),
    // Seconds.
    'connect_timeout' => 2,
    'timeout' => 8,
];
