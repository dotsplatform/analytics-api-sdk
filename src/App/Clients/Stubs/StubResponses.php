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
use Dotsplatform\AnalyticsApi\Enums\FunnelChannel;
use Dotsplatform\AnalyticsApi\Enums\HeatmapMetric;
use Dotsplatform\AnalyticsApi\Enums\RetentionView;

final class StubResponses
{
    public const string ACCOUNT_TOKEN = '01890f3e-1b2c-7abc-9def-000000000291';

    public const string DATE_FROM = '2026-11-02';

    public const string DATE_TO = '2026-11-08';

    public const string DEFAULT_DIMENSION = 'os';

    public const int DEFAULT_LIMIT = 50;

    private const int HOURS_IN_DAY = 24;

    private const int DAYS_IN_WEEK = 7;

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
     * Item views (the dish card) are optional: people can add to cart straight from the menu.
     * The tracking numbers are null unless the channel is "all", like the service.
     *
     * @return array<string, mixed>
     */
    public static function funnel(StatsRequestDTO $request, FunnelChannel $channel = FunnelChannel::ALL): array
    {
        return [
            'filters' => self::filters($request),
            'channel' => $channel->value,
            'steps' => [
                self::funnelStep('sessions', 'Visit', 150, null, 100.0, false),
                self::funnelStep('menus', 'Company menu', 120, 80.0, 80.0, false),
                self::funnelStep('item_views', 'Dish card', 60, 50.0, 40.0, true),
                self::funnelStep('add_to_cart', 'Added to cart', 45, 75.0, 30.0, false),
                self::funnelStep('cart_views', 'Cart opened', 40, 88.89, 26.67, false),
                self::funnelStep('checkouts', 'Checkout', 30, 75.0, 20.0, false),
                self::funnelStep('orders', 'Order placed', 2, 6.67, 1.33, false),
            ],
            'checkout_steps' => [
                self::funnelStep('checkouts', 'Checkout opened', 30, null, 100.0, false),
                self::funnelStep('verification_shown', 'Phone verification shown', 12, null, 40.0, false),
                self::funnelStep('sms_sent', 'SMS code sent', 10, null, 33.33, false),
                self::funnelStep('captchas', 'Captcha shown', 0, null, 0.0, false),
                self::funnelStep('payment_info', 'Payment details entered', 4, null, 13.33, false),
                self::funnelStep('orders', 'Order placed', 2, null, 6.67, false),
            ],
            'orders' => [
                'tracked' => 2,
                'outcomes' => [
                    ['key' => 'completed', 'label' => 'Completed', 'orders' => 1, 'percent_of_orders' => 50.0],
                    ['key' => 'cancelled', 'label' => 'Cancelled', 'orders' => 1, 'percent_of_orders' => 50.0],
                ],
                'cancel_reasons' => [
                    ['key' => '9', 'label' => 'Not accepted in time', 'orders' => 1, 'percent_of_orders' => 100.0],
                ],
            ],
            'tracking' => self::funnelTracking($channel),
            'revenue' => ['tracked_cents' => 40000, 'completed_cents' => 30000, 'average_order_cents' => 20000],
        ];
    }

    /**
     * Activity on Monday at lunch and every evening; the other cells are zero.
     *
     * @return array<string, mixed>
     */
    public static function heatmap(StatsRequestDTO $request, HeatmapMetric $metric = HeatmapMetric::SESSIONS): array
    {
        $weekdays = [];
        for ($weekday = 1; $weekday <= self::DAYS_IN_WEEK; $weekday++) {
            $weekdays[] = ['weekday' => $weekday, 'hours' => self::heatmapHours($weekday)];
        }

        return [
            'filters' => self::filters($request),
            'metric' => $metric->value,
            'max_value' => 2.0,
            'weekdays' => $weekdays,
        ];
    }

    /**
     * The last source had sessions but no orders.
     *
     * @return array<string, mixed>
     */
    public static function sources(StatsRequestDTO $request): array
    {
        return [
            'filters' => self::filters($request),
            'total_sessions' => 50,
            'sources' => [
                ['name' => 'Instagram', 'kind' => 'paid', 'sessions' => 40, 'ordering_sessions' => 4, 'conversion_percent' => 10.0],
                ['name' => 'Direct', 'kind' => 'direct', 'sessions' => 8, 'ordering_sessions' => 1, 'conversion_percent' => 12.5],
                ['name' => 'App (iOS)', 'kind' => 'app', 'sessions' => 2, 'ordering_sessions' => 0, 'conversion_percent' => 0.0],
            ],
            'campaigns' => [
                ['name' => 'autumn', 'kind' => 'Instagram', 'sessions' => 40, 'ordering_sessions' => 4, 'conversion_percent' => 10.0],
            ],
        ];
    }

