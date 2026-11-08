<?php

declare(strict_types=1);

/**
 * Description of PlatformTotalsDTO.php
 *
 * One platform of the overview: the platform and its counters summed over the period.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Overview;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\DTO\Common\CountersDTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class PlatformTotalsDTO extends DTO
{
    protected string $platform;

    protected CountersDTO $counters;

    /**
     * The row is flat: {"platform": "web", "events": 100, ...}.
     *
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'platform' => ResponseField::string($data, 'platform'),
            'counters' => CountersDTO::fromResponse($data),
        ]);
    }

    /**
     * The raw platform string (web, ios, android, server, unknown, or a newer one).
     */
    public function getPlatform(): string
    {
        return $this->platform;
    }

    public function getCounters(): CountersDTO
    {
        return $this->counters;
    }
}
