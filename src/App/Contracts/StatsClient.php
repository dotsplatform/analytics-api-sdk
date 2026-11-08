<?php

declare(strict_types=1);

/**
 * Description of StatsClient.php
 *
 * The Read API of the analytics service: GET api/v1/accounts/{account}/... One method per endpoint. It reads daily
 * rollups, so today's numbers grow during the day and the latest events may not be counted yet.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Contracts;

use Dotsplatform\AnalyticsApi\DTO\ActiveUsers\ActiveUsersDTO;
use Dotsplatform\AnalyticsApi\DTO\Events\EventStatsDTO;
use Dotsplatform\AnalyticsApi\DTO\HourlyVisits\HourlyVisitsDTO;
use Dotsplatform\AnalyticsApi\DTO\Items\TopItemsDTO;
use Dotsplatform\AnalyticsApi\DTO\Overview\AccountOverviewDTO;
use Dotsplatform\AnalyticsApi\DTO\Pages\TopPagesDTO;
use Dotsplatform\AnalyticsApi\DTO\Platforms\PlatformBreakdownDTO;
use Dotsplatform\AnalyticsApi\DTO\Requests\StatsRequestDTO;

interface StatsClient
{
    /**
     * GET overview: totals of the period, the same split by platform, and one row per day.
     */
    public function getOverview(StatsRequestDTO $request): AccountOverviewDTO;

    /**
     * GET active-users: DAU / WAU / MAU per day.
     */
    public function getActiveUsers(StatsRequestDTO $request): ActiveUsersDTO;

    /**
     * GET events: the most frequent events (limit applies).
     */
    public function getEventStats(StatsRequestDTO $request): EventStatsDTO;

    /**
     * GET platforms: devices and events by the request dimension (limit applies; dimension "os" when not set).
     */
    public function getPlatformBreakdown(StatsRequestDTO $request): PlatformBreakdownDTO;

    /**
     * GET items: the most viewed items with the add-to-cart rate (limit applies).
     */
    public function getTopItems(StatsRequestDTO $request): TopItemsDTO;

    /**
     * GET pages: the most viewed pages and screens (limit applies).
     */
    public function getTopPages(StatsRequestDTO $request): TopPagesDTO;

    /**
     * GET hourly-visits: totals by local hour and by weekday.
     */
    public function getHourlyVisits(StatsRequestDTO $request): HourlyVisitsDTO;
}
