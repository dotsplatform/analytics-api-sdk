<?php

declare(strict_types=1);

/**
 * Description of PushCountsDTO.php
 *
 * Push notifications of one kind (campaign / order), of all kinds, or of one day. "Sent" = accepted by Apple /
 * Google; "delivered" and "opened" are reported by the apps. Rates are of the sent pushes, null when none was sent.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Push;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class PushCountsDTO extends DTO
{
    protected string $key = '';

    protected int $sent;

    protected int $failed;

    protected int $delivered;

    protected int $opened;

    protected ?float $openRatePercent = null;

    protected ?float $deliveryRatePercent = null;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data, string $key = ''): static
    {
        return static::fromArray([
            'key' => $key,
            'sent' => ResponseField::int($data, 'sent'),
            'failed' => ResponseField::int($data, 'failed'),
            'delivered' => ResponseField::int($data, 'delivered'),
            'opened' => ResponseField::int($data, 'opened'),
            'openRatePercent' => ResponseField::nullableFloat($data, 'open_rate_percent'),
            'deliveryRatePercent' => ResponseField::nullableFloat($data, 'delivery_rate_percent'),
        ]);
    }

    /**
     * "campaign" / "order" in the kinds list, Y-m-d in the daily list, "" for the totals.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    public function getSent(): int
    {
        return $this->sent;
    }

    public function getFailed(): int
    {
        return $this->failed;
    }

    public function getDelivered(): int
    {
        return $this->delivered;
    }

    public function getOpened(): int
    {
        return $this->opened;
    }

    public function getOpenRatePercent(): ?float
    {
        return $this->openRatePercent;
    }

    public function getDeliveryRatePercent(): ?float
    {
        return $this->deliveryRatePercent;
    }
}
