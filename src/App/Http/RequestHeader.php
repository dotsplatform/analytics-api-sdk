<?php

declare(strict_types=1);

/**
 * Description of RequestHeader.php
 *
 * Headers the analytics service reads from internal callers (VerifyInternalGatewayToken in the analytics service).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Http;

final class RequestHeader
{
    public const string INTERNAL_GATEWAY_TOKEN = 'X-Internal-Gateway-Token';
}
