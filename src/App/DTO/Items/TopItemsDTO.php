<?php

declare(strict_types=1);

/**
 * Description of TopItemsDTO.php
 *
 * GET items: the most viewed items of the period with their add-to-cart rate, most viewed first, at most "limit" rows.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Items;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\DTO\Common\StatsFiltersDTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class TopItemsDTO extends DTO
{
    protected StatsFiltersDTO $filters;

    /** @var list<TopItemDTO> */
    protected array $items = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'filters' => StatsFiltersDTO::fromResponse(ResponseField::object($data, 'filters')),
            'items' => self::readItems($data),
        ]);
    }

    public function getFilters(): StatsFiltersDTO
    {
        return $this->filters;
    }

    /**
     * Ordered by views, most first.
     *
     * @return list<TopItemDTO>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<TopItemDTO>
     */
    private static function readItems(array $data): array
    {
        $rows = [];
        foreach (ResponseField::listOfObjects($data, 'items') as $rowData) {
            $rows[] = TopItemDTO::fromResponse($rowData);
        }

        return $rows;
    }
}
