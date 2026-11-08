<?php

declare(strict_types=1);

/**
 * Description of StatsRequestDTOTest.php
 *
 * The request DTO: the account path segment (an id or "all"), the query string without null fields, and the
 * dimension sent only in the platform breakdown query.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Tests\DTO;

use Dotsplatform\AnalyticsApi\DTO\Requests\StatsRequestDTO;
use Dotsplatform\AnalyticsApi\Enums\BreakdownDimension;
use Dotsplatform\AnalyticsApi\Enums\Platform;
use PHPUnit\Framework\TestCase;

class StatsRequestDTOTest extends TestCase
{
    public function testAnAccountRequestUsesTheAccountId(): void
    {
        $request = StatsRequestDTO::forAccount(42, '2026-11-01', '2026-11-07');

        $this->assertSame(42, $request->getAccountId());
        $this->assertFalse($request->isAllAccounts());
        $this->assertSame('42', $request->getAccountPathSegment());
        $this->assertSame(['date_from' => '2026-11-01', 'date_to' => '2026-11-07'], $request->toQuery());
    }

    public function testAnAllAccountsRequestUsesAll(): void
    {
        $request = StatsRequestDTO::forAllAccounts();

        $this->assertNull($request->getAccountId());
        $this->assertTrue($request->isAllAccounts());
        $this->assertSame('all', $request->getAccountPathSegment());
        $this->assertSame([], $request->toQuery());
    }

    public function testEveryFilterIsSent(): void
    {
        $request = StatsRequestDTO::fromArray([
            'accountId' => 7,
            'dateFrom' => '2026-10-01',
            'dateTo' => '2026-10-31',
            'platform' => Platform::IOS,
            'limit' => 20,
            'dimension' => BreakdownDimension::OS_VERSION,
        ]);

        $this->assertSame([
            'date_from' => '2026-10-01',
            'date_to' => '2026-10-31',
            'platform' => 'ios',
            'limit' => 20,
        ], $request->toQuery());
        $this->assertSame([
            'date_from' => '2026-10-01',
            'date_to' => '2026-10-31',
            'platform' => 'ios',
            'limit' => 20,
            'dimension' => 'os_version',
        ], $request->toPlatformBreakdownQuery());
    }

    public function testEnumsCanBeGivenAsStrings(): void
    {
        $request = StatsRequestDTO::fromArray(['platform' => 'android', 'dimension' => 'browser']);

        $this->assertSame(Platform::ANDROID, $request->getPlatform());
        $this->assertSame(BreakdownDimension::BROWSER, $request->getDimension());
    }

    public function testCopyKeepsTheOtherFields(): void
    {
        $request = StatsRequestDTO::forAccount(5, '2026-11-01', '2026-11-07')->copy(['platform' => Platform::WEB]);

        $this->assertSame(5, $request->getAccountId());
        $this->assertSame('2026-11-01', $request->getDateFrom());
        $this->assertSame(Platform::WEB, $request->getPlatform());
        $this->assertNull($request->getLimit());
    }
}
