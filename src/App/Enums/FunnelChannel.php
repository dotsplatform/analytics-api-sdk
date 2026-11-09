<?php

declare(strict_types=1);

/**
 * Description of FunnelChannel.php
 *
 * Which sessions the funnel counts (FunnelChannel in the analytics service). APPS is iOS and Android together.
 * The service uses ALL when none is sent; only ALL returns the livesite order tracking numbers.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Enums;

enum FunnelChannel: string
{
    case ALL = 'all';
    case WEB = 'web';
    case APPS = 'apps';
    case IOS = 'ios';
    case ANDROID = 'android';
}
