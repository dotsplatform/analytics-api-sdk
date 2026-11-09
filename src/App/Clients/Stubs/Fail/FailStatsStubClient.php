<?php

declare(strict_types=1);

/**
 * Description of FailStatsStubClient.php
 *
 * Every stats call throws AnalyticsServerException (503).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Clients\Stubs\Fail;

use Dotsplatform\AnalyticsApi\Contracts\StatsClient;
use Dotsplatform\AnalyticsApi\DTO\Accounts\AccountListDTO;
use Dotsplatform\AnalyticsApi\DTO\ActiveUsers\ActiveUsersDTO;
use Dotsplatform\AnalyticsApi\DTO\Categories\TopCategoriesDTO;
use Dotsplatform\AnalyticsApi\DTO\Events\EventStatsDTO;
use Dotsplatform\AnalyticsApi\DTO\HourlyVisits\HourlyVisitsDTO;
use Dotsplatform\AnalyticsApi\DTO\Items\TopItemsDTO;
use Dotsplatform\AnalyticsApi\DTO\Overview\AccountOverviewDTO;
use Dotsplatform\AnalyticsApi\DTO\Pages\TopPagesDTO;
use Dotsplatform\AnalyticsApi\DTO\Platforms\PlatformBreakdownDTO;
use Dotsplatform\AnalyticsApi\DTO\Requests\StatsRequestDTO;

class FailStatsStubClient implements StatsClient
{
    public function getAccounts(): AccountListDTO
    {
        throw StubFailure::make();
    }

    public function getTopCategories(StatsRequestDTO $request): TopCategoriesDTO
    {
        throw StubFailure::make();
    }

    public function getOverview(StatsRequestDTO $request): AccountOverviewDTO
    {
        throw StubFailure::make();
    }

    public function getActiveUsers(StatsRequestDTO $request): ActiveUsersDTO
    {
        throw StubFailure::make();
    }

    public function getEventStats(StatsRequestDTO $request): EventStatsDTO
    {
        throw StubFailure::make();
    }

    public function getPlatformBreakdown(StatsRequestDTO $request): PlatformBreakdownDTO
    {
        throw StubFailure::make();
    }

    public function getTopItems(StatsRequestDTO $request): TopItemsDTO
    {
        throw StubFailure::make();
    }

    public function getTopPages(StatsRequestDTO $request): TopPagesDTO
    {
        throw StubFailure::make();
    }

    public function getHourlyVisits(StatsRequestDTO $request): HourlyVisitsDTO
    {
        throw StubFailure::make();
    }
}
