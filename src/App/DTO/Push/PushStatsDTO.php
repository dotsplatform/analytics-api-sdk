<?php

declare(strict_types=1);

/**
 * Description of PushStatsDTO.php
 *
 * GET push: push notifications of the period — the total, the total of the period before (null when it starts
 * before the data), per kind (campaign, order) and per day. The platform filter does not apply.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Push;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\DTO\Common\StatsFiltersDTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class PushStatsDTO extends DTO
{
    public const string KIND_CAMPAIGN = 'campaign';

    public const string KIND_ORDER = 'order';

    protected StatsFiltersDTO $filters;

    protected PushCountsDTO $total;

    protected ?PushCountsDTO $previousTotal = null;

    /** @var list<PushCountsDTO> */
    protected array $kinds = [];

    /** @var list<PushCountsDTO> */
    protected array $days = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        $push = ResponseField::object($data, 'push');

        $previousTotal = null;
        if (isset($push['previous_total']) && is_array($push['previous_total'])) {
            $previousTotal = PushCountsDTO::fromResponse($push['previous_total']);
        }

        $kinds = [];
        foreach (ResponseField::listOfObjects($push, 'kinds') as $kind) {
            $kinds[] = PushCountsDTO::fromResponse($kind, ResponseField::string($kind, 'kind'));
        }

        $days = [];
        foreach (ResponseField::listOfObjects($push, 'days') as $day) {
            $days[] = PushCountsDTO::fromResponse($day, ResponseField::string($day, 'date'));
        }

        return static::fromArray([
            'filters' => StatsFiltersDTO::fromResponse(ResponseField::object($data, 'filters')),
            'total' => PushCountsDTO::fromResponse(ResponseField::object($push, 'total')),
            'previousTotal' => $previousTotal,
            'kinds' => $kinds,
            'days' => $days,
        ]);
    }

    public function getFilters(): StatsFiltersDTO
    {
        return $this->filters;
    }

    public function getTotal(): PushCountsDTO
    {
        return $this->total;
    }

    public function getPreviousTotal(): ?PushCountsDTO
    {
        return $this->previousTotal;
    }

    /**
     * @return list<PushCountsDTO> campaign first, then order; a kind without pushes is left out
     */
    public function getKinds(): array
    {
        return $this->kinds;
    }

    /**
     * @return list<PushCountsDTO> every day of the period
     */
    public function getDays(): array
    {
        return $this->days;
    }

    public function isEmpty(): bool
    {
        if ($this->total->getSent() > 0) {
            return false;
        }

        return $this->total->getFailed() === 0;
    }
}
