<?php

declare(strict_types=1);

/**
 * Description of ErrorMappingTest.php
 *
 * Every error status becomes its typed exception: 401 (VerifyInternalGatewayToken), 404 (an account segment that is
 * not a number or "all"), 422 with the field errors (StatsRequest), 429 with Retry-After, and the rest as
 * AnalyticsServerException. Bodies are the standard Laravel JSON shapes.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Tests\Http;

use Dotsplatform\AnalyticsApi\Clients\Stubs\StubResponses;
use Dotsplatform\AnalyticsApi\DTO\Requests\StatsRequestDTO;
use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsApiException;
use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsNotFoundException;
use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsRateLimitException;
use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsServerException;
use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsUnauthorizedException;
use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsUnexpectedResponseException;
use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsValidationException;
use Dotsplatform\AnalyticsApi\Tests\HttpClientTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ErrorMappingTest extends HttpClientTestCase
{
    /**
     * @return iterable<string, array{int, class-string<AnalyticsApiException>, string}>
     */
    public static function errorStatuses(): iterable
    {
        yield 'unauthorized' => [401, AnalyticsUnauthorizedException::class, 'Internal gateway token is required'];
        yield 'forbidden' => [403, AnalyticsServerException::class, 'Forbidden'];
        yield 'not found' => [404, AnalyticsNotFoundException::class, 'Not Found'];
        yield 'server error' => [500, AnalyticsServerException::class, 'Server Error'];
        yield 'bad gateway' => [502, AnalyticsServerException::class, 'Bad Gateway'];
    }

    /**
     * @param  class-string<AnalyticsApiException>  $exceptionClass
     */
    #[DataProvider('errorStatuses')]
    public function testAnErrorStatusBecomesItsTypedException(int $statusCode, string $exceptionClass, string $message): void
    {
        $this->queueJsonResponse(['message' => $message], $statusCode);

        $exception = $this->catchException(fn () => $this->makeClient()->stats()->getOverview($this->makeRequest()));

        $this->assertInstanceOf($exceptionClass, $exception);
        $this->assertSame($statusCode, $exception->getStatusCode());
        $this->assertSame($message, $exception->getMessage());
        $this->assertStringContainsString($message, $exception->getResponseBody());
    }

    public function testValidationErrorsAreKeptByField(): void
    {
        $this->queueJsonResponse([
            'message' => 'date_to must not be before date_from.',
            'errors' => [
                'date_to' => ['date_to must not be before date_from.'],
            ],
        ], 422);

        $exception = $this->catchException(fn () => $this->makeClient()->stats()->getOverview($this->makeRequest()));

        $this->assertInstanceOf(AnalyticsValidationException::class, $exception);
        $this->assertSame(422, $exception->getStatusCode());
        $this->assertSame(['date_to must not be before date_from.'], $exception->getErrorsFor('date_to'));
        $this->assertSame([], $exception->getErrorsFor('platform'));
        $this->assertCount(1, $exception->getErrors());
    }

    public function testTooManyRequestsKeepsRetryAfter(): void
    {
        $this->queueJsonResponse(['message' => 'Too Many Attempts.'], 429, ['Retry-After' => '42']);

        $exception = $this->catchException(fn () => $this->makeClient()->stats()->getEventStats($this->makeRequest()));

        $this->assertInstanceOf(AnalyticsRateLimitException::class, $exception);
        $this->assertSame(429, $exception->getStatusCode());
        $this->assertSame(42, $exception->getRetryAfterSeconds());
    }

    public function testAnErrorWithoutAJsonBodyStillHasAMessage(): void
    {
        $this->queueRawResponse('<html>Bad gateway</html>', 502, 'text/html');

        $exception = $this->catchException(fn () => $this->makeClient()->stats()->getTopItems($this->makeRequest()));

        $this->assertInstanceOf(AnalyticsServerException::class, $exception);
        $this->assertSame('The analytics service answered with HTTP 502.', $exception->getMessage());
    }

    public function testAFieldOfAnotherTypeIsAnUnexpectedResponse(): void
    {
        $response = StubResponses::activeUsers($this->makeRequest());
        $response['daily'] = [['date' => '2026-11-02', 'dau' => '12', 'wau' => 40, 'mau' => 90]];
        $this->queueJsonResponse($response);

        $this->expectException(AnalyticsUnexpectedResponseException::class);
        $this->expectExceptionMessage('"dau"');

        $this->makeClient()->stats()->getActiveUsers($this->makeRequest());
    }

    public function testAMissingListIsAnUnexpectedResponse(): void
    {
        $this->queueJsonResponse(['filters' => StubResponses::filters($this->makeRequest())]);

        $this->expectException(AnalyticsUnexpectedResponseException::class);
        $this->expectExceptionMessage('"pages"');

        $this->makeClient()->stats()->getTopPages($this->makeRequest());
    }

    private function makeRequest(): StatsRequestDTO
    {
        return StatsRequestDTO::forAccount(StubResponses::ACCOUNT_TOKEN);
    }

    /**
     * @param  callable(): mixed  $call
     */
    private function catchException(callable $call): AnalyticsApiException
    {
        try {
            $call();
        } catch (AnalyticsApiException $exception) {
            return $exception;
        }

        self::fail('No AnalyticsApiException was thrown.');
    }
}
