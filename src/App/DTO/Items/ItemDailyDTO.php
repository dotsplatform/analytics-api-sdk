<?php

declare(strict_types=1);

/**
 * Description of ItemDailyDTO.php
 *
 * GET items/{item}/daily: one dish, one row per day of the period, newest first. Not aggregated.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Items;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\DTO\Common\StatsFiltersDTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class ItemDailyDTO extends DTO
{
    protected StatsFiltersDTO $filters;

    protected string $itemId;

    protected ?string $name = null;

    protected ?string $category = null;

    /** @var list<ItemDayDTO> */
    protected array $days = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        $item = ResponseField::object($data, 'item');

        $days = [];
        foreach (ResponseField::listOfObjects($data, 'days') as $day) {
            $days[] = ItemDayDTO::fromResponse($day);
        }

        return static::fromArray([
            'filters' => StatsFiltersDTO::fromResponse(ResponseField::object($data, 'filters')),
            'itemId' => ResponseField::string($item, 'item_id'),
            'name' => ResponseField::nullableString($item, 'name'),
            'category' => ResponseField::nullableString($item, 'category'),
            'days' => $days,
        ]);
    }

    public function getFilters(): StatsFiltersDTO
    {
        return $this->filters;
    }

    public function getItemId(): string
    {
        return $this->itemId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * Newest first.
     *
     * @return list<ItemDayDTO>
     */
    public function getDays(): array
    {
        return $this->days;
    }
}
