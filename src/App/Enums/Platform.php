<?php

declare(strict_types=1);

/**
 * Description of Platform.php
 *
 * The platform filter of the Read API (StatsRequest::PLATFORMS in the analytics service).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Enums;

enum Platform: string
{
    case WEB = 'web';
    case IOS = 'ios';
    case ANDROID = 'android';
    case SERVER = 'server';
    case UNKNOWN = 'unknown';
}
