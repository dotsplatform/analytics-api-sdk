<?php

declare(strict_types=1);

/**
 * Description of ConversionPeriodDTO.php
 *
 * One period of GET conversion: a day ('2026-11-15') or a month ('2026-11') and the counters per channel.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Conversion;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class ConversionPeriodDTO extends DTO
{
    protected string $period;

    /** @var array<string, ConversionCountsDTO> */
    protected array $channels = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'period' => ResponseField::string($data, 'period'),
            'channels' => ConversionDTO::channelsFromResponse(ResponseField::object($data, 'channels')),
        ]);
    }

    /**
     * 'Y-m-d' for a day, 'Y-m' for a month.
     */
    public function getPeriod(): string
    {
        return $this->period;
    }

    /**
     * @return array<string, ConversionCountsDTO> channel (web, ios, android, all) => counters
     */
    public function getChannels(): array
    {
        return $this->channels;
    }

    /**
     * The counters of a channel; null when the response has no such channel.
     */
    public function getChannel(string $channel): ?ConversionCountsDTO
    {
        return $this->channels[$channel] ?? null;
    }
}
