<?php

declare(strict_types=1);

/**
 * Description of FunnelTrackingDTO.php
 *
 * The "tracking" object of the funnel: how many orders livesite received in the period, how many of them analytics
 * tracked and that coverage in percent. All three are null unless the channel is "all" (livesite cannot tell the
 * channel of an order); the percent is also null when livesite had no orders.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Funnel;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class FunnelTrackingDTO extends DTO
{
    protected ?int $livesiteOrders = null;

    protected ?int $matchedOrders = null;

    protected ?float $coveragePercent = null;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'livesiteOrders' => ResponseField::nullableInt($data, 'livesite_orders'),
            'matchedOrders' => ResponseField::nullableInt($data, 'matched_orders'),
            'coveragePercent' => ResponseField::nullableFloat($data, 'coverage_percent'),
        ]);
    }

    public function getLivesiteOrders(): ?int
    {
        return $this->livesiteOrders;
    }

    /**
     * Livesite orders analytics tracked.
     */
    public function getMatchedOrders(): ?int
    {
        return $this->matchedOrders;
    }

    public function getCoveragePercent(): ?float
    {
        return $this->coveragePercent;
    }
}
