<?php

declare(strict_types=1);

/**
 * Description of EventStatsDTO.php
 *
 * GET events: the most frequent events of the period, most first, at most "limit" rows.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Events;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\DTO\Common\StatsFiltersDTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class EventStatsDTO extends DTO
{
    protected StatsFiltersDTO $filters;

    /** @var list<EventStatDTO> */
    protected array $events = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'filters' => StatsFiltersDTO::fromResponse(ResponseField::object($data, 'filters')),
            'events' => self::readEvents($data),
        ]);
    }

    public function getFilters(): StatsFiltersDTO
    {
        return $this->filters;
    }

    /**
     * Ordered by events, most first.
     *
     * @return list<EventStatDTO>
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<EventStatDTO>
     */
    private static function readEvents(array $data): array
    {
        $rows = [];
        foreach (ResponseField::listOfObjects($data, 'events') as $rowData) {
            $rows[] = EventStatDTO::fromResponse($rowData);
        }

        return $rows;
    }
}
