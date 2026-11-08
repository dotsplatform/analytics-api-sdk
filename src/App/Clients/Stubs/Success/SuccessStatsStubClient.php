<?php

declare(strict_types=1);

/**
 * Description of SuccessStatsStubClient.php
 *
 * Answers every stats call with StubResponses data, no HTTP. The filters echo the request (and the stub period when
 * the request has no dates).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Clients\Stubs\Success;

use Dotsplatform\AnalyticsApi\Clients\Stubs\StubResponses;
use Dotsplatform\AnalyticsApi\Contracts\StatsClient;
use Dotsplatform\AnalyticsApi\DTO\ActiveUsers\ActiveUsersDTO;
use Dotsplatform\AnalyticsApi\DTO\Events\EventStatsDTO;
use Dotsplatform\AnalyticsApi\DTO\HourlyVisits\HourlyVisitsDTO;
use Dotsplatform\AnalyticsApi\DTO\Items\TopItemsDTO;
use Dotsplatform\AnalyticsApi\DTO\Overview\AccountOverviewDTO;
use Dotsplatform\AnalyticsApi\DTO\Pages\TopPagesDTO;
use Dotsplatform\AnalyticsApi\DTO\Platforms\PlatformBreakdownDTO;
use Dotsplatform\AnalyticsApi\DTO\Requests\StatsRequestDTO;

class SuccessStatsStubClient implements StatsClient
{
    public function getOverview(StatsRequestDTO $request): AccountOverviewDTO
    {
        return AccountOverviewDTO::fromResponse(StubResponses::overview($request));
    }

    public function getActiveUsers(StatsRequestDTO $request): ActiveUsersDTO
    {
        return ActiveUsersDTO::fromResponse(StubResponses::activeUsers($request));
    }

    public function getEventStats(StatsRequestDTO $request): EventStatsDTO
    {
        return EventStatsDTO::fromResponse(StubResponses::eventStats($request));
    }

    public function getPlatformBreakdown(StatsRequestDTO $request): PlatformBreakdownDTO
    {
        return PlatformBreakdownDTO::fromResponse(StubResponses::platformBreakdown($request));
    }

    public function getTopItems(StatsRequestDTO $request): TopItemsDTO
    {
        return TopItemsDTO::fromResponse(StubResponses::topItems($request));
    }

    public function getTopPages(StatsRequestDTO $request): TopPagesDTO
    {
        return TopPagesDTO::fromResponse(StubResponses::topPages($request));
    }

    public function getHourlyVisits(StatsRequestDTO $request): HourlyVisitsDTO
    {
        return HourlyVisitsDTO::fromResponse(StubResponses::hourlyVisits($request));
    }
}
