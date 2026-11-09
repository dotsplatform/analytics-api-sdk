<?php

declare(strict_types=1);

/**
 * Description of FunnelDTO.php
 *
 * GET funnel: the sessions funnel from a visit to an order, the checkout funnel, what happened to the tracked orders
 * in livesite, how many livesite orders analytics tracked, and the revenue. The steps come in funnel order;
 * "company_lists" is there only when the account sends it.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Funnel;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\DTO\Common\StatsFiltersDTO;
use Dotsplatform\AnalyticsApi\Enums\FunnelChannel;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class FunnelDTO extends DTO
{
    protected StatsFiltersDTO $filters;

    protected string $channel;

    /** @var list<FunnelStepDTO> */
    protected array $steps = [];

    /** @var list<FunnelStepDTO> */
    protected array $checkoutSteps = [];

    protected FunnelOrdersDTO $orders;

    protected FunnelTrackingDTO $tracking;

    protected FunnelRevenueDTO $revenue;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'filters' => StatsFiltersDTO::fromResponse(ResponseField::object($data, 'filters')),
            'channel' => ResponseField::string($data, 'channel'),
            'steps' => self::readSteps($data, 'steps'),
            'checkoutSteps' => self::readSteps($data, 'checkout_steps'),
            'orders' => FunnelOrdersDTO::fromResponse(ResponseField::object($data, 'orders')),
            'tracking' => FunnelTrackingDTO::fromResponse(ResponseField::object($data, 'tracking')),
            'revenue' => FunnelRevenueDTO::fromResponse(ResponseField::object($data, 'revenue')),
        ]);
    }

    public function getFilters(): StatsFiltersDTO
    {
        return $this->filters;
    }

    /**
     * The raw channel the service counted ("all" when the request sent none).
     */
    public function getChannelValue(): string
    {
        return $this->channel;
    }

    /**
     * The channel, or null for one this SDK version does not know.
     */
    public function getChannel(): ?FunnelChannel
    {
        return FunnelChannel::tryFrom($this->channel);
    }

    /**
     * From visits to orders, in funnel order.
     *
     * @return list<FunnelStepDTO>
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    /**
     * From an opened checkout to an order; percent of start = percent of checkouts.
     *
     * @return list<FunnelStepDTO>
     */
    public function getCheckoutSteps(): array
    {
        return $this->checkoutSteps;
    }

    public function getOrders(): FunnelOrdersDTO
    {
        return $this->orders;
    }

    /**
     * Its numbers are null unless the channel is "all".
     */
    public function getTracking(): FunnelTrackingDTO
    {
        return $this->tracking;
    }

    public function getRevenue(): FunnelRevenueDTO
    {
        return $this->revenue;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<FunnelStepDTO>
     */
    private static function readSteps(array $data, string $key): array
    {
        $steps = [];
        foreach (ResponseField::listOfObjects($data, $key) as $stepData) {
            $steps[] = FunnelStepDTO::fromResponse($stepData);
        }

        return $steps;
    }
}
