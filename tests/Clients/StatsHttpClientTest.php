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
    private const int ACCOUNT = StubResponses::ACCOUNT_ID;

    private const string ACCOUNT_PATH = '/api/v1/accounts/291/';

    public function testReadsTheOverview(): void
    {
        $request = $this->makeRequest();
        $this->queueJsonResponse(StubResponses::overview($request));

        $overview = $this->makeClient()->stats()->getOverview($request);

        $this->assertLastRequest('GET', self::ACCOUNT_PATH.'overview');
        $this->assertSame(['date_from' => '2026-11-01', 'date_to' => '2026-11-07'], $this->getLastRequestQuery());
        $this->assertSame(self::ACCOUNT, $overview->getFilters()->getAccountId());
        $this->assertSame('2026-11-01', $overview->getFilters()->getDateFrom());
        $this->assertSame('2026-11-07', $overview->getFilters()->getDateTo());
        $this->assertNull($overview->getFilters()->getPlatform());
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
        $this->assertSame('2026-11-03', $overview->getDaily()[1]->getDate());
        $this->assertSame(80, $overview->getDaily()[1]->getCounters()->getEvents());
    }

    public function testAllAccountsUseTheAllSegmentAndEchoANullAccount(): void
    {
        $request = StatsRequestDTO::forAllAccounts('2026-11-01', '2026-11-07')->copy(['platform' => Platform::WEB]);
        $this->queueJsonResponse(StubResponses::overview($request));

        $overview = $this->makeClient()->stats()->getOverview($request);

        $this->assertLastRequest('GET', '/api/v1/accounts/all/overview');
        $this->assertSame(['date_from' => '2026-11-01', 'date_to' => '2026-11-07', 'platform' => 'web'], $this->getLastRequestQuery());
        $this->assertNull($overview->getFilters()->getAccountId());
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
        $this->assertSame('2026-11-02', $activeUsers->getDaily()[0]->getDate());
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
                ['app_version' => '5.6.0', 'events' => 10, 'device_days' => 4],
                ['app_version' => '', 'events' => 5, 'device_days' => 1],
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
        $this->assertSame('', $breakdown->getRows()[1]->getValue());
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
            'rows' => [['device_model' => 'iPhone15,2', 'events' => 3, 'device_days' => 1]],
        ]);

        $breakdown = $this->makeClient()->stats()->getPlatformBreakdown($request);

        $this->assertArrayNotHasKey('dimension', $this->getLastRequestQuery());
        $this->assertNull($breakdown->getDimension());
        $this->assertSame('device_model', $breakdown->getDimensionValue());
        $this->assertSame('iPhone15,2', $breakdown->getRows()[0]->getValue());
    }

    public function testReadsTopItemsWithTheAddToCartRate(): void
    {
        $request = $this->makeRequest()->copy(['limit' => 1]);
        $this->queueJsonResponse(StubResponses::topItems($request));

        $topItems = $this->makeClient()->stats()->getTopItems($request);

        $this->assertLastRequest('GET', self::ACCOUNT_PATH.'items');
        $this->assertSame('pizza', $topItems->getItems()[0]->getItemId());
        $this->assertSame(100, $topItems->getItems()[0]->getViews());
        $this->assertSame(25, $topItems->getItems()[0]->getAddToCart());
        $this->assertSame(50, $topItems->getItems()[0]->getDeviceDays());
        $this->assertSame(0.25, $topItems->getItems()[0]->getAddToCartRate());
    }

    public function testAZeroAddToCartRateEncodedAsAnIntegerIsAFloat(): void
    {
        $request = $this->makeRequest();
        $this->queueRawResponse('{"filters":{"account_id":291,"date_from":"2026-11-01","date_to":"2026-11-07","platform":null},"items":[{"item_id":"sushi","views":70,"add_to_cart":0,"device_days":50,"add_to_cart_rate":0}]}');

        $topItems = $this->makeClient()->stats()->getTopItems($request);

        $this->assertSame(0.0, $topItems->getItems()[0]->getAddToCartRate());
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
        $this->assertSame(19, $hourlyVisits->getHours()[1]->getHour());
        $this->assertSame(25, $hourlyVisits->getHours()[1]->getEvents());
        $this->assertSame(8, $hourlyVisits->getHours()[1]->getDeviceDays());
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
