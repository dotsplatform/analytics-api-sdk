<?php

declare(strict_types=1);

/**
 * Description of HourVisitsDTO.php
 *
 * Events and devices of one local hour (Kyiv time, 0–23) summed over the period. All 24 hours are returned (0 for
 * hours without data). deviceHours sums hourly unique devices: use it to compare hours, not as a user count.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\HourlyVisits;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class HourVisitsDTO extends DTO
{
    protected int $hour;

    protected int $events;

    protected int $deviceHours;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'hour' => ResponseField::int($data, 'hour'),
            'events' => ResponseField::int($data, 'events'),
            'deviceHours' => ResponseField::int($data, 'device_hours'),
        ]);
    }

    /**
     * 0–23, local time.
     */
    public function getHour(): int
    {
        return $this->hour;
    }

    public function getEvents(): int
    {
        return $this->events;
    }

    /**
     * Hourly unique devices summed over the days.
     */
    public function getDeviceHours(): int
    {
        return $this->deviceHours;
    }
}
