<?php

declare(strict_types=1);

/**
 * Description of StubResponses.php
 *
 * Sample Read API responses in the exact JSON shape the analytics service answers with (the Stats actions), used by
 * SuccessStatsStubClient and the SDK tests. The "filters" object echoes the request; a request without dates gets
 * the stub period.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Clients\Stubs;

use Dotsplatform\AnalyticsApi\DTO\Requests\StatsRequestDTO;

final class StubResponses
{
    public const int ACCOUNT_ID = 291;

    public const string DATE_FROM = '2026-11-02';

    public const string DATE_TO = '2026-11-08';

    public const string DEFAULT_DIMENSION = 'os';

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
            'daily' => [
                ['date' => '2026-11-02'] + self::counters(100, 20, 70, 30, 6, 2, 2, 10),
                ['date' => '2026-11-03'] + self::counters(80, 20, 50, 30, 6, 4, 5, 11),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function activeUsers(StatsRequestDTO $request): array
    {
        return [
            'filters' => self::filters($request),
            'daily' => [
                ['date' => '2026-11-02', 'dau' => 12, 'wau' => 40, 'mau' => 90],
                ['date' => '2026-11-03', 'dau' => 15, 'wau' => 44, 'mau' => 92],
            ],
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
     * The value key of each row is the dimension, as the service sends it.
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
                [$dimension => 'sample-1', 'events' => 10, 'device_days' => 4],
                [$dimension => 'sample-2', 'events' => 5, 'device_days' => 1],
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
                ['item_id' => 'pizza', 'views' => 100, 'add_to_cart' => 25, 'device_days' => 50, 'add_to_cart_rate' => 0.25],
                // PHP's json_encode writes 0.0 as 0, so a zero rate arrives as an integer.
                ['item_id' => 'sushi', 'views' => 70, 'add_to_cart' => 0, 'device_days' => 50, 'add_to_cart_rate' => 0],
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
            'hours' => [
                ['hour' => 12, 'events' => 7, 'device_days' => 3],
                ['hour' => 19, 'events' => 25, 'device_days' => 8],
            ],
            'weekdays' => [
                ['weekday' => 1, 'events' => 20, 'device_days' => 6],
                ['weekday' => 2, 'events' => 12, 'device_days' => 5],
                ['weekday' => 3, 'events' => 0, 'device_days' => 0],
                ['weekday' => 4, 'events' => 0, 'device_days' => 0],
                ['weekday' => 5, 'events' => 0, 'device_days' => 0],
                ['weekday' => 6, 'events' => 0, 'device_days' => 0],
                ['weekday' => 7, 'events' => 0, 'device_days' => 0],
            ],
        ];
    }

    /**
     * @return array{account_id: int|null, date_from: string, date_to: string, platform: string|null}
     */
    public static function filters(StatsRequestDTO $request): array
    {
        return [
            'account_id' => $request->getAccountId(),
            'date_from' => $request->getDateFrom() ?? self::DATE_FROM,
            'date_to' => $request->getDateTo() ?? self::DATE_TO,
            'platform' => $request->getPlatform()?->value,
        ];
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
