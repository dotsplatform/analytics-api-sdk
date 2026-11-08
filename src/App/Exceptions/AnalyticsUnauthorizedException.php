<?php

declare(strict_types=1);

/**
 * Description of AnalyticsUnauthorizedException.php
 *
 * 401: the X-Internal-Gateway-Token header is missing or wrong (config analytics-api.token).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Exceptions;

class AnalyticsUnauthorizedException extends AnalyticsApiException
{
}
