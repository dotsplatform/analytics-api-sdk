<?php

declare(strict_types=1);

/**
 * Description of FailAnalyticsStubClient.php
 *
 * An AnalyticsClient for tests of consumers: every call throws AnalyticsServerException (503), as when the analytics
 * service is down. Pick it with ANALYTICS_API_STUB=fail, or bind it in a test:
 * $this->app->bind(AnalyticsClient::class, FailAnalyticsStubClient::class).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Clients;

use Dotsplatform\AnalyticsApi\AnalyticsClient;
use Dotsplatform\AnalyticsApi\Clients\Stubs\Fail\FailStatsStubClient;
use Dotsplatform\AnalyticsApi\Contracts\StatsClient;

class FailAnalyticsStubClient implements AnalyticsClient
{
    public function stats(): StatsClient
    {
        return new FailStatsStubClient();
    }
}
