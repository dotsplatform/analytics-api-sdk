<?php

declare(strict_types=1);

/**
 * Description of StubResponses.php
 *
 * Sample Read API responses in the exact JSON shape the analytics service answers with (the Stats actions), used by
 * SuccessStatsStubClient and the SDK tests. The "filters" object echoes the request; a request without dates gets
 * the stub period. Daily series have one row per day of that period and hours always 24 rows, like the service.
 * The numbers are samples: the totals are not the sum of the rows.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Clients\Stubs;

use DateTimeImmutable;
use Dotsplatform\AnalyticsApi\DTO\Requests\StatsRequestDTO;

final class StubResponses
{
    public const string ACCOUNT_TOKEN = '01890f3e-1b2c-7abc-9def-000000000291';

    public const string DATE_FROM = '2026-11-02';

    public const string DATE_TO = '2026-11-08';

    public const string DEFAULT_DIMENSION = 'os';

    public const int DEFAULT_LIMIT = 50;

    private const int HOURS_IN_DAY = 24;

    private const int MAX_DAYS = 366;

    /**
     * @return array<string, mixed>
     */
    public static function overview(StatsRequestDTO $request): array
    {
        return [
            'filters' => self::filters($request),
            'totals' => self::counters(180, 40, 120, 60, 12, 6, 7, 21),
            'platforms' => [
                ['platform' => 'ios'] + self::counters(30, 5, 0, 20, 4, 3, 4, 3),
                ['platform' => 'web'] + self::counters(150, 35, 120, 40, 8, 3, 3, 18),
            ],
            'daily' => self::overviewDaily($request),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function activeUsers(StatsRequestDTO $request): array
    {
        return [
            'filters' => self::filters($request),
            'daily' => self::activeUsersDaily($request),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function eventStats(StatsRequestDTO $request): array
    {
        return [
            'filters' => self::filters($request),
            'events' => [
                ['event' => '$pageview', 'events' => 30, 'device_days' => 9],
                ['event' => 'add_to_cart', 'events' => 4, 'device_days' => 2],
            ],
        ];
    }

    /**
     * The second row has no value: devices that did not report the dimension.
     *
     * @return array<string, mixed>
     */
    public static function platformBreakdown(StatsRequestDTO $request): array
    {
        $dimension = self::DEFAULT_DIMENSION;
        if ($request->getDimension() !== null) {
            $dimension = $request->getDimension()->value;
        }

        return [
            'filters' => self::filters($request),
            'dimension' => $dimension,
            'rows' => [
                ['value' => 'sample-1', 'events' => 10, 'device_days' => 4],
                ['value' => null, 'events' => 5, 'device_days' => 1],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function topItems(StatsRequestDTO $request): array
    {
        return [
            'filters' => self::filters($request),
            'items' => [
                ['item_id' => '0190a1b2-0000-7000-8000-000000000001', 'name' => 'Margherita', 'category' => 'Pizza', 'views' => 100, 'add_to_cart' => 25, 'device_days' => 50, 'add_to_cart_rate' => 0.25],
                ['item_id' => '0190a1b2-0000-7000-8000-000000000002', 'name' => null, 'category' => null, 'views' => 70, 'add_to_cart' => 0, 'device_days' => 50, 'add_to_cart_rate' => 0.0],
            ],
        ];
    }

    /**
     * The second row: items the service has not named yet.
     *
     * @return array<string, mixed>
     */
    public static function topCategories(StatsRequestDTO $request): array
    {
        return [
            'filters' => self::filters($request),
            'categories' => [
                ['category' => 'Pizza', 'category_token' => '0190a1b2-0000-7000-8000-0000000000c1', 'items' => 2, 'views' => 100, 'add_to_cart' => 25, 'device_days' => 50, 'add_to_cart_rate' => 0.25],
                ['category' => null, 'category_token' => null, 'items' => 1, 'views' => 70, 'add_to_cart' => 0, 'device_days' => 50, 'add_to_cart_rate' => 0.0],
            ],
        ];
    }

    /**
     * @return array{accounts: list<array{token: string, name: string, domain: string|null, logo_url: string|null}>}
     */
    public static function accounts(): array
    {
        return [
            'accounts' => [
                ['token' => self::ACCOUNT_TOKEN, 'name' => 'Stub Pizza', 'domain' => 'stub-pizza.test', 'logo_url' => 'https://assets.dots.live/misteram-public/stub-logo.webp'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function topPages(StatsRequestDTO $request): array
    {
        return [
            'filters' => self::filters($request),
            'pages' => [
                ['page' => 'shop.test/menu', 'views' => 12, 'device_days' => 5],
                ['page' => 'MenuScreen', 'views' => 8, 'device_days' => 3],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function hourlyVisits(StatsRequestDTO $request): array
    {
        return [
            'filters' => self::filters($request),
            'hours' => self::hours(),
            'weekdays' => [
                ['weekday' => 1, 'events' => 20, 'device_hours' => 6],
                ['weekday' => 2, 'events' => 12, 'device_hours' => 5],
                ['weekday' => 3, 'events' => 0, 'device_hours' => 0],
                ['weekday' => 4, 'events' => 0, 'device_hours' => 0],
                ['weekday' => 5, 'events' => 0, 'device_hours' => 0],
                ['weekday' => 6, 'events' => 0, 'device_hours' => 0],
                ['weekday' => 7, 'events' => 0, 'device_hours' => 0],
            ],
        ];
    }

    /**
     * @return array{account: string, date_from: string, date_to: string, platform: string|null, limit: int}
     */
    public static function filters(StatsRequestDTO $request): array
    {
        return [
            'account' => $request->getAccountPathSegment(),
            'date_from' => $request->getDateFrom() ?? self::DATE_FROM,
            'date_to' => $request->getDateTo() ?? self::DATE_TO,
            'platform' => $request->getPlatform()?->value,
            'limit' => $request->getLimit() ?? self::DEFAULT_LIMIT,
        ];
    }

    /**
     * @return list<array<string, int|string>>
     */
    private static function overviewDaily(StatsRequestDTO $request): array
    {
        $daily = [];
        foreach (self::periodDates($request) as $dayIndex => $date) {
            $daily[] = ['date' => $date] + self::counters(100 + $dayIndex, 20, 70, 30, 6, 2, 2, 10);
        }

        return $daily;
    }

    /**
     * @return list<array{date: string, dau: int, wau: int, mau: int}>
     */
    private static function activeUsersDaily(StatsRequestDTO $request): array
    {
        $daily = [];
        foreach (self::periodDates($request) as $dayIndex => $date) {
            $daily[] = ['date' => $date, 'dau' => 12 + $dayIndex, 'wau' => 40, 'mau' => 90];
        }

        return $daily;
    }

    /**
     * Every day of the echoed period, Y-m-d, at most 366 (the service's range limit).
     *
     * @return list<string>
     */
    private static function periodDates(StatsRequestDTO $request): array
    {
        $filters = self::filters($request);
        $firstDate = DateTimeImmutable::createFromFormat('!Y-m-d', $filters['date_from']);
        $lastDate = DateTimeImmutable::createFromFormat('!Y-m-d', $filters['date_to']);
        if ($firstDate === false || $lastDate === false) {
            return [];
        }

        $dates = [];
        $date = $firstDate;
        while ($date <= $lastDate) {
            $dates[] = $date->format('Y-m-d');
            if (count($dates) >= self::MAX_DAYS) {
                break;
            }

            $date = $date->modify('+1 day');
        }

        return $dates;
    }

    /**
     * Always 24 rows; the evening is busiest.
     *
     * @return list<array{hour: int, events: int, device_hours: int}>
     */
    private static function hours(): array
    {
        $hours = [];
        for ($hour = 0; $hour < self::HOURS_IN_DAY; $hour++) {
            $hours[] = ['hour' => $hour, 'events' => 0, 'device_hours' => 0];
        }

        $hours[12] = ['hour' => 12, 'events' => 7, 'device_hours' => 3];
        $hours[19] = ['hour' => 19, 'events' => 25, 'device_hours' => 8];

        return $hours;
    }

    /**
     * @return array<string, int>
     */
    private static function counters(int $events, int $sessions, int $pageViews, int $itemViews, int $addToCart, int $checkouts, int $purchases, int $deviceDays): array
    {
        return [
            'events' => $events,
            'sessions' => $sessions,
            'page_views' => $pageViews,
            'item_views' => $itemViews,
            'add_to_cart' => $addToCart,
            'checkouts' => $checkouts,
            'purchases' => $purchases,
            'device_days' => $deviceDays,
        ];
    }
}
