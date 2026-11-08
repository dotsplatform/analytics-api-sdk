<?php

declare(strict_types=1);

/**
 * Description of CountersDTO.php
 *
 * The counters of analytics_daily_account_stats summed over a period. deviceDays sums daily unique devices, so a device
 * active on 3 days counts 3: compare it between periods, do not show it as a user count.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Common;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class CountersDTO extends DTO
{
    protected int $events;

    protected int $sessions;

    protected int $pageViews;

    protected int $itemViews;

    protected int $addToCart;

    protected int $checkouts;

    protected int $purchases;

    protected int $deviceDays;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'events' => ResponseField::int($data, 'events'),
            'sessions' => ResponseField::int($data, 'sessions'),
            'pageViews' => ResponseField::int($data, 'page_views'),
            'itemViews' => ResponseField::int($data, 'item_views'),
            'addToCart' => ResponseField::int($data, 'add_to_cart'),
            'checkouts' => ResponseField::int($data, 'checkouts'),
            'purchases' => ResponseField::int($data, 'purchases'),
            'deviceDays' => ResponseField::int($data, 'device_days'),
        ]);
    }

    public function getEvents(): int
    {
        return $this->events;
    }

    public function getSessions(): int
    {
        return $this->sessions;
    }

    public function getPageViews(): int
    {
        return $this->pageViews;
    }

    public function getItemViews(): int
    {
        return $this->itemViews;
    }

    public function getAddToCart(): int
    {
        return $this->addToCart;
    }

    public function getCheckouts(): int
    {
        return $this->checkouts;
    }

    public function getPurchases(): int
    {
        return $this->purchases;
    }

    /**
     * Daily unique devices summed over the days (a device active on 3 days counts 3).
     */
    public function getDeviceDays(): int
    {
        return $this->deviceDays;
    }
}
