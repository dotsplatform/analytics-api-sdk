<?php

declare(strict_types=1);

/**
 * Description of OrderOutcomeDTO.php
 *
 * One row of the tracked orders split: an outcome (completed, cancelled, in_progress, not_in_livesite) or a cancel
 * reason (the livesite orders.declineReasonType as a string, "1".."13", or "none"), with an English label, the number
 * of orders and its percent of the tracked orders (of the cancelled orders for cancel reasons).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Funnel;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class OrderOutcomeDTO extends DTO
{
    protected string $key;

    protected string $label;

    protected int $orders;

    protected ?float $percentOfOrders = null;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'key' => ResponseField::string($data, 'key'),
            'label' => ResponseField::string($data, 'label'),
            'orders' => ResponseField::int($data, 'orders'),
            'percentOfOrders' => ResponseField::nullableFloat($data, 'percent_of_orders'),
        ]);
    }

    /**
     * A stable code to translate the row by: an outcome key, or a livesite decline reason type ("9") or "none".
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * The English name of the row.
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    public function getOrders(): int
    {
        return $this->orders;
    }

    /**
     * Null when there is nothing to divide by.
     */
    public function getPercentOfOrders(): ?float
    {
        return $this->percentOfOrders;
    }
}
