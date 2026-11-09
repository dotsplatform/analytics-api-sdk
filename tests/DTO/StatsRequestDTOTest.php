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
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StatsRequestDTOTest extends TestCase
{
    private const string TOKEN = '01890f3e-1b2c-7abc-9def-000000000042';

    public function testAnAccountRequestUsesTheAccountToken(): void
    {
        $request = StatsRequestDTO::forAccount(strtoupper(self::TOKEN), '2026-11-01', '2026-11-07');

        $this->assertSame(strtoupper(self::TOKEN), $request->getAccountToken());
        $this->assertFalse($request->isAllAccounts());
        $this->assertSame(self::TOKEN, $request->getAccountPathSegment());
        $this->assertSame(['date_from' => '2026-11-01', 'date_to' => '2026-11-07'], $request->toQuery());
    }

    public function testAnAllAccountsRequestUsesAll(): void
    {
        $request = StatsRequestDTO::forAllAccounts();

        $this->assertNull($request->getAccountToken());
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

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidAccountTokens(): iterable
    {
        yield 'numeric id' => ['42'];
        yield 'empty' => [''];
        yield 'path injection' => ['../all'];
        yield 'almost a uuid' => ['01890f3e-1b2c-7abc-9def-00000000004'];
    }

    #[DataProvider('invalidAccountTokens')]
    public function testForAccountRejectsATokenThatIsNotAUuid(string $accountToken): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be a uuid');

        StatsRequestDTO::forAccount($accountToken);
    }

    public function testFromArrayAndCopyRejectAnInvalidToken(): void
    {
        $exceptionsCount = 0;

        try {
            StatsRequestDTO::fromArray(['accountToken' => 'abc']);
        } catch (InvalidArgumentException) {
            $exceptionsCount++;
        }

        try {
            StatsRequestDTO::forAccount(self::TOKEN)->copy(['accountToken' => '42']);
        } catch (InvalidArgumentException) {
            $exceptionsCount++;
        }

        $this->assertSame(2, $exceptionsCount);
    }

    public function testAnAccountTokenThatIsNotAStringIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        StatsRequestDTO::fromArray(['accountToken' => 42]);
    }

    public function testCopyKeepsTheOtherFields(): void
    {
        $request = StatsRequestDTO::forAccount(self::TOKEN, '2026-11-01', '2026-11-07')->copy(['platform' => Platform::WEB]);

        $this->assertSame(self::TOKEN, $request->getAccountToken());
        $this->assertSame('2026-11-01', $request->getDateFrom());
        $this->assertSame(Platform::WEB, $request->getPlatform());
        $this->assertNull($request->getLimit());
    }
}
