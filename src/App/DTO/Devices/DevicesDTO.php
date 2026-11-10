<?php

declare(strict_types=1);

/**
 * Description of DevicesDTO.php
 *
 * GET devices: the top 10 device models, OS versions ("iOS 18"), browsers ("Chrome 141") and app versions of the
 * period, most devices first.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Devices;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\DTO\Common\StatsFiltersDTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class DevicesDTO extends DTO
{
    protected StatsFiltersDTO $filters;

    /** @var list<DeviceValueDTO> */
    protected array $deviceModels = [];

    /** @var list<DeviceValueDTO> */
    protected array $osVersions = [];

    /** @var list<DeviceValueDTO> */
    protected array $browsers = [];

    /** @var list<DeviceValueDTO> */
    protected array $appVersions = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        $dimensions = ResponseField::object($data, 'dimensions');

        return static::fromArray([
            'filters' => StatsFiltersDTO::fromResponse(ResponseField::object($data, 'filters')),
            'deviceModels' => self::readValues($dimensions, 'device_model'),
            'osVersions' => self::readValues($dimensions, 'os_version'),
            'browsers' => self::readValues($dimensions, 'browser'),
            'appVersions' => self::readValues($dimensions, 'app_version'),
        ]);
    }

    public function getFilters(): StatsFiltersDTO
    {
        return $this->filters;
    }

    /**
     * @return list<DeviceValueDTO>
     */
    public function getDeviceModels(): array
    {
        return $this->deviceModels;
    }

    /**
     * @return list<DeviceValueDTO>
     */
    public function getOsVersions(): array
    {
        return $this->osVersions;
    }

    /**
     * @return list<DeviceValueDTO>
     */
    public function getBrowsers(): array
    {
        return $this->browsers;
    }

    /**
     * @return list<DeviceValueDTO>
     */
    public function getAppVersions(): array
    {
        return $this->appVersions;
    }

    public function isEmpty(): bool
    {
        return $this->deviceModels === [] && $this->osVersions === [] && $this->browsers === [] && $this->appVersions === [];
    }

    /**
     * @param  array<array-key, mixed>  $dimensions
     * @return list<DeviceValueDTO>
     */
    private static function readValues(array $dimensions, string $dimension): array
    {
        $values = [];
        foreach (ResponseField::listOfObjects($dimensions, $dimension) as $valueData) {
            $values[] = DeviceValueDTO::fromResponse($valueData);
        }

        return $values;
    }
}
