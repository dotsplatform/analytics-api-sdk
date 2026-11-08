<?php

declare(strict_types=1);

/**
 * Description of TimeoutsTest.php
 *
 * Every call has a 2 s connect and an 8 s total timeout, both configurable; a timeout surfaces as
 * AnalyticsConnectionException.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Tests\Http;

use Dotsplatform\AnalyticsApi\Clients\AnalyticsHttpClient;
use Dotsplatform\AnalyticsApi\Clients\Stubs\StubResponses;
use Dotsplatform\AnalyticsApi\DTO\Requests\StatsRequestDTO;
use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsConnectionException;
use Dotsplatform\AnalyticsApi\Http\AnalyticsHttpTimeouts;
use Dotsplatform\AnalyticsApi\Http\AnalyticsHttpTransport;
use Dotsplatform\AnalyticsApi\Tests\HttpClientTestCase;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class TimeoutsTest extends HttpClientTestCase
{
    /** @var list<array<string, mixed>> */
    private array $capturedOptions = [];

    public function testACallHasTheDefaultConnectAndRequestTimeouts(): void
    {
        $this->queueHandler($this->makeCapturingHandler());

        $this->makeClient()->stats()->getTopPages($this->makeRequest());

        $this->assertSame(2.0, $this->capturedOptions[0]['connect_timeout'] ?? null);
        $this->assertSame(8.0, $this->capturedOptions[0]['timeout'] ?? null);
    }

    public function testTimeoutsAreConfigurable(): void
    {
        $mockHandler = new MockHandler([$this->makeCapturingHandler()]);
        $transport = new AnalyticsHttpTransport(self::HOST, self::TOKEN, new AnalyticsHttpTimeouts(1.5, 4.0), HandlerStack::create($mockHandler));

        (new AnalyticsHttpClient($transport))->stats()->getTopPages($this->makeRequest());

        $this->assertSame(1.5, $this->capturedOptions[0]['connect_timeout'] ?? null);
        $this->assertSame(4.0, $this->capturedOptions[0]['timeout'] ?? null);
    }

    public function testATimeoutIsAConnectionError(): void
    {
        $this->queueHandler(function (RequestInterface $request): never {
            throw new ConnectException('cURL error 28: Operation timed out after 8001 milliseconds with 0 bytes received', $request);
        });

        $this->expectException(AnalyticsConnectionException::class);
        $this->expectExceptionMessage('Operation timed out');

        $this->makeClient()->stats()->getTopPages($this->makeRequest());
    }

    private function makeRequest(): StatsRequestDTO
    {
        return StatsRequestDTO::forAccount(StubResponses::ACCOUNT_ID);
    }

    /**
     * @return callable(RequestInterface, array<string, mixed>): Response
     */
    private function makeCapturingHandler(): callable
    {
        $body = json_encode(StubResponses::topPages($this->makeRequest()), JSON_THROW_ON_ERROR);

        return function (RequestInterface $request, array $options) use ($body): Response {
            $this->capturedOptions[] = $options;

            return new Response(200, [], $body);
        };
    }
}
