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
use Dotsplatform\AnalyticsApi\Enums\BreakdownDimension;
use Dotsplatform\AnalyticsApi\Enums\Platform;
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

    private function makeRequest(): StatsRequestDTO
    {
        return StatsRequestDTO::forAccount(self::ACCOUNT, '2026-11-01', '2026-11-07');
    }
}
