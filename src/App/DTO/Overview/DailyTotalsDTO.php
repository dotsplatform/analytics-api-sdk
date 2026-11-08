<?php

declare(strict_types=1);

/**
 * Description of DailyTotalsDTO.php
 *
 * One day of the overview: the date and its counters (all 0 for a day without data).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Overview;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\DTO\Common\CountersDTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class DailyTotalsDTO extends DTO
{
    protected string $date;

    protected CountersDTO $counters;

    /**
     * The row is flat: {"date": "2026-11-02", "events": 80, ...}.
     *
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'date' => ResponseField::string($data, 'date'),
            'counters' => CountersDTO::fromResponse($data),
        ]);
    }

    /**
     * Y-m-d.
     */
    public function getDate(): string
    {
        return $this->date;
    }

    public function getCounters(): CountersDTO
    {
        return $this->counters;
    }
}
