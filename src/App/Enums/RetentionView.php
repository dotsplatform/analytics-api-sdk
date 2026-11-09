<?php

declare(strict_types=1);

/**
 * Description of RetentionView.php
 *
 * The retention table to read (RetentionView in the analytics service). REPEAT_BUYERS: monthly cohorts of livesite
 * customers. DEVICES_RETURNING and DEVICES_ORDERING: weekly cohorts of devices that came back / ordered again.
 * REPEAT_BUYERS when none is sent.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Enums;

enum RetentionView: string
{
    case REPEAT_BUYERS = 'repeat_buyers';
    case DEVICES_RETURNING = 'devices_returning';
    case DEVICES_ORDERING = 'devices_ordering';
}
