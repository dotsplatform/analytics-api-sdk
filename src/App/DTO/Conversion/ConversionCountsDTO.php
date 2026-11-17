<?php

declare(strict_types=1);

/**
 * Description of ConversionCountsDTO.php
 *
 * The conversion counters of one channel (web, ios, android or all) for one period. Visits, checkout and purchase
 * sessions come from the analytics funnel; orders are real livesite orders of the channel (failed and declined
 * orders left out), so they do not depend on the apps sending purchase events. Rates are 0–1, null when their base
 * is 0.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Conversion;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class ConversionCountsDTO extends DTO
{
    protected int $visits;

    protected int $orders;

    protected ?float $conversionRate = null;

    protected int $checkoutSessions;

    protected int $purchaseSessions;

    protected ?float $checkoutToOrderRate = null;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'visits' => ResponseField::int($data, 'visits'),
            'orders' => ResponseField::int($data, 'orders'),
            'conversionRate' => ResponseField::nullableFloat($data, 'conversion_rate'),
            'checkoutSessions' => ResponseField::int($data, 'checkout_sessions'),
            'purchaseSessions' => ResponseField::int($data, 'purchase_sessions'),
            'checkoutToOrderRate' => ResponseField::nullableFloat($data, 'checkout_to_order_rate'),
        ]);
    }

    /**
     * Sessions (visits) of the channel.
     */
    public function getVisits(): int
    {
        return $this->visits;
    }

    /**
     * Real livesite orders of the channel; failed and declined orders are not counted.
     */
    public function getOrders(): int
    {
        return $this->orders;
    }

    /**
     * orders / visits, 0–1.
     */
    public function getConversionRate(): ?float
    {
        return $this->conversionRate;
    }

    /**
     * Sessions that opened the checkout.
     */
    public function getCheckoutSessions(): int
    {
        return $this->checkoutSessions;
    }

    /**
     * Sessions that ordered (purchase events).
     */
    public function getPurchaseSessions(): int
    {
        return $this->purchaseSessions;
    }

    /**
     * purchase sessions / checkout sessions, 0–1.
     */
    public function getCheckoutToOrderRate(): ?float
    {
        return $this->checkoutToOrderRate;
    }
}
