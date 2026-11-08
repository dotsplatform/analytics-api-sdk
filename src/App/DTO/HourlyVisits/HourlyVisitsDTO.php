<?php

declare(strict_types=1);

/**
 * Description of HourlyVisitsDTO.php
 *
 * GET hourly-visits: when users come, by local hour (Kyiv time) and by ISO weekday. "limit" does not apply.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\HourlyVisits;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\DTO\Common\StatsFiltersDTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class HourlyVisitsDTO extends DTO
{
    protected StatsFiltersDTO $filters;

    /** @var list<HourVisitsDTO> */
    protected array $hours = [];

    /** @var list<WeekdayVisitsDTO> */
    protected array $weekdays = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'filters' => StatsFiltersDTO::fromResponse(ResponseField::object($data, 'filters')),
            'hours' => self::readHours($data),
            'weekdays' => self::readWeekdays($data),
        ]);
    }

    public function getFilters(): StatsFiltersDTO
    {
        return $this->filters;
    }

    /**
     * Always 24 rows, hour 0 first (0 for hours without data).
     *
     * @return list<HourVisitsDTO>
     */
    public function getHours(): array
    {
        return $this->hours;
    }

    /**
     * Always 7 rows, Monday first.
     *
     * @return list<WeekdayVisitsDTO>
     */
    public function getWeekdays(): array
    {
        return $this->weekdays;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<HourVisitsDTO>
     */
    private static function readHours(array $data): array
    {
        $rows = [];
        foreach (ResponseField::listOfObjects($data, 'hours') as $rowData) {
            $rows[] = HourVisitsDTO::fromResponse($rowData);
        }

        return $rows;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<WeekdayVisitsDTO>
     */
    private static function readWeekdays(array $data): array
    {
        $rows = [];
        foreach (ResponseField::listOfObjects($data, 'weekdays') as $rowData) {
            $rows[] = WeekdayVisitsDTO::fromResponse($rowData);
        }

        return $rows;
    }
}
