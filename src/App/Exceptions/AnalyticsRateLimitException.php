<?php

declare(strict_types=1);

/**
 * Description of AnalyticsRateLimitException.php
 *
 * 429: the caller sent too many requests. Retry-After (seconds) is kept when the service or a gateway sends it.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Exceptions;

class AnalyticsRateLimitException extends AnalyticsApiException
{
    public function __construct(
        string $message,
        private readonly ?int $retryAfterSeconds,
        string $responseBody = '',
    ) {
        parent::__construct($message, 429, $responseBody);
    }

    public function getRetryAfterSeconds(): ?int
    {
        return $this->retryAfterSeconds;
    }
}
