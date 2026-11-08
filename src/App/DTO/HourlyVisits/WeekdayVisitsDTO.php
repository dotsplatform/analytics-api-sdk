<?php

declare(strict_types=1);

/**
 * Description of WeekdayVisitsDTO.php
 *
 * Events and devices of one ISO weekday (1 = Monday … 7 = Sunday) summed over the period. All 7 weekdays are returned.
 * deviceHours sums hourly devices, so a device active in two hours of a day counts twice: use it to compare weekdays.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\HourlyVisits;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class WeekdayVisitsDTO extends DTO
{
    protected int $weekday;

    protected int $events;

    protected int $deviceHours;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'weekday' => ResponseField::int($data, 'weekday'),
            'events' => ResponseField::int($data, 'events'),
            'deviceHours' => ResponseField::int($data, 'device_hours'),
        ]);
    }

    /**
     * 1 = Monday … 7 = Sunday.
     */
    public function getWeekday(): int
    {
        return $this->weekday;
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
