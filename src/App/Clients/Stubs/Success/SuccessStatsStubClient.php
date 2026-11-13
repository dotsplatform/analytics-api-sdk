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
use Dotsplatform\AnalyticsApi\DTO\Accounts\AccountListDTO;
use Dotsplatform\AnalyticsApi\DTO\ActiveUsers\ActiveUsersDTO;
use Dotsplatform\AnalyticsApi\DTO\Categories\TopCategoriesDTO;
use Dotsplatform\AnalyticsApi\DTO\Devices\DevicesDTO;
use Dotsplatform\AnalyticsApi\DTO\Events\EventStatsDTO;
use Dotsplatform\AnalyticsApi\DTO\Funnel\FunnelDTO;
use Dotsplatform\AnalyticsApi\DTO\Heatmap\HeatmapDTO;
use Dotsplatform\AnalyticsApi\DTO\HourlyVisits\HourlyVisitsDTO;
use Dotsplatform\AnalyticsApi\DTO\Installs\AppInstallsDTO;
use Dotsplatform\AnalyticsApi\DTO\Items\TopItemsDTO;
use Dotsplatform\AnalyticsApi\DTO\Overview\AccountOverviewDTO;
use Dotsplatform\AnalyticsApi\DTO\Pages\TopPagesDTO;
use Dotsplatform\AnalyticsApi\DTO\Platforms\PlatformBreakdownDTO;
use Dotsplatform\AnalyticsApi\DTO\Push\PushStatsDTO;
use Dotsplatform\AnalyticsApi\DTO\Sessions\SessionDurationDTO;
use Dotsplatform\AnalyticsApi\DTO\Requests\StatsRequestDTO;
use Dotsplatform\AnalyticsApi\DTO\Retention\RetentionDTO;
use Dotsplatform\AnalyticsApi\DTO\Sources\SourcesDTO;
use Dotsplatform\AnalyticsApi\Enums\FunnelChannel;
use Dotsplatform\AnalyticsApi\Enums\HeatmapMetric;
use Dotsplatform\AnalyticsApi\Enums\RetentionView;

class SuccessStatsStubClient implements StatsClient
{
    public function getAccounts(): AccountListDTO
    {
        return AccountListDTO::fromResponse(StubResponses::accounts());
    }

    public function getTopCategories(StatsRequestDTO $request): TopCategoriesDTO
    {
        return TopCategoriesDTO::fromResponse(StubResponses::topCategories($request));
    }

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

    public function getFunnel(StatsRequestDTO $request, FunnelChannel $channel = FunnelChannel::ALL): FunnelDTO
    {
        return FunnelDTO::fromResponse(StubResponses::funnel($request, $channel));
    }

    public function getHeatmap(StatsRequestDTO $request, HeatmapMetric $metric = HeatmapMetric::SESSIONS): HeatmapDTO
    {
        return HeatmapDTO::fromResponse(StubResponses::heatmap($request, $metric));
    }

    public function getSources(StatsRequestDTO $request): SourcesDTO
    {
        return SourcesDTO::fromResponse(StubResponses::sources($request));
    }

    public function getDevices(StatsRequestDTO $request): DevicesDTO
    {
        return DevicesDTO::fromResponse(StubResponses::devices($request));
    }

    public function getInstalls(StatsRequestDTO $request): AppInstallsDTO
    {
        return AppInstallsDTO::fromResponse(StubResponses::installs($request));
    }

    public function getPushStats(StatsRequestDTO $request): PushStatsDTO
    {
        return PushStatsDTO::fromResponse(StubResponses::push($request));
    }

    public function getSessionDuration(StatsRequestDTO $request): SessionDurationDTO
    {
        return SessionDurationDTO::fromResponse(StubResponses::sessionDuration($request));
    }

    public function getRetention(StatsRequestDTO $request, RetentionView $view = RetentionView::REPEAT_BUYERS): RetentionDTO
    {
        return RetentionDTO::fromResponse(StubResponses::retention($request, $view));
    }
}
