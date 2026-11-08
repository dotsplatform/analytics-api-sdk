<?php

declare(strict_types=1);

/**
 * Description of AnalyticsHttpTimeouts.php
 *
 * Guzzle timeouts of the analytics client, in seconds (config analytics-api.connect_timeout / timeout). The Read API
 * answers from MySQL rollups, so a short timeout is enough.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Http;

final class AnalyticsHttpTimeouts
{
    public const float DEFAULT_CONNECT_SECONDS = 2.0;

    public const float DEFAULT_REQUEST_SECONDS = 8.0;

    public function __construct(
        private readonly float $connectSeconds = self::DEFAULT_CONNECT_SECONDS,
        private readonly float $requestSeconds = self::DEFAULT_REQUEST_SECONDS,
    ) {
    }

    /**
     * Time to open the TCP / TLS connection.
     */
    public function getConnectSeconds(): float
    {
        return $this->connectSeconds;
    }

    /**
     * Total time of a JSON request.
     */
    public function getRequestSeconds(): float
    {
        return $this->requestSeconds;
    }
}
