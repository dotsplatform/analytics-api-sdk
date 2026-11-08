<?php

declare(strict_types=1);

/**
 * Description of SuccessAnalyticsStubClient.php
 *
 * An AnalyticsClient for tests and local development of consumers: every call succeeds with sample data
 * (Stubs\StubResponses), no HTTP. Pick it with ANALYTICS_API_STUB=success, or bind it in a test:
 * $this->app->bind(AnalyticsClient::class, SuccessAnalyticsStubClient::class).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Clients;

use Dotsplatform\AnalyticsApi\AnalyticsClient;
use Dotsplatform\AnalyticsApi\Clients\Stubs\Success\SuccessStatsStubClient;
use Dotsplatform\AnalyticsApi\Contracts\StatsClient;

class SuccessAnalyticsStubClient implements AnalyticsClient
{
    public function stats(): StatsClient
    {
        return new SuccessStatsStubClient();
    }
}
