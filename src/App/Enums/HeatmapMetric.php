<?php

declare(strict_types=1);

/**
 * Description of HeatmapMetric.php
 *
 * The value of each heatmap cell (HeatmapMetric in the analytics service): SESSIONS and ORDERS are the average per
 * day of that weekday in the period, CONVERSION is the percent of sessions with an order. SESSIONS when none is sent.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Enums;

enum HeatmapMetric: string
{
    case SESSIONS = 'sessions';
    case ORDERS = 'orders';
    case CONVERSION = 'conversion';
}
