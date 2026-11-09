<?php

declare(strict_types=1);

/**
 * Description of FunnelRevenueDTO.php
 *
 * The "revenue" object of the funnel, in cents of the account currency: the total of the tracked orders, of the
 * completed ones, and the average tracked order (null when there were no orders).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Funnel;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class FunnelRevenueDTO extends DTO
{
    protected int $trackedCents;

    protected int $completedCents;

    protected ?int $averageOrderCents = null;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'trackedCents' => ResponseField::int($data, 'tracked_cents'),
            'completedCents' => ResponseField::int($data, 'completed_cents'),
            'averageOrderCents' => ResponseField::nullableInt($data, 'average_order_cents'),
        ]);
    }

    public function getTrackedCents(): int
    {
        return $this->trackedCents;
    }

    public function getCompletedCents(): int
    {
        return $this->completedCents;
    }

    /**
     * Null when there were no tracked orders.
     */
    public function getAverageOrderCents(): ?int
    {
        return $this->averageOrderCents;
    }
}
