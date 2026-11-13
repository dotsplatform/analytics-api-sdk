<?php

declare(strict_types=1);

/**
 * Description of StatsClient.php
 *
 * The Read API of the analytics service: GET api/v1/accounts and api/v1/accounts/{account}/... ({account} = the livesite
 * account token or "all"). One method per endpoint. It reads daily
 * rollups, so today's numbers grow during the day and the latest events may not be counted yet.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Contracts;

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

interface StatsClient
{
    /**
     * GET accounts: every account the service knows (token, name, domain, logo URL).
     */
    public function getAccounts(): AccountListDTO;

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
     * GET categories: menu categories by item views with the add-to-cart rate (limit applies).
     */
    public function getTopCategories(StatsRequestDTO $request): TopCategoriesDTO;

    /**
     * GET pages: the most viewed pages and screens (limit applies).
     */
    public function getTopPages(StatsRequestDTO $request): TopPagesDTO;

    /**
     * GET hourly-visits: totals by local hour and by weekday.
     */
    public function getHourlyVisits(StatsRequestDTO $request): HourlyVisitsDTO;

    /**
     * GET funnel: sessions from a visit to an order, the checkout steps, the outcomes of the tracked orders, the
     * livesite order tracking coverage (channel ALL only) and the revenue. The request limit is not used.
     */
    public function getFunnel(StatsRequestDTO $request, FunnelChannel $channel = FunnelChannel::ALL): FunnelDTO;

    /**
     * GET heatmap: 7 weekdays × 24 Kyiv hours of the metric. The request limit is not used.
     */
    public function getHeatmap(StatsRequestDTO $request, HeatmapMetric $metric = HeatmapMetric::SESSIONS): HeatmapDTO;

    /**
     * GET sources: sessions and conversion by traffic source (first touch), and the top 10 UTM campaigns.
     * The request limit is not used.
     */
    public function getSources(StatsRequestDTO $request): SourcesDTO;

    /**
     * GET devices: the top 10 device models, OS versions, browsers and app versions. The request limit is not used.
     */
    public function getDevices(StatsRequestDTO $request): DevicesDTO;

    /**
     * GET installs: app installs and updates per platform and day, with the totals of the period before.
     * The request limit is not used.
     */
    public function getInstalls(StatsRequestDTO $request): AppInstallsDTO;

    /**
     * GET push: push notifications sent, failed, delivered and opened (campaign / order, per day, the period before).
     * The request platform and limit are not used.
     */
    public function getPushStats(StatsRequestDTO $request): PushStatsDTO;

    /**
     * GET session-duration: average session duration and duration buckets per platform, the "all" row last.
     * The request limit is not used.
     */
    public function getSessionDuration(StatsRequestDTO $request): SessionDurationDTO;

    /**
     * GET retention: a cohort table of the view. The request period and limit are not used; the platform applies
     * to the device views only.
     */
    public function getRetention(StatsRequestDTO $request, RetentionView $view = RetentionView::REPEAT_BUYERS): RetentionDTO;
}
