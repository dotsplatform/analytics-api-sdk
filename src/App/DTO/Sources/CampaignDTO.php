<?php

declare(strict_types=1);

/**
 * Description of CampaignDTO.php
 *
 * One UTM campaign of the period: its name, the source it came from (the service sends it as "kind"), sessions,
 * sessions with an order and that conversion in percent (null when there were no sessions).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Sources;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class CampaignDTO extends DTO
{
    protected string $name;

    protected string $sourceName;

    protected int $sessions;

    protected int $orderingSessions;

    protected ?float $conversionPercent = null;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'name' => ResponseField::string($data, 'name'),
            'sourceName' => ResponseField::string($data, 'kind'),
            'sessions' => ResponseField::int($data, 'sessions'),
            'orderingSessions' => ResponseField::int($data, 'ordering_sessions'),
            'conversionPercent' => ResponseField::nullableFloat($data, 'conversion_percent'),
        ]);
    }

    /**
     * The utm_campaign value.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * The name of the source the campaign came from, e.g. "Instagram" (the "kind" field of the response).
     */
    public function getSourceName(): string
    {
        return $this->sourceName;
    }

    public function getSessions(): int
    {
        return $this->sessions;
    }

    /**
     * Sessions of this campaign that ended with an order.
     */
    public function getOrderingSessions(): int
    {
        return $this->orderingSessions;
    }

    public function getConversionPercent(): ?float
    {
        return $this->conversionPercent;
    }
}