    /**
     * Devices: two iPhone models (one named), OS versions merged over platforms, one browser, app versions per platform.
     *
     * @return array<string, mixed>
     */
    public static function devices(StatsRequestDTO $request): array
    {
        return [
            'filters' => self::filters($request),
            'dimensions' => [
                'device_model' => [
                    ['value' => 'iPhone17,2', 'label' => 'iPhone 16 Pro Max', 'platform' => 'ios', 'devices' => 30, 'sessions' => 60, 'share_percent' => 75.0],
                    ['value' => 'SM-S918B', 'label' => 'SM-S918B', 'platform' => 'android', 'devices' => 10, 'sessions' => 20, 'share_percent' => 25.0],
                ],
                'os_version' => [
                    ['value' => 'iOS 18', 'label' => 'iOS 18', 'platform' => 'ios,web', 'devices' => 80, 'sessions' => 160, 'share_percent' => 80.0],
                    ['value' => 'Windows 10', 'label' => 'Windows 10', 'platform' => 'web', 'devices' => 20, 'sessions' => 40, 'share_percent' => 20.0],
                ],
                'browser' => [
                    ['value' => 'Chrome 141', 'label' => 'Chrome 141', 'platform' => 'web', 'devices' => 20, 'sessions' => 40, 'share_percent' => 100.0],
                ],
                'app_version' => [
                    ['value' => '5.10.1', 'label' => '5.10.1', 'platform' => 'ios', 'devices' => 24, 'sessions' => 48, 'share_percent' => 80.0],
                    ['value' => '5.6.2', 'label' => '5.6.2', 'platform' => 'ios', 'devices' => 6, 'sessions' => 12, 'share_percent' => 20.0],
                ],
            ],
        ];
    }

    /**
     * Installs: iOS and Android over two days, 10 installs in the period before.
     *
     * @return array<string, mixed>
     */
    public static function installs(StatsRequestDTO $request): array
    {
        return [
            'filters' => self::filters($request),
            'installs' => [
                'installs' => 24,
                'updates' => 30,
                'previous_installs' => 10,
                'previous_updates' => 0,
                'platforms' => [
                    ['platform' => 'android', 'installs' => 8, 'updates' => 0],
                    ['platform' => 'ios', 'installs' => 16, 'updates' => 30],
                ],
                'days' => [
                    ['date' => '2026-11-10', 'installs' => 20, 'updates' => 0],
                    ['date' => '2026-11-11', 'installs' => 4, 'updates' => 30],
                ],
            ],
        ];
    }

    /**
     * Repeat buyers: monthly cohorts with offsets M1–M3. The device views: weekly cohorts with offsets W1–W8.
     * The oldest cohort is mixed; null percents are periods that have not happened yet.
     *
     * @return array<string, mixed>
     */
    public static function retention(StatsRequestDTO $request, RetentionView $view = RetentionView::REPEAT_BUYERS): array
    {
        if ($view === RetentionView::REPEAT_BUYERS) {
            return [
                'account' => $request->getAccountPathSegment(),
                'view' => $view->value,
                'period' => 'month',
                'offsets' => ['M1', 'M2', 'M3'],
                'cohorts' => [
                    ['start_date' => '2026-08-01', 'label' => 'August 2026', 'size' => 80, 'percents' => [25.0, 18.75, null], 'is_mixed' => true],
                    ['start_date' => '2026-09-01', 'label' => 'September 2026', 'size' => 50, 'percents' => [20.0, null, null], 'is_mixed' => false],
                    ['start_date' => '2026-10-01', 'label' => 'October 2026', 'size' => 40, 'percents' => [null, null, null], 'is_mixed' => false],
                ],
            ];
        }

        return [
            'account' => $request->getAccountPathSegment(),
            'view' => $view->value,
            'period' => 'week',
            'offsets' => ['W1', 'W2', 'W3', 'W4', 'W5', 'W6', 'W7', 'W8'],
            'cohorts' => [
                ['start_date' => '2026-10-19', 'label' => 'Week of 19 Oct', 'size' => 120, 'percents' => [30.0, 22.5, null, null, null, null, null, null], 'is_mixed' => true],
                ['start_date' => '2026-10-26', 'label' => 'Week of 26 Oct', 'size' => 90, 'percents' => [28.89, null, null, null, null, null, null, null], 'is_mixed' => false],
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
     * @return array{key: string, label: string, sessions: int, percent_of_previous: float|null, percent_of_start: float|null, optional: bool}
     */
    private static function funnelStep(string $key, string $label, int $sessions, ?float $percentOfPrevious, ?float $percentOfStart, bool $optional): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'sessions' => $sessions,
            'percent_of_previous' => $percentOfPrevious,
            'percent_of_start' => $percentOfStart,
            'optional' => $optional,
        ];
    }

    /**
     * @return array{livesite_orders: int|null, matched_orders: int|null, coverage_percent: float|null}
     */
    private static function funnelTracking(FunnelChannel $channel): array
    {
        if ($channel !== FunnelChannel::ALL) {
            return ['livesite_orders' => null, 'matched_orders' => null, 'coverage_percent' => null];
        }

        return ['livesite_orders' => 3, 'matched_orders' => 2, 'coverage_percent' => 66.67];
    }

    /**
     * Always 24 values.
     *
     * @return list<float>
     */
    private static function heatmapHours(int $weekday): array
    {
        $hours = array_fill(0, self::HOURS_IN_DAY, 0.0);
        $hours[19] = 1.5;
        if ($weekday === 1) {
            $hours[12] = 0.5;
            $hours[19] = 2.0;
        }

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
