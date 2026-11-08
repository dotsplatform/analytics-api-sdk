<?php

declare(strict_types=1);

/**
 * Description of AccountOverviewDTO.php
 *
 * GET overview: the period totals, the same split by platform, and one row per day of the period.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Overview;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\DTO\Common\CountersDTO;
use Dotsplatform\AnalyticsApi\DTO\Common\StatsFiltersDTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class AccountOverviewDTO extends DTO
{
    protected StatsFiltersDTO $filters;

    protected CountersDTO $totals;

    /** @var list<PlatformTotalsDTO> */
    protected array $platforms = [];

    /** @var list<DailyTotalsDTO> */
    protected array $daily = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'filters' => StatsFiltersDTO::fromResponse(ResponseField::object($data, 'filters')),
            'totals' => CountersDTO::fromResponse(ResponseField::object($data, 'totals')),
            'platforms' => self::readPlatforms($data),
            'daily' => self::readDaily($data),
        ]);
    }

    public function getFilters(): StatsFiltersDTO
    {
        return $this->filters;
    }

    public function getTotals(): CountersDTO
    {
        return $this->totals;
    }

    /**
     * Ordered by platform name.
     *
     * @return list<PlatformTotalsDTO>
     */
    public function getPlatforms(): array
    {
        return $this->platforms;
    }

    /**
     * One row per day of the period, ordered by date (counters 0 for days without data).
     *
     * @return list<DailyTotalsDTO>
     */
    public function getDaily(): array
    {
        return $this->daily;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<PlatformTotalsDTO>
     */
    private static function readPlatforms(array $data): array
    {
        $platforms = [];
        foreach (ResponseField::listOfObjects($data, 'platforms') as $platformData) {
            $platforms[] = PlatformTotalsDTO::fromResponse($platformData);
        }

        return $platforms;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<DailyTotalsDTO>
     */
    private static function readDaily(array $data): array
    {
        $daily = [];
        foreach (ResponseField::listOfObjects($data, 'daily') as $dayData) {
            $daily[] = DailyTotalsDTO::fromResponse($dayData);
        }

        return $daily;
    }
}
