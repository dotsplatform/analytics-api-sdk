<?php

declare(strict_types=1);

/**
 * Description of ItemFunnelDTO.php
 *
 * GET items/funnel: per dish for the period — opened, added to cart, ordered — to find dishes that are opened often
 * but rarely added or ordered. The most viewed dishes (up to the request limit) plus the most ordered ones, sorted by
 * view sessions, then orders.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Items;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\DTO\Common\StatsFiltersDTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class ItemFunnelDTO extends DTO
{
    protected StatsFiltersDTO $filters;

    protected int $minViewSessionsForRates;

    /** @var list<ItemFunnelRowDTO> */
    protected array $items = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        $items = [];
        foreach (ResponseField::listOfObjects($data, 'items') as $item) {
            $items[] = ItemFunnelRowDTO::fromResponse($item);
        }

        return static::fromArray([
            'filters' => StatsFiltersDTO::fromResponse(ResponseField::object($data, 'filters')),
            'minViewSessionsForRates' => ResponseField::int($data, 'min_view_sessions_for_rates'),
            'items' => $items,
        ]);
    }

    public function getFilters(): StatsFiltersDTO
    {
        return $this->filters;
    }

    public function getMinViewSessionsForRates(): int
    {
        return $this->minViewSessionsForRates;
    }

    /**
     * @return list<ItemFunnelRowDTO>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }
}
