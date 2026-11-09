<?php

declare(strict_types=1);

/**
 * Description of HeatmapWeekdayDTO.php
 *
 * One row of the heatmap: an ISO weekday (1 = Monday … 7 = Sunday) and 24 values, one per Kyiv hour 0–23, of the
 * heatmap metric, rounded to 2 decimals.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Heatmap;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class HeatmapWeekdayDTO extends DTO
{
    protected int $weekday;

    /** @var list<float> */
    protected array $hours = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'weekday' => ResponseField::int($data, 'weekday'),
            'hours' => ResponseField::listOfFloats($data, 'hours'),
        ]);
    }

    /**
     * 1 = Monday … 7 = Sunday.
     */
    public function getWeekday(): int
    {
        return $this->weekday;
    }

    /**
     * 24 values; the index is the Kyiv hour.
     *
     * @return list<float>
     */
    public function getHours(): array
    {
        return $this->hours;
    }
}
