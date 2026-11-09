<?php

declare(strict_types=1);

/**
 * Description of AnalyticsClient.php
 *
 * The analytics service SDK entry point. Resolve it from the container (AnalyticsApiServiceProvider binds the HTTP
 * client, or a stub when config analytics-api.stub says so; tests may bind SuccessAnalyticsStubClient or
 * FailAnalyticsStubClient) and pick a group:
 *
 *     $analyticsClient->stats()->getOverview(StatsRequestDTO::forAccount($account->token, '2026-11-01', '2026-11-07'));
 *
 * Every call may throw an AnalyticsApiException subclass (401, 404, 422, 429, server or connection errors).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi;

use Dotsplatform\AnalyticsApi\Contracts\StatsClient;

interface AnalyticsClient
{
    /**
     * The Read API: overview, active users, events, platforms, items, pages and hourly visits.
     */
    public function stats(): StatsClient;
}
