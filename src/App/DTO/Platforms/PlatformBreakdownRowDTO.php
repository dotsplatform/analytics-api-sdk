<?php

declare(strict_types=1);

/**
 * Description of PlatformBreakdownRowDTO.php
 *
 * One value of the breakdown dimension (an OS, an app version, ...) with its events and device-days:
 * {"value": "5.6.0", "events": 10, "device_days": 4}. The value is null for devices that did not report it.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Platforms;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class PlatformBreakdownRowDTO extends DTO
{
    protected ?string $value = null;

    protected int $events;

    protected int $deviceDays;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'value' => ResponseField::nullableString($data, 'value'),
            'events' => ResponseField::int($data, 'events'),
            'deviceDays' => ResponseField::int($data, 'device_days'),
        ]);
    }

    /**
     * The dimension value, e.g. "iOS" or "5.6.0"; null when devices did not report it.
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    public function getEvents(): int
    {
        return $this->events;
    }

    /**
     * Daily unique devices summed over the days.
     */
    public function getDeviceDays(): int
    {
        return $this->deviceDays;
    }
}
