<?php

declare(strict_types=1);

/**
 * Description of EventStatDTO.php
 *
 * One event name of the period with how often it happened and the device-days that sent it.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Events;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class EventStatDTO extends DTO
{
    protected string $event;

    protected int $events;

    protected int $deviceDays;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'event' => ResponseField::string($data, 'event'),
            'events' => ResponseField::int($data, 'events'),
            'deviceDays' => ResponseField::int($data, 'device_days'),
        ]);
    }

    /**
     * The event name, e.g. "$pageview" or "add_to_cart".
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    public function getEvents(): int
    {
        return $this->events;
    }

    public function getDeviceDays(): int
    {
        return $this->deviceDays;
    }
}
