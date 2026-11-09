<?php

declare(strict_types=1);

/**
 * Description of HeatmapDTO.php
 *
 * GET heatmap: weekday × hour activity of the period. For sessions and orders a cell is the average per day of that
 * weekday; for conversion it is the percent of sessions with an order. Always 7 rows of 24 values. maxValue is the
 * largest cell, to scale the colours.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Heatmap;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\DTO\Common\StatsFiltersDTO;
use Dotsplatform\AnalyticsApi\Enums\HeatmapMetric;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class HeatmapDTO extends DTO
{
    protected StatsFiltersDTO $filters;

    protected string $metric;

    protected float $maxValue;

    /** @var list<HeatmapWeekdayDTO> */
    protected array $weekdays = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'filters' => StatsFiltersDTO::fromResponse(ResponseField::object($data, 'filters')),
            'metric' => ResponseField::string($data, 'metric'),
            'maxValue' => ResponseField::float($data, 'max_value'),
            'weekdays' => self::readWeekdays($data),
        ]);
    }

    public function getFilters(): StatsFiltersDTO
    {
        return $this->filters;
    }

    /**
     * The raw metric of the cells ("sessions" when the request sent none).
     */
    public function getMetricValue(): string
    {
        return $this->metric;
    }

    /**
     * The metric, or null for one this SDK version does not know.
     */
    public function getMetric(): ?HeatmapMetric
    {
        return HeatmapMetric::tryFrom($this->metric);
    }

    /**
     * The largest cell value (0.0 when there was no activity).
     */
    public function getMaxValue(): float
    {
        return $this->maxValue;
    }

    /**
     * Monday first.
     *
     * @return list<HeatmapWeekdayDTO>
     */
    public function getWeekdays(): array
    {
        return $this->weekdays;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<HeatmapWeekdayDTO>
     */
    private static function readWeekdays(array $data): array
    {
        $weekdays = [];
        foreach (ResponseField::listOfObjects($data, 'weekdays') as $weekdayData) {
            $weekdays[] = HeatmapWeekdayDTO::fromResponse($weekdayData);
        }

        return $weekdays;
    }
}
