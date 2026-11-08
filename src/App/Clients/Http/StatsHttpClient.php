<?php

declare(strict_types=1);

/**
 * Description of StatsHttpClient.php
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Clients\Http;

use Dotsplatform\AnalyticsApi\Contracts\StatsClient;
use Dotsplatform\AnalyticsApi\DTO\ActiveUsers\ActiveUsersDTO;
use Dotsplatform\AnalyticsApi\DTO\Events\EventStatsDTO;
use Dotsplatform\AnalyticsApi\DTO\HourlyVisits\HourlyVisitsDTO;
use Dotsplatform\AnalyticsApi\DTO\Items\TopItemsDTO;
use Dotsplatform\AnalyticsApi\DTO\Overview\AccountOverviewDTO;
use Dotsplatform\AnalyticsApi\DTO\Pages\TopPagesDTO;
use Dotsplatform\AnalyticsApi\DTO\Platforms\PlatformBreakdownDTO;
use Dotsplatform\AnalyticsApi\DTO\Requests\StatsRequestDTO;
use Dotsplatform\AnalyticsApi\Http\AnalyticsHttpTransport;

class StatsHttpClient implements StatsClient
{
    private const string OVERVIEW_URL_TEMPLATE = 'api/v1/accounts/%s/overview';

    private const string ACTIVE_USERS_URL_TEMPLATE = 'api/v1/accounts/%s/active-users';

    private const string EVENTS_URL_TEMPLATE = 'api/v1/accounts/%s/events';

    private const string PLATFORMS_URL_TEMPLATE = 'api/v1/accounts/%s/platforms';

    private const string ITEMS_URL_TEMPLATE = 'api/v1/accounts/%s/items';

    private const string PAGES_URL_TEMPLATE = 'api/v1/accounts/%s/pages';

    private const string HOURLY_VISITS_URL_TEMPLATE = 'api/v1/accounts/%s/hourly-visits';

    public function __construct(
        private readonly AnalyticsHttpTransport $transport,
    ) {
    }

    public function getOverview(StatsRequestDTO $request): AccountOverviewDTO
    {
        $response = $this->transport->getJson($this->buildPath(self::OVERVIEW_URL_TEMPLATE, $request), $request->toQuery());

        return AccountOverviewDTO::fromResponse($response);
    }

    public function getActiveUsers(StatsRequestDTO $request): ActiveUsersDTO
    {
        $response = $this->transport->getJson($this->buildPath(self::ACTIVE_USERS_URL_TEMPLATE, $request), $request->toQuery());

        return ActiveUsersDTO::fromResponse($response);
    }

    public function getEventStats(StatsRequestDTO $request): EventStatsDTO
    {
        $response = $this->transport->getJson($this->buildPath(self::EVENTS_URL_TEMPLATE, $request), $request->toQuery());

        return EventStatsDTO::fromResponse($response);
    }

    public function getPlatformBreakdown(StatsRequestDTO $request): PlatformBreakdownDTO
    {
        $response = $this->transport->getJson($this->buildPath(self::PLATFORMS_URL_TEMPLATE, $request), $request->toPlatformBreakdownQuery());

        return PlatformBreakdownDTO::fromResponse($response);
    }

    public function getTopItems(StatsRequestDTO $request): TopItemsDTO
    {
        $response = $this->transport->getJson($this->buildPath(self::ITEMS_URL_TEMPLATE, $request), $request->toQuery());

        return TopItemsDTO::fromResponse($response);
    }

    public function getTopPages(StatsRequestDTO $request): TopPagesDTO
    {
        $response = $this->transport->getJson($this->buildPath(self::PAGES_URL_TEMPLATE, $request), $request->toQuery());

        return TopPagesDTO::fromResponse($response);
    }

    public function getHourlyVisits(StatsRequestDTO $request): HourlyVisitsDTO
    {
        $response = $this->transport->getJson($this->buildPath(self::HOURLY_VISITS_URL_TEMPLATE, $request), $request->toQuery());

        return HourlyVisitsDTO::fromResponse($response);
    }

    private function buildPath(string $template, StatsRequestDTO $request): string
    {
        return $this->transport->buildPath($template, $request->getAccountPathSegment());
    }
}
