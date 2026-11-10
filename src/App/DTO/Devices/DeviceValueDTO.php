<?php

declare(strict_types=1);

/**
 * Description of DeviceValueDTO.php
 *
 * One value of a devices dimension: the raw value (e.g. "iPhone17,2"), a readable label ("iPhone 16 Pro Max"), the
 * platforms it was seen on (comma-separated), device-days, sessions and its share in percent (app versions: of their
 * platform's devices; null when there were no devices).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Devices;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class DeviceValueDTO extends DTO
{
    protected string $value;

    protected string $label;

    protected string $platform;

    protected int $devices;

    protected int $sessions;

    protected ?float $sharePercent = null;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'value' => ResponseField::string($data, 'value'),
            'label' => ResponseField::string($data, 'label'),
            'platform' => ResponseField::string($data, 'platform'),
            'devices' => ResponseField::int($data, 'devices'),
            'sessions' => ResponseField::int($data, 'sessions'),
            'sharePercent' => ResponseField::nullableFloat($data, 'share_percent'),
        ]);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * "ios", "android", "web" or several joined by commas ("ios,web").
     */
    public function getPlatform(): string
    {
        return $this->platform;
    }

    /**
     * Device-days: a device counts once per day it was active.
     */
    public function getDevices(): int
    {
        return $this->devices;
    }

    public function getSessions(): int
    {
        return $this->sessions;
    }

    public function getSharePercent(): ?float
    {
        return $this->sharePercent;
    }
}
