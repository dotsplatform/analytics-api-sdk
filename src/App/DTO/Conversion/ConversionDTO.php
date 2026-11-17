<?php

declare(strict_types=1);

/**
 * Description of ConversionDTO.php
 *
 * GET conversion: per channel (web, ios, android and all = their sum) visits, real livesite orders, orders / visits
 * and checkout → order, one row per day or month (oldest first), the totals of the period and of the period of the
 * same length before it, and rule-based hints.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Conversion;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\DTO\Common\StatsFiltersDTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class ConversionDTO extends DTO
{
    public const string GROUP_DAY = 'day';

    public const string GROUP_MONTH = 'month';

    public const string CHANNEL_WEB = 'web';

    public const string CHANNEL_IOS = 'ios';

    public const string CHANNEL_ANDROID = 'android';

    public const string CHANNEL_ALL = 'all';

    protected StatsFiltersDTO $filters;

    protected string $group;

    /** @var list<string> */
    protected array $channels = [];

    /** @var list<ConversionPeriodDTO> */
    protected array $periods = [];

    /** @var array<string, ConversionCountsDTO> */
    protected array $totals = [];

    /** @var array<string, ConversionCountsDTO> */
    protected array $previousTotals = [];

    /** @var list<ConversionHintDTO> */
    protected array $hints = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        $periods = [];
        foreach (ResponseField::listOfObjects($data, 'periods') as $period) {
            $periods[] = ConversionPeriodDTO::fromResponse($period);
        }

        $hints = [];
        foreach (ResponseField::listOfObjects($data, 'hints') as $hint) {
            $hints[] = ConversionHintDTO::fromResponse($hint);
        }

        return static::fromArray([
            'filters' => StatsFiltersDTO::fromResponse(ResponseField::object($data, 'filters')),
            'group' => ResponseField::string($data, 'group'),
            'channels' => ResponseField::listOfStrings($data, 'channels'),
            'periods' => $periods,
            'totals' => self::channelsFromResponse(ResponseField::object($data, 'totals')),
            'previousTotals' => self::channelsFromResponse(ResponseField::object($data, 'previous_totals')),
            'hints' => $hints,
        ]);
    }

    /**
     * A channel map of the response (channel => counters) as DTOs.
     *
     * @param  array<array-key, mixed>  $responseChannels
     * @return array<string, ConversionCountsDTO>
     */
    public static function channelsFromResponse(array $responseChannels): array
    {
        $channels = [];
        foreach (array_keys($responseChannels) as $channel) {
            $channelName = (string) $channel;
            $channels[$channelName] = ConversionCountsDTO::fromResponse(ResponseField::object($responseChannels, $channelName));
        }

        return $channels;
    }

    public function getFilters(): StatsFiltersDTO
    {
        return $this->filters;
    }

    /**
     * 'day' or 'month'.
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @return list<string> web, ios, android, all
     */
    public function getChannels(): array
    {
        return $this->channels;
    }

    /**
     * @return list<ConversionPeriodDTO> oldest first
     */
    public function getPeriods(): array
    {
        return $this->periods;
    }

    /**
     * @return array<string, ConversionCountsDTO> channel => counters of the period
     */
    public function getTotals(): array
    {
        return $this->totals;
    }

    public function getTotal(string $channel): ?ConversionCountsDTO
    {
        return $this->totals[$channel] ?? null;
    }

    /**
     * @return array<string, ConversionCountsDTO> channel => counters of the period of the same length before
     */
    public function getPreviousTotals(): array
    {
        return $this->previousTotals;
    }

    public function getPreviousTotal(string $channel): ?ConversionCountsDTO
    {
        return $this->previousTotals[$channel] ?? null;
    }

    /**
     * @return list<ConversionHintDTO>
     */
    public function getHints(): array
    {
        return $this->hints;
    }

    public function isEmpty(): bool
    {
        return $this->periods === [];
    }
}
