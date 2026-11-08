<?php

declare(strict_types=1);

/**
 * Description of AnalyticsHttpClient.php
 *
 * The real AnalyticsClient: every group shares one transport, so every call carries X-Internal-Gateway-Token.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Clients;

use Dotsplatform\AnalyticsApi\AnalyticsClient;
use Dotsplatform\AnalyticsApi\Clients\Http\StatsHttpClient;
use Dotsplatform\AnalyticsApi\Contracts\StatsClient;
use Dotsplatform\AnalyticsApi\Http\AnalyticsHttpTransport;

class AnalyticsHttpClient implements AnalyticsClient
{
    private readonly StatsClient $statsClient;

    public function __construct(AnalyticsHttpTransport $transport)
    {
        $this->statsClient = new StatsHttpClient($transport);
    }

    public function stats(): StatsClient
    {
        return $this->statsClient;
    }
}
