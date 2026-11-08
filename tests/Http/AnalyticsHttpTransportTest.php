<?php

declare(strict_types=1);

/**
 * Description of AnalyticsHttpTransportTest.php
 *
 * The transport: the internal gateway token and Accept header, a host with a path prefix, encoded path parameters,
 * and configuration, connection and body failures.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Tests\Http;

use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsConfigurationException;
use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsConnectionException;
use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsUnexpectedResponseException;
use Dotsplatform\AnalyticsApi\Http\RequestHeader;
use Dotsplatform\AnalyticsApi\Tests\HttpClientTestCase;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;

class AnalyticsHttpTransportTest extends HttpClientTestCase
{
    public function testAGetSendsTheTokenAndAcceptsJson(): void
    {
        $this->queueJsonResponse(['filters' => []]);

        $this->makeTransport()->getJson('api/v1/accounts/1/overview', ['date_from' => '2026-11-01']);

        $request = $this->getLastRequest();
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame(self::TOKEN, $request->getHeaderLine(RequestHeader::INTERNAL_GATEWAY_TOKEN));
        $this->assertSame('application/json', $request->getHeaderLine('Accept'));
        $this->assertSame('date_from=2026-11-01', $request->getUri()->getQuery());
    }

    public function testAHostWithAPathPrefixKeepsIt(): void
    {
        $this->queueJsonResponse(['filters' => []]);

        $this->makeTransport('https://gateway.example.test/analytics/')->getJson('api/v1/accounts/all/overview');

        $this->assertSame('/analytics/api/v1/accounts/all/overview', $this->getLastRequest()->getUri()->getPath());
        $this->assertSame('gateway.example.test', $this->getLastRequest()->getUri()->getHost());
    }

    public function testPathParametersAreEncoded(): void
    {
        $path = $this->makeTransport()->buildPath('api/v1/accounts/%s/overview', '1/2');

        $this->assertSame('api/v1/accounts/1%2F2/overview', $path);
    }

    public function testAMissingTokenFailsBeforeAnyRequest(): void
    {
        $this->expectException(AnalyticsConfigurationException::class);
        $this->expectExceptionMessage('ANALYTICS_API_GATEWAY_TOKEN');

        try {
            $this->makeTransport(self::HOST, '')->getJson('api/v1/accounts/1/overview');
        } finally {
            $this->assertSame([], $this->getRecordedRequests());
        }
    }

    public function testAMissingHostFailsBeforeAnyRequest(): void
    {
        $this->expectException(AnalyticsConfigurationException::class);
        $this->expectExceptionMessage('ANALYTICS_API_URL');

        $this->makeTransport('')->getJson('api/v1/accounts/1/overview');
    }

    public function testNoResponseIsAConnectionError(): void
    {
        $this->queueThrowable(new ConnectException('Connection refused', new Request('GET', 'api/v1/accounts/1/overview')));

        $this->expectException(AnalyticsConnectionException::class);
        $this->expectExceptionMessage('Connection refused');

        $this->makeTransport()->getJson('api/v1/accounts/1/overview');
    }

    public function testABodyThatIsNotJsonIsUnexpected(): void
    {
        $this->queueRawResponse('<html>gateway</html>', 200, 'text/html');

        $this->expectException(AnalyticsUnexpectedResponseException::class);

        $this->makeTransport()->getJson('api/v1/accounts/1/overview');
    }

    public function testAnEmptyBodyIsUnexpected(): void
    {
        $this->queueRawResponse('');

        $this->expectException(AnalyticsUnexpectedResponseException::class);

        $this->makeTransport()->getJson('api/v1/accounts/1/overview');
    }
}
