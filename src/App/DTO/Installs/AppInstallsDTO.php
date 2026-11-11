<?php

declare(strict_types=1);

/**
 * Description of AppInstallsDTO.php
 *
 * GET installs: app installs and updates of the period ("Application Installed" / "Application Updated" from the
 * apps), per platform and per day, with the totals of the period before (null when it starts before the data).
 * An install is counted on the first launch that reaches analytics; app removals are not included.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Installs;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\DTO\Common\StatsFiltersDTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class AppInstallsDTO extends DTO
{
    protected StatsFiltersDTO $filters;

    protected int $installs;

    protected int $updates;

    protected ?int $previousInstalls = null;

    protected ?int $previousUpdates = null;

    /** @var list<AppInstallCountsDTO> */
    protected array $platforms = [];

    /** @var list<AppInstallCountsDTO> */
    protected array $days = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        $installs = ResponseField::object($data, 'installs');

        $platforms = [];
        foreach (ResponseField::listOfObjects($installs, 'platforms') as $platform) {
            $platforms[] = AppInstallCountsDTO::fromResponse($platform, 'platform');
        }

        $days = [];
        foreach (ResponseField::listOfObjects($installs, 'days') as $day) {
            $days[] = AppInstallCountsDTO::fromResponse($day, 'date');
        }

        return static::fromArray([
            'filters' => StatsFiltersDTO::fromResponse(ResponseField::object($data, 'filters')),
            'installs' => ResponseField::int($installs, 'installs'),
            'updates' => ResponseField::int($installs, 'updates'),
            'previousInstalls' => ResponseField::nullableInt($installs, 'previous_installs'),
            'previousUpdates' => ResponseField::nullableInt($installs, 'previous_updates'),
            'platforms' => $platforms,
            'days' => $days,
        ]);
    }

    public function getFilters(): StatsFiltersDTO
    {
        return $this->filters;
    }

    public function getInstalls(): int
    {
        return $this->installs;
    }

    public function getUpdates(): int
    {
        return $this->updates;
    }

    public function getPreviousInstalls(): ?int
    {
        return $this->previousInstalls;
    }

    public function getPreviousUpdates(): ?int
    {
        return $this->previousUpdates;
    }

    /**
     * @return list<AppInstallCountsDTO> one per platform with installs or updates, by platform name
     */
    public function getPlatforms(): array
    {
        return $this->platforms;
    }

    /**
     * @return list<AppInstallCountsDTO> every day of the period, days without installs as zeros
     */
    public function getDays(): array
    {
        return $this->days;
    }

    public function isEmpty(): bool
    {
        if ($this->installs > 0) {
            return false;
        }

        return $this->updates === 0;
    }
}
