<?php

declare(strict_types=1);

/**
 * Description of ItemDayDTO.php
 *
 * One day of GET items/{item}/daily: the dish counters of that Kyiv day (zeros when nothing happened).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Items;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class ItemDayDTO extends DTO
{
    protected string $date;

    protected ItemFunnelCountsDTO $counts;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'date' => ResponseField::string($data, 'date'),
            'counts' => ItemFunnelCountsDTO::fromResponse($data),
        ]);
    }

    /**
     * Y-m-d.
     */
    public function getDate(): string
    {
        return $this->date;
    }

    public function getCounts(): ItemFunnelCountsDTO
    {
        return $this->counts;
    }
}
