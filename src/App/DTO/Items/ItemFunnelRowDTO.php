<?php

declare(strict_types=1);

/**
 * Description of ItemFunnelRowDTO.php
 *
 * One dish of GET items/funnel: its token, name and category (null when the catalog does not know it yet), the
 * counters, and whether enough sessions opened it for the rates to mean something.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Items;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class ItemFunnelRowDTO extends DTO
{
    protected string $itemId;

    protected ?string $name = null;

    protected ?string $category = null;

    protected ItemFunnelCountsDTO $counts;

    protected bool $ratesReliable;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'itemId' => ResponseField::string($data, 'item_id'),
            'name' => ResponseField::nullableString($data, 'name'),
            'category' => ResponseField::nullableString($data, 'category'),
            'counts' => ItemFunnelCountsDTO::fromResponse($data),
            'ratesReliable' => ResponseField::bool($data, 'rates_reliable'),
        ]);
    }

    /**
     * The livesite dish token.
     */
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

    public function getCounts(): ItemFunnelCountsDTO
    {
        return $this->counts;
    }

    /**
     * False when fewer sessions than ItemFunnelDTO::getMinViewSessionsForRates() opened the dish.
     */
    public function areRatesReliable(): bool
    {
        return $this->ratesReliable;
    }
}
