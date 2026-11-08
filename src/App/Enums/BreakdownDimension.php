<?php

declare(strict_types=1);

/**
 * Description of BreakdownDimension.php
 *
 * The column the platform breakdown groups by (PlatformBreakdownRequest::DIMENSIONS in the analytics service).
 * The service uses OS when none is sent.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Enums;

enum BreakdownDimension: string
{
    case PLATFORM = 'platform';
    case OS = 'os';
    case OS_VERSION = 'os_version';
    case BROWSER = 'browser';
    case BROWSER_VERSION = 'browser_version';
    case APP_VERSION = 'app_version';
}
