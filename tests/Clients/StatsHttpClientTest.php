<?php

declare(strict_types=1);

/**
 * Description of StatsHttpClientTest.php
 *
 * stats(): every endpoint's path, query string and typed response, built from the JSON the analytics Stats actions
 * return (the same shapes as tests/Feature/Api/ReadApiTest.php in the analytics service).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Tests\Clients;

use Dotsplatform\AnalyticsApi\Clients\Stubs\StubResponses;
use Dotsplatform\AnalyticsApi\DTO\Requests\StatsRequestDTO;
use Dotsplatform\AnalyticsApi\DTO\Retention\RetentionDTO;
use Dotsplatform\AnalyticsApi\DTO\Sources\SourceDTO;
use Dotsplatform\AnalyticsApi\Enums\BreakdownDimension;
use Dotsplatform\AnalyticsApi\Enums\FunnelChannel;
use Dotsplatform\AnalyticsApi\Enums\HeatmapMetric;
use Dotsplatform\AnalyticsApi\Enums\Platform;
use Dotsplatform\AnalyticsApi\Enums\RetentionView;
use Dotsplatform\AnalyticsApi\Enums\SourceKind;
use Dotsplatform\AnalyticsApi\Tests\HttpClientTestCase;

class StatsHttpClientTest extends HttpClientTestCase
{
    private const string ACCOUNT = StubResponses::ACCOUNT_TOKEN;

    private const string ACCOUNT_PATH = '/api/v1/accounts/'.StubResponses::ACCOUNT_TOKEN.'/';

    public function testReadsTheOverview(): void
    {
        $request = $this->makeRequest();
        $this->queueJsonResponse(StubResponses::overview($request));

        $overview = $this->makeClient()->stats()->getOverview($request);

        $this->assertLastRequest('GET', self::ACCOUNT_PATH.'overview');
        $this->assertSame(['date_from' => '2026-11-01', 'date_to' => '2026-11-07'], $this->getLastRequestQuery());
        $this->assertSame(self::ACCOUNT, $overview->getFilters()->getAccountToken());
        $this->assertSame('2026-11-01', $overview->getFilters()->getDateFrom());
        $this->assertSame('2026-11-07', $overview->getFilters()->getDateTo());
        $this->assertNull($overview->getFilters()->getPlatform());
        $this->assertSame(StubResponses::DEFAULT_LIMIT, $overview->getFilters()->getLimit());
        $this->assertSame(180, $overview->getTotals()->getEvents());
        $this->assertSame(40, $overview->getTotals()->getSessions());
        $this->assertSame(120, $overview->getTotals()->getPageViews());
        $this->assertSame(60, $overview->getTotals()->getItemViews());
        $this->assertSame(12, $overview->getTotals()->getAddToCart());
        $this->assertSame(6, $overview->getTotals()->getCheckouts());
        $this->assertSame(7, $overview->getTotals()->getPurchases());
        $this->assertSame(21, $overview->getTotals()->getDeviceDays());
        $this->assertCount(2, $overview->getPlatforms());
        $this->assertSame('ios', $overview->getPlatforms()[0]->getPlatform());
        $this->assertSame(4, $overview->getPlatforms()[0]->getCounters()->getPurchases());
        $this->assertCount(7, $overview->getDaily());
        $this->assertSame('2026-11-01', $overview->getDaily()[0]->getDate());
        $this->assertSame('2026-11-07', $overview->getDaily()[6]->getDate());
        $this->assertSame(101, $overview->getDaily()[1]->getCounters()->getEvents());
    }

    public function testAllAccountsUseTheAllSegmentAndEchoANullAccount(): void
    {
        $request = StatsRequestDTO::forAllAccounts('2026-11-01', '2026-11-07')->copy(['platform' => Platform::WEB]);
        $this->queueJsonResponse(StubResponses::overview($request));

        $overview = $this->makeClient()->stats()->getOverview($request);

        $this->assertLastRequest('GET', '/api/v1/accounts/all/overview');
        $this->assertSame(['date_from' => '2026-11-01', 'date_to' => '2026-11-07', 'platform' => 'web'], $this->getLastRequestQuery());
        $this->assertNull($overview->getFilters()->getAccountToken());
        $this->assertSame('web', $overview->getFilters()->getPlatform());
    }

    public function testARequestWithoutFiltersSendsAnEmptyQuery(): void
    {
        $request = StatsRequestDTO::forAccount(self::ACCOUNT);
        $this->queueJsonResponse(StubResponses::activeUsers($request));

        $activeUsers = $this->makeClient()->stats()->getActiveUsers($request);

        $this->assertLastRequest('GET', self::ACCOUNT_PATH.'active-users');
        $this->assertSame('', $this->getLastRequest()->getUri()->getQuery());
        $this->assertSame(StubResponses::DATE_FROM, $activeUsers->getFilters()->getDateFrom());
    }

    public function testReadsActiveUsers(): void
    {
        $request = $this->makeRequest()->copy(['platform' => Platform::IOS]);
        $this->queueJsonResponse(StubResponses::activeUsers($request));

        $activeUsers = $this->makeClient()->stats()->getActiveUsers($request);

        $this->assertLastRequest('GET', self::ACCOUNT_PATH.'active-users');
        $this->assertSame('ios', $this->getLastRequestQuery()['platform'] ?? null);
        $this->assertCount(7, $activeUsers->getDaily());
        $this->assertSame('2026-11-01', $activeUsers->getDaily()[0]->getDate());
        $this->assertSame(12, $activeUsers->getDaily()[0]->getDau());
        $this->assertSame(40, $activeUsers->getDaily()[0]->getWau());
        $this->assertSame(90, $activeUsers->getDaily()[0]->getMau());
    }

    public function testReadsEventStats(): void
    {
        $request = $this->makeRequest()->copy(['limit' => 10]);
        $this->queueJsonResponse(StubResponses::eventStats($request));

        $eventStats = $this->makeClient()->stats()->getEventStats($request);

        $this->assertLastRequest('GET', self::ACCOUNT_PATH.'events');
        $this->assertSame('10', $this->getLastRequestQuery()['limit'] ?? null);
        $this->assertSame(10, $eventStats->getFilters()->getLimit());
        $this->assertSame('$pageview', $eventStats->getEvents()[0]->getEvent());
        $this->assertSame(30, $eventStats->getEvents()[0]->getEvents());
        $this->assertSame(9, $eventStats->getEvents()[0]->getDeviceDays());
    }

    public function testReadsThePlatformBreakdownByTheRequestedDimension(): void
    {
        $request = $this->makeRequest()->copy(['dimension' => BreakdownDimension::APP_VERSION]);
        $this->queueJsonResponse([
            'filters' => StubResponses::filters($request),
            'dimension' => 'app_version',
            'rows' => [
                ['value' => '5.6.0', 'events' => 10, 'device_days' => 4],
                ['value' => null, 'events' => 5, 'device_days' => 1],
            ],
        ]);

        $breakdown = $this->makeClient()->stats()->getPlatformBreakdown($request);

        $this->assertLastRequest('GET', self::ACCOUNT_PATH.'platforms');
        $this->assertSame('app_version', $this->getLastRequestQuery()['dimension'] ?? null);
        $this->assertSame(BreakdownDimension::APP_VERSION, $breakdown->getDimension());
        $this->assertSame('app_version', $breakdown->getDimensionValue());
        $this->assertSame('5.6.0', $breakdown->getRows()[0]->getValue());
        $this->assertSame(10, $breakdown->getRows()[0]->getEvents());
        $this->assertSame(4, $breakdown->getRows()[0]->getDeviceDays());
        $this->assertNull($breakdown->getRows()[1]->getValue());
    }

    public function testTheDimensionIsSentOnlyToThePlatformBreakdown(): void
    {
        $request = $this->makeRequest()->copy(['dimension' => BreakdownDimension::BROWSER]);
        $this->queueJsonResponse(StubResponses::topPages($request));

        $this->makeClient()->stats()->getTopPages($request);

        $this->assertArrayNotHasKey('dimension', $this->getLastRequestQuery());
    }

    public function testAnUnknownDimensionKeepsTheRawValue(): void
    {
        $request = $this->makeRequest();
        $this->queueJsonResponse([
            'filters' => StubResponses::filters($request),
            'dimension' => 'device_model',
            'rows' => [['value' => 'iPhone15,2', 'events' => 3, 'device_days' => 1]],
        ]);

        $breakdown = $this->makeClient()->stats()->getPlatformBreakdown($request);

        $this->assertArrayNotHasKey('dimension', $this->getLastRequestQuery());
        $this->assertNull($breakdown->getDimension());
        $this->assertSame('device_model', $breakdown->getDimensionValue());
        $this->assertSame('iPhone15,2', $breakdown->getRows()[0]->getValue());
    }

    public function testReadsTopCategories(): void
    {
        $request = $this->makeRequest();
        $this->queueJsonResponse(StubResponses::topCategories($request));

        $categories = $this->makeClient()->stats()->getTopCategories($request);

        $this->assertLastRequest('GET', self::ACCOUNT_PATH.'categories');
        $this->assertSame(self::ACCOUNT, $categories->getFilters()->getAccountToken());
        $this->assertSame('Pizza', $categories->getCategories()[0]->getCategory());
        $this->assertSame(2, $categories->getCategories()[0]->getItems());
        $this->assertSame(0.25, $categories->getCategories()[0]->getAddToCartRate());
        $this->assertNull($categories->getCategories()[1]->getCategory());
    }

    public function testListsAccounts(): void
    {
        $this->queueJsonResponse(StubResponses::accounts());

        $accounts = $this->makeClient()->stats()->getAccounts()->getAccounts();

        $this->assertLastRequest('GET', '/api/v1/accounts');
        $this->assertSame(self::ACCOUNT, $accounts[0]->getToken());
        $this->assertSame('Stub Pizza', $accounts[0]->getName());
        $this->assertSame('https://assets.dots.live/misteram-public/stub-logo.webp', $accounts[0]->getLogoUrl());
    }

    public function testReadsTopItemsWithTheAddToCartRate(): void
    {
        $request = $this->makeRequest()->copy(['limit' => 1]);
        $this->queueJsonResponse(StubResponses::topItems($request));

        $topItems = $this->makeClient()->stats()->getTopItems($request);

        $this->assertLastRequest('GET', self::ACCOUNT_PATH.'items');
        $this->assertSame('0190a1b2-0000-7000-8000-000000000001', $topItems->getItems()[0]->getItemId());
        $this->assertSame('Margherita', $topItems->getItems()[0]->getName());
        $this->assertSame('Pizza', $topItems->getItems()[0]->getCategory());
        $this->assertNull($topItems->getItems()[1]->getName());
        $this->assertSame(100, $topItems->getItems()[0]->getViews());
        $this->assertSame(25, $topItems->getItems()[0]->getAddToCart());
        $this->assertSame(50, $topItems->getItems()[0]->getDeviceDays());
        $this->assertSame(0.25, $topItems->getItems()[0]->getAddToCartRate());
    }

    public function testAZeroAddToCartRateIsAFloatWhetherSentAs0Point0OrAs0(): void
    {
        $request = $this->makeRequest();
        $this->queueRawResponse('{"filters":{"account":"01890f3e-1b2c-7abc-9def-000000000291","date_from":"2026-11-01","date_to":"2026-11-07","platform":null,"limit":50},"items":[{"item_id":"sushi","views":70,"add_to_cart":0,"device_days":50,"add_to_cart_rate":0.0}]}');
        $this->queueRawResponse('{"filters":{"account":"01890f3e-1b2c-7abc-9def-000000000291","date_from":"2026-11-01","date_to":"2026-11-07","platform":null,"limit":50},"items":[{"item_id":"sushi","views":70,"add_to_cart":0,"device_days":50,"add_to_cart_rate":0}]}');

        $fromFloat = $this->makeClient()->stats()->getTopItems($request);
        $fromInteger = $this->makeClient()->stats()->getTopItems($request);

        $this->assertSame(0.0, $fromFloat->getItems()[0]->getAddToCartRate());
        $this->assertSame(0.0, $fromInteger->getItems()[0]->getAddToCartRate());
    }

    public function testMissingKeyValuesAreNull(): void
    {
        $request = $this->makeRequest();
        $filters = StubResponses::filters($request);
        $this->queueJsonResponse(['filters' => $filters, 'events' => [['event' => null, 'events' => 2, 'device_days' => 1]]]);
        $this->queueJsonResponse(['filters' => $filters, 'items' => [['item_id' => null, 'views' => 3, 'add_to_cart' => 1, 'device_days' => 2, 'add_to_cart_rate' => 0.3333]]]);
        $this->queueJsonResponse(['filters' => $filters, 'pages' => [['page' => null, 'views' => 4, 'device_days' => 2]]]);

        $this->assertNull($this->makeClient()->stats()->getEventStats($request)->getEvents()[0]->getEvent());
        $this->assertNull($this->makeClient()->stats()->getTopItems($request)->getItems()[0]->getItemId());
        $this->assertNull($this->makeClient()->stats()->getTopPages($request)->getPages()[0]->getPage());
    }

    public function testReadsTopPages(): void
    {
        $request = $this->makeRequest();
        $this->queueJsonResponse(StubResponses::topPages($request));

        $topPages = $this->makeClient()->stats()->getTopPages($request);

        $this->assertLastRequest('GET', self::ACCOUNT_PATH.'pages');
        $this->assertSame('shop.test/menu', $topPages->getPages()[0]->getPage());
        $this->assertSame(12, $topPages->getPages()[0]->getViews());
        $this->assertSame(5, $topPages->getPages()[0]->getDeviceDays());
    }

    public function testReadsHourlyVisits(): void
    {
        $request = $this->makeRequest();
        $this->queueJsonResponse(StubResponses::hourlyVisits($request));

        $hourlyVisits = $this->makeClient()->stats()->getHourlyVisits($request);

        $this->assertLastRequest('GET', self::ACCOUNT_PATH.'hourly-visits');
        $this->assertCount(24, $hourlyVisits->getHours());
        $this->assertSame(0, $hourlyVisits->getHours()[0]->getEvents());
        $this->assertSame(19, $hourlyVisits->getHours()[19]->getHour());
        $this->assertSame(25, $hourlyVisits->getHours()[19]->getEvents());
        $this->assertSame(8, $hourlyVisits->getHours()[19]->getDeviceHours());
        $this->assertSame(6, $hourlyVisits->getWeekdays()[0]->getDeviceHours());
        $this->assertCount(7, $hourlyVisits->getWeekdays());
        $this->assertSame(1, $hourlyVisits->getWeekdays()[0]->getWeekday());
        $this->assertSame(20, $hourlyVisits->getWeekdays()[0]->getEvents());
        $this->assertSame(7, $hourlyVisits->getWeekdays()[6]->getWeekday());
    }

    public function testReadsTheFunnelOfAllChannelsByDefault(): void
    {
        $request = $this->makeRequest();
        $this->queueJsonResponse(StubResponses::funnel($request));

        $funnel = $this->makeClient()->stats()->getFunnel($request);

        $this->assertLastRequest('GET', self::ACCOUNT_PATH.'funnel');
        $this->assertSame(['date_from' => '2026-11-01', 'date_to' => '2026-11-07', 'channel' => 'all'], $this->getLastRequestQuery());
        $this->assertSame(self::ACCOUNT, $funnel->getFilters()->getAccountToken());
        $this->assertSame(FunnelChannel::ALL, $funnel->getChannel());
        $this->assertSame('all', $funnel->getChannelValue());

        $steps = $funnel->getSteps();
        $this->assertCount(7, $steps);
        $this->assertSame('sessions', $steps[0]->getKey());
        $this->assertSame('Visit', $steps[0]->getLabel());
        $this->assertSame(150, $steps[0]->getSessions());
        $this->assertNull($steps[0]->getPercentOfPrevious());
        $this->assertSame(100.0, $steps[0]->getPercentOfStart());
        $this->assertFalse($steps[0]->isOptional());
        $this->assertSame('item_views', $steps[2]->getKey());
        $this->assertTrue($steps[2]->isOptional());
        $this->assertSame(80.0, $steps[1]->getPercentOfPrevious());
        $this->assertSame('orders', $steps[6]->getKey());

        $checkoutSteps = $funnel->getCheckoutSteps();
        $this->assertCount(6, $checkoutSteps);
        $this->assertSame('checkouts', $checkoutSteps[0]->getKey());
        $this->assertSame('Checkout opened', $checkoutSteps[0]->getLabel());
        $this->assertNull($checkoutSteps[1]->getPercentOfPrevious());
        $this->assertSame(40.0, $checkoutSteps[1]->getPercentOfStart());
        $this->assertSame(0.0, $checkoutSteps[3]->getPercentOfStart());

        $orders = $funnel->getOrders();
        $this->assertSame(2, $orders->getTracked());
        $this->assertSame('completed', $orders->getOutcomes()[0]->getKey());
        $this->assertSame('Completed', $orders->getOutcomes()[0]->getLabel());
        $this->assertSame(1, $orders->getOutcomes()[0]->getOrders());
        $this->assertSame(50.0, $orders->getOutcomes()[0]->getPercentOfOrders());
        $this->assertSame('cancelled', $orders->getOutcomes()[1]->getKey());
        $this->assertSame('9', $orders->getCancelReasons()[0]->getKey());
        $this->assertSame('Not accepted in time', $orders->getCancelReasons()[0]->getLabel());
        $this->assertSame(100.0, $orders->getCancelReasons()[0]->getPercentOfOrders());

        $this->assertSame(3, $funnel->getTracking()->getLivesiteOrders());
        $this->assertSame(2, $funnel->getTracking()->getMatchedOrders());
        $this->assertSame(66.67, $funnel->getTracking()->getCoveragePercent());

        $this->assertSame(40000, $funnel->getRevenue()->getTrackedCents());
        $this->assertSame(30000, $funnel->getRevenue()->getCompletedCents());
        $this->assertSame(20000, $funnel->getRevenue()->getAverageOrderCents());
    }

    public function testReadsTheFunnelOfOneChannelWithoutTracking(): void
    {
        $request = $this->makeRequest()->copy(['platform' => Platform::IOS]);
        $this->queueJsonResponse(StubResponses::funnel($request, FunnelChannel::APPS));

        $funnel = $this->makeClient()->stats()->getFunnel($request, FunnelChannel::APPS);

        $this->assertLastRequest('GET', self::ACCOUNT_PATH.'funnel');
        $this->assertSame('apps', $this->getLastRequestQuery()['channel'] ?? null);
        $this->assertSame('ios', $this->getLastRequestQuery()['platform'] ?? null);
        $this->assertSame(FunnelChannel::APPS, $funnel->getChannel());
        $this->assertNull($funnel->getTracking()->getLivesiteOrders());
        $this->assertNull($funnel->getTracking()->getMatchedOrders());
        $this->assertNull($funnel->getTracking()->getCoveragePercent());
    }

    public function testAFunnelWithoutOrdersHasNullPercentsAndNoAverageOrder(): void
    {
        $request = $this->makeRequest();
        $response = StubResponses::funnel($request);
        $response['channel'] = 'kiosk';
        $response['steps'] = [['key' => 'sessions', 'label' => 'Visit', 'sessions' => 0, 'percent_of_previous' => null, 'percent_of_start' => null, 'optional' => false]];
        $response['orders'] = ['tracked' => 0, 'outcomes' => [['key' => 'completed', 'label' => 'Completed', 'orders' => 0, 'percent_of_orders' => null]], 'cancel_reasons' => []];
        $response['revenue'] = ['tracked_cents' => 0, 'completed_cents' => 0, 'average_order_cents' => null];
        $this->queueJsonResponse($response);

        $funnel = $this->makeClient()->stats()->getFunnel($request);

        $this->assertNull($funnel->getChannel());
        $this->assertSame('kiosk', $funnel->getChannelValue());
        $this->assertNull($funnel->getSteps()[0]->getPercentOfStart());
        $this->assertNull($funnel->getOrders()->getOutcomes()[0]->getPercentOfOrders());
        $this->assertSame([], $funnel->getOrders()->getCancelReasons());
        $this->assertNull($funnel->getRevenue()->getAverageOrderCents());
    }

    public function testReadsTheHeatmapOfTheRequestedMetric(): void
    {
        $request = $this->makeRequest();
        $this->queueJsonResponse(StubResponses::heatmap($request, HeatmapMetric::ORDERS));

        $heatmap = $this->makeClient()->stats()->getHeatmap($request, HeatmapMetric::ORDERS);

        $this->assertLastRequest('GET', self::ACCOUNT_PATH.'heatmap');
        $this->assertSame(['date_from' => '2026-11-01', 'date_to' => '2026-11-07', 'metric' => 'orders'], $this->getLastRequestQuery());
        $this->assertSame('2026-11-01', $heatmap->getFilters()->getDateFrom());
        $this->assertSame(HeatmapMetric::ORDERS, $heatmap->getMetric());
        $this->assertSame('orders', $heatmap->getMetricValue());
        $this->assertSame(2.0, $heatmap->getMaxValue());
        $this->assertCount(7, $heatmap->getWeekdays());
        $this->assertSame(1, $heatmap->getWeekdays()[0]->getWeekday());
        $this->assertSame(7, $heatmap->getWeekdays()[6]->getWeekday());
        $this->assertCount(24, $heatmap->getWeekdays()[0]->getHours());
        $this->assertSame(0.0, $heatmap->getWeekdays()[0]->getHours()[0]);
        $this->assertSame(0.5, $heatmap->getWeekdays()[0]->getHours()[12]);
        $this->assertSame(2.0, $heatmap->getWeekdays()[0]->getHours()[19]);
        $this->assertSame(1.5, $heatmap->getWeekdays()[6]->getHours()[19]);
    }

    public function testTheHeatmapMetricIsSessionsByDefault(): void
    {
        $request = $this->makeRequest();
        $this->queueJsonResponse(StubResponses::heatmap($request));

        $heatmap = $this->makeClient()->stats()->getHeatmap($request);

        $this->assertSame('sessions', $this->getLastRequestQuery()['metric'] ?? null);
        $this->assertSame(HeatmapMetric::SESSIONS, $heatmap->getMetric());
    }

    public function testHeatmapValuesSentAsWholeNumbersAreFloats(): void
    {
        $request = $this->makeRequest();
        $this->queueRawResponse('{"filters":{"account":"all","date_from":"2026-11-01","date_to":"2026-11-07","platform":null,"limit":50},"metric":"conversion","max_value":50,"weekdays":[{"weekday":1,"hours":[0,50,12.5]}]}');

        $heatmap = $this->makeClient()->stats()->getHeatmap($request, HeatmapMetric::CONVERSION);

        $this->assertSame(50.0, $heatmap->getMaxValue());
        $this->assertSame([0.0, 50.0, 12.5], $heatmap->getWeekdays()[0]->getHours());
    }

    public function testReadsDevicesAndVersions(): void
    {
        $request = $this->makeRequest();
        $this->queueJsonResponse(StubResponses::devices($request));

        $devices = $this->makeClient()->stats()->getDevices($request);

        $this->assertLastRequest('GET', self::ACCOUNT_PATH.'devices');
        $this->assertFalse($devices->isEmpty());
        $this->assertSame('iPhone17,2', $devices->getDeviceModels()[0]->getValue());
        $this->assertSame('iPhone 16 Pro Max', $devices->getDeviceModels()[0]->getLabel());
        $this->assertSame(75.0, $devices->getDeviceModels()[0]->getSharePercent());
        $this->assertSame('ios,web', $devices->getOsVersions()[0]->getPlatform());
        $this->assertSame(80, $devices->getOsVersions()[0]->getDevices());
        $this->assertSame('Chrome 141', $devices->getBrowsers()[0]->getLabel());
        $this->assertSame(48, $devices->getAppVersions()[0]->getSessions());
        $this->assertCount(2, $devices->getAppVersions());
    }

    public function testReadsSourcesAndCampaigns(): void
    {
        $request = $this->makeRequest()->copy(['platform' => Platform::WEB]);
        $this->queueJsonResponse(StubResponses::sources($request));

        $sources = $this->makeClient()->stats()->getSources($request);

        $this->assertLastRequest('GET', self::ACCOUNT_PATH.'sources');
        $this->assertSame(['date_from' => '2026-11-01', 'date_to' => '2026-11-07', 'platform' => 'web'], $this->getLastRequestQuery());
        $this->assertSame('web', $sources->getFilters()->getPlatform());
        $this->assertSame(50, $sources->getTotalSessions());
        $this->assertCount(3, $sources->getSources());
        $this->assertSame('Instagram', $sources->getSources()[0]->getName());
        $this->assertSame(SourceKind::PAID, $sources->getSources()[0]->getKind());
        $this->assertSame('paid', $sources->getSources()[0]->getKindValue());
        $this->assertSame(40, $sources->getSources()[0]->getSessions());
        $this->assertSame(4, $sources->getSources()[0]->getOrderingSessions());
        $this->assertSame(10.0, $sources->getSources()[0]->getConversionPercent());
        $this->assertSame(SourceDTO::NAME_DIRECT, $sources->getSources()[1]->getName());
        $this->assertSame(SourceKind::DIRECT, $sources->getSources()[1]->getKind());
        $this->assertSame(SourceDTO::NAME_APP_IOS, $sources->getSources()[2]->getName());
        $this->assertSame(0.0, $sources->getSources()[2]->getConversionPercent());
        $this->assertCount(1, $sources->getCampaigns());
        $this->assertSame('autumn', $sources->getCampaigns()[0]->getName());
        $this->assertSame('Instagram', $sources->getCampaigns()[0]->getSourceName());
        $this->assertSame(40, $sources->getCampaigns()[0]->getSessions());
        $this->assertSame(4, $sources->getCampaigns()[0]->getOrderingSessions());
        $this->assertSame(10.0, $sources->getCampaigns()[0]->getConversionPercent());
    }

    public function testASourceOfAnUnknownKindAndWithoutSessionsKeepsTheRawValues(): void
    {
        $request = $this->makeRequest();
        $this->queueJsonResponse([
            'filters' => StubResponses::filters($request),
            'total_sessions' => 0,
            'sources' => [['name' => 'Mastodon', 'kind' => 'fediverse', 'sessions' => 0, 'ordering_sessions' => 0, 'conversion_percent' => null]],
            'campaigns' => [],
        ]);

        $sources = $this->makeClient()->stats()->getSources($request);

        $this->assertNull($sources->getSources()[0]->getKind());
        $this->assertSame('fediverse', $sources->getSources()[0]->getKindValue());
        $this->assertNull($sources->getSources()[0]->getConversionPercent());
        $this->assertSame([], $sources->getCampaigns());
    }

    public function testReadsRepeatBuyersRetentionByDefault(): void
    {
        $request = $this->makeRequest();
        $this->queueJsonResponse(StubResponses::retention($request));

        $retention = $this->makeClient()->stats()->getRetention($request);

        $this->assertLastRequest('GET', self::ACCOUNT_PATH.'retention');
        $this->assertSame(['date_from' => '2026-11-01', 'date_to' => '2026-11-07', 'view' => 'repeat_buyers'], $this->getLastRequestQuery());
        $this->assertSame(self::ACCOUNT, $retention->getAccountToken());
        $this->assertSame(RetentionView::REPEAT_BUYERS, $retention->getView());
        $this->assertSame('repeat_buyers', $retention->getViewValue());
        $this->assertSame(RetentionDTO::PERIOD_MONTH, $retention->getPeriod());
        $this->assertSame(['M1', 'M2', 'M3'], $retention->getOffsets());
        $this->assertCount(3, $retention->getCohorts());

        $oldestCohort = $retention->getCohorts()[0];
        $this->assertSame('2026-08-01', $oldestCohort->getStartDate());
        $this->assertSame('August 2026', $oldestCohort->getLabel());
        $this->assertSame(80, $oldestCohort->getSize());
        $this->assertSame([25.0, 18.75, null], $oldestCohort->getPercents());
        $this->assertTrue($oldestCohort->isMixed());
        $this->assertFalse($retention->getCohorts()[1]->isMixed());
        $this->assertSame([null, null, null], $retention->getCohorts()[2]->getPercents());
    }

    public function testReadsWeeklyDeviceRetentionOfAllAccounts(): void
    {
        $request = StatsRequestDTO::forAllAccounts()->copy(['platform' => Platform::ANDROID]);
        $this->queueJsonResponse(StubResponses::retention($request, RetentionView::DEVICES_ORDERING));

        $retention = $this->makeClient()->stats()->getRetention($request, RetentionView::DEVICES_ORDERING);

        $this->assertLastRequest('GET', '/api/v1/accounts/all/retention');
        $this->assertSame(['platform' => 'android', 'view' => 'devices_ordering'], $this->getLastRequestQuery());
        $this->assertNull($retention->getAccountToken());
        $this->assertSame(RetentionView::DEVICES_ORDERING, $retention->getView());
        $this->assertSame(RetentionDTO::PERIOD_WEEK, $retention->getPeriod());
        $this->assertCount(8, $retention->getOffsets());
        $this->assertSame('W8', $retention->getOffsets()[7]);
        $this->assertCount(8, $retention->getCohorts()[0]->getPercents());
        $this->assertSame(28.89, $retention->getCohorts()[1]->getPercents()[0]);
    }

    private function makeRequest(): StatsRequestDTO
    {
        return StatsRequestDTO::forAccount(self::ACCOUNT, '2026-11-01', '2026-11-07');
    }
}
