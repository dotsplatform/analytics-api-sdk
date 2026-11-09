<?php

declare(strict_types=1);

/**
 * Description of SourceKind.php
 *
 * The kind of a traffic source (SourceKind in the analytics service): the mobile app, paid ads, a UTM campaign,
 * a search engine, a social network, an AI assistant, another site, or direct traffic.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Enums;

enum SourceKind: string
{
    case APP = 'app';
    case PAID = 'paid';
    case CAMPAIGN = 'campaign';
    case SEARCH = 'search';
    case SOCIAL = 'social';
    case AI = 'ai';
    case REFERRAL = 'referral';
    case DIRECT = 'direct';
}
