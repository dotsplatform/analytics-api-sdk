<?php

declare(strict_types=1);

/**
 * Description of StubClientsTest.php
 *
 * SuccessAnalyticsStubClient answers every stats method with typed sample data; FailAnalyticsStubClient throws
 * AnalyticsServerException (503) from every method.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Tests\Stubs;

use Dotsplatform\AnalyticsApi\AnalyticsClient;
use Dotsplatform\AnalyticsApi\Clients\FailAnalyticsStubClient;
use Dotsplatform\AnalyticsApi\Clients\Stubs\Fail\StubFailure;
use Dotsplatform\AnalyticsApi\Clients\Stubs\StubResponses;
use Dotsplatform\AnalyticsApi\Clients\SuccessAnalyticsStubClient;
use Dotsplatform\AnalyticsApi\DTO\Requests\StatsRequestDTO;
use Dotsplatform\AnalyticsApi\Enums\BreakdownDimension;
use Dotsplatform\AnalyticsApi\Enums\Platform;
use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsServerException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StubClientsTest extends TestCase
{
    public function testTheSuccessStubAnswersEveryMethod(): void
    {
        $stats = (new SuccessAnalyticsStubClient())->stats();
        $request = StatsRequestDTO::forAccount(StubResponses::ACCOUNT_ID);

        $this->assertSame(180, $stats->getOverview($request)->getTotals()->getEvents());
        $this->assertSame(12, $stats->getActiveUsers($request)->getDaily()[0]->getDau());
        $this->assertSame('$pageview', $stats->getEventStats($request)->getEvents()[0]->getEvent());
        $this->assertSame(BreakdownDimension::OS, $stats->getPlatformBreakdown($request)->getDimension());
        $this->assertSame(0.0, $stats->getTopItems($request)->getItems()[1]->getAddToCartRate());
        $this->assertSame('shop.test/menu', $stats->getTopPages($request)->getPages()[0]->getPage());
        $this->assertCount(7, $stats->getHourlyVisits($request)->getWeekdays());
        $this->assertCount(24, $stats->getHourlyVisits($request)->getHours());
        $this->assertCount(7, $stats->getOverview($request)->getDaily());
    }

    public function testTheSuccessStubEchoesTheRequestFilters(): void
    {
        $stats = (new SuccessAnalyticsStubClient())->stats();
        $request = StatsRequestDTO::forAllAccounts('2026-10-01', '2026-10-31')->copy([
            'platform' => Platform::ANDROID,
            'dimension' => BreakdownDimension::APP_VERSION,
        ]);

        $breakdown = $stats->getPlatformBreakdown($request);

        $this->assertNull($breakdown->getFilters()->getAccountId());
        $this->assertSame('2026-10-01', $breakdown->getFilters()->getDateFrom());
        $this->assertSame('2026-10-31', $breakdown->getFilters()->getDateTo());
        $this->assertSame('android', $breakdown->getFilters()->getPlatform());
        $this->assertSame(BreakdownDimension::APP_VERSION, $breakdown->getDimension());
        $this->assertSame(StubResponses::DEFAULT_LIMIT, $breakdown->getFilters()->getLimit());
        $this->assertSame('sample-1', $breakdown->getRows()[0]->getValue());
        $this->assertNull($breakdown->getRows()[1]->getValue());
        $this->assertCount(31, $stats->getOverview($request)->getDaily());
        $this->assertCount(31, $stats->getActiveUsers($request)->getDaily());
    }

    /**
     * @return iterable<string, array{callable(AnalyticsClient, StatsRequestDTO): mixed}>
     */
    public static function statsCalls(): iterable
    {
        yield 'overview' => [fn (AnalyticsClient $client, StatsRequestDTO $request) => $client->stats()->getOverview($request)];
        yield 'active users' => [fn (AnalyticsClient $client, StatsRequestDTO $request) => $client->stats()->getActiveUsers($request)];
        yield 'events' => [fn (AnalyticsClient $client, StatsRequestDTO $request) => $client->stats()->getEventStats($request)];
        yield 'platforms' => [fn (AnalyticsClient $client, StatsRequestDTO $request) => $client->stats()->getPlatformBreakdown($request)];
        yield 'items' => [fn (AnalyticsClient $client, StatsRequestDTO $request) => $client->stats()->getTopItems($request)];
        yield 'pages' => [fn (AnalyticsClient $client, StatsRequestDTO $request) => $client->stats()->getTopPages($request)];
        yield 'hourly visits' => [fn (AnalyticsClient $client, StatsRequestDTO $request) => $client->stats()->getHourlyVisits($request)];
    }

    /**
     * @param  callable(AnalyticsClient, StatsRequestDTO): mixed  $call
     */
    #[DataProvider('statsCalls')]
    public function testTheFailStubThrowsFromEveryMethod(callable $call): void
    {
        $this->expectException(AnalyticsServerException::class);
        $this->expectExceptionCode(StubFailure::STATUS_CODE);
        $this->expectExceptionMessage(StubFailure::MESSAGE);

        $call(new FailAnalyticsStubClient(), StatsRequestDTO::forAccount(StubResponses::ACCOUNT_ID));
    }
}
