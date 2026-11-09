<?php

declare(strict_types=1);

/**
 * Description of ReadApiResponseShapeTest.php
 *
 * The funnel, heatmap, sources and retention DTOs reject a response field of another type than documented with
 * AnalyticsUnexpectedResponseException, so a changed API fails loudly. Each case breaks one field of a stub response.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Tests\DTO;

use Closure;
use Dotsplatform\AnalyticsApi\Clients\Stubs\StubResponses;
use Dotsplatform\AnalyticsApi\DTO\Funnel\FunnelDTO;
use Dotsplatform\AnalyticsApi\DTO\Heatmap\HeatmapDTO;
use Dotsplatform\AnalyticsApi\DTO\Requests\StatsRequestDTO;
use Dotsplatform\AnalyticsApi\DTO\Retention\RetentionDTO;
use Dotsplatform\AnalyticsApi\DTO\Sources\SourcesDTO;
use Dotsplatform\AnalyticsApi\Enums\RetentionView;
use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsUnexpectedResponseException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ReadApiResponseShapeTest extends TestCase
{
    /**
     * Each case: the response to parse and the field name the exception message must mention.
     *
     * @return iterable<string, array{Closure(): mixed, string}>
     */
    public static function brokenResponses(): iterable
    {
        yield 'funnel step percent as a string' => [static function (): FunnelDTO {
            $response = StubResponses::funnel(self::makeRequest());
            $response['steps'] = [['key' => 'sessions', 'label' => 'Visit', 'sessions' => 1, 'percent_of_previous' => null, 'percent_of_start' => '100', 'optional' => false]];

            return FunnelDTO::fromResponse($response);
        }, 'percent_of_start'];

        yield 'funnel step optional as an integer' => [static function (): FunnelDTO {
            $response = StubResponses::funnel(self::makeRequest());
            $response['checkout_steps'] = [['key' => 'checkouts', 'label' => 'Checkout opened', 'sessions' => 1, 'percent_of_previous' => null, 'percent_of_start' => 100.0, 'optional' => 0]];

            return FunnelDTO::fromResponse($response);
        }, 'optional'];

        yield 'funnel without orders' => [static function (): FunnelDTO {
            $response = StubResponses::funnel(self::makeRequest());
            unset($response['orders']);

            return FunnelDTO::fromResponse($response);
        }, 'orders'];

        yield 'funnel tracking coverage as a string' => [static function (): FunnelDTO {
            $response = StubResponses::funnel(self::makeRequest());
            $response['tracking'] = ['livesite_orders' => 3, 'matched_orders' => 2, 'coverage_percent' => '66.67'];

            return FunnelDTO::fromResponse($response);
        }, 'coverage_percent'];

        yield 'funnel average order as a float' => [static function (): FunnelDTO {
            $response = StubResponses::funnel(self::makeRequest());
            $response['revenue'] = ['tracked_cents' => 1, 'completed_cents' => 1, 'average_order_cents' => 0.5];

            return FunnelDTO::fromResponse($response);
        }, 'average_order_cents'];

        yield 'heatmap hour as a string' => [static function (): HeatmapDTO {
            $response = StubResponses::heatmap(self::makeRequest());
            $response['weekdays'] = [['weekday' => 1, 'hours' => [0.0, '1.5']]];

            return HeatmapDTO::fromResponse($response);
        }, 'hours[]'];

        yield 'heatmap hour as null' => [static function (): HeatmapDTO {
            $response = StubResponses::heatmap(self::makeRequest());
            $response['weekdays'] = [['weekday' => 1, 'hours' => [null]]];

            return HeatmapDTO::fromResponse($response);
        }, 'hours[]'];

        yield 'heatmap hours as a string' => [static function (): HeatmapDTO {
            $response = StubResponses::heatmap(self::makeRequest());
            $response['weekdays'] = [['weekday' => 1, 'hours' => 'none']];

            return HeatmapDTO::fromResponse($response);
        }, 'hours'];

        yield 'source sessions as a string' => [static function (): SourcesDTO {
            $response = StubResponses::sources(self::makeRequest());
            $response['sources'] = [['name' => 'Instagram', 'kind' => 'paid', 'sessions' => '40', 'ordering_sessions' => 4, 'conversion_percent' => 10.0]];

            return SourcesDTO::fromResponse($response);
        }, 'sessions'];

        yield 'campaign without a source name' => [static function (): SourcesDTO {
            $response = StubResponses::sources(self::makeRequest());
            $response['campaigns'] = [['name' => 'autumn', 'sessions' => 40, 'ordering_sessions' => 4, 'conversion_percent' => 10.0]];

            return SourcesDTO::fromResponse($response);
        }, 'kind'];

        yield 'retention offset as a number' => [static function (): RetentionDTO {
            $response = StubResponses::retention(self::makeRequest());
            $response['offsets'] = [1, 2, 3];

            return RetentionDTO::fromResponse($response);
        }, 'offsets[]'];

        yield 'retention percent as a string' => [static function (): RetentionDTO {
            $response = StubResponses::retention(self::makeRequest(), RetentionView::DEVICES_RETURNING);
            $response['cohorts'] = [['start_date' => '2026-10-19', 'label' => 'Week of 19 Oct', 'size' => 1, 'percents' => ['30'], 'is_mixed' => false]];

            return RetentionDTO::fromResponse($response);
        }, 'percents[]'];

        yield 'retention is_mixed as a string' => [static function (): RetentionDTO {
            $response = StubResponses::retention(self::makeRequest());
            $response['cohorts'] = [['start_date' => '2026-09-01', 'label' => 'September 2026', 'size' => 1, 'percents' => [], 'is_mixed' => 'false']];

            return RetentionDTO::fromResponse($response);
        }, 'is_mixed'];

        yield 'retention with filters instead of the account' => [static function (): RetentionDTO {
            $response = StubResponses::retention(self::makeRequest());
            unset($response['account']);
            $response['filters'] = StubResponses::filters(self::makeRequest());

            return RetentionDTO::fromResponse($response);
        }, 'account'];
    }

    /**
     * @param  Closure(): mixed  $parseResponse
     */
    #[DataProvider('brokenResponses')]
    public function testAFieldOfAnotherTypeIsAnUnexpectedResponse(Closure $parseResponse, string $fieldName): void
    {
        $this->expectException(AnalyticsUnexpectedResponseException::class);
        $this->expectExceptionMessage('"'.$fieldName.'"');

        $parseResponse();
    }

    private static function makeRequest(): StatsRequestDTO
    {
        return StatsRequestDTO::forAccount(StubResponses::ACCOUNT_TOKEN);
    }
}
