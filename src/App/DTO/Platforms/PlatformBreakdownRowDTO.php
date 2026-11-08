<?php

declare(strict_types=1);

/**
 * Description of PlatformBreakdownRowDTO.php
 *
 * One value of the breakdown dimension (an OS, an app version, ...) with its events and device-days. The service
 * names the value field after the dimension ({"app_version": "5.6.0", ...}); this DTO calls it "value". A missing value
 * (a device that did not report it) comes as an empty string.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Platforms;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class PlatformBreakdownRowDTO extends DTO
{
    protected string $value;

    protected int $events;

    protected int $deviceDays;

    /**
     * @param  array<array-key, mixed>  $data
     * @param  string  $dimension  the response "dimension": the key that holds the value
     */
    public static function fromResponse(array $data, string $dimension): static
    {
        return static::fromArray([
            'value' => ResponseField::string($data, $dimension),
            'events' => ResponseField::int($data, 'events'),
            'deviceDays' => ResponseField::int($data, 'device_days'),
        ]);
    }

    /**
     * The dimension value, e.g. "iOS" or "5.6.0"; "" when devices did not report it.
     */
    public function getValue(): string
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
