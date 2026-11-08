<?php

declare(strict_types=1);

/**
 * Description of AnalyticsHttpTransport.php
 *
 * The only place that talks HTTP to the analytics service. Every request carries X-Internal-Gateway-Token
 * (config analytics-api.token) and has a connect and a total timeout (AnalyticsHttpTimeouts). Error statuses become
 * typed exceptions (ErrorResponseMapper); a missing response, a timeout included, becomes AnalyticsConnectionException.
 *
 * The Guzzle handler stack can be passed in, so tests run against a MockHandler.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Http;

use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsConfigurationException;
use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsConnectionException;
use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsUnexpectedResponseException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class AnalyticsHttpTransport
{
    private const string METHOD_GET = 'GET';

    private const int FIRST_ERROR_STATUS = 400;

    private const string ACCEPT_JSON = 'application/json';

    private ?GuzzleClient $guzzleClient = null;

    private readonly AnalyticsHttpTimeouts $timeouts;

    public function __construct(
        private readonly string $host,
        private readonly string $token,
        ?AnalyticsHttpTimeouts $timeouts = null,
        private readonly ?HandlerStack $handlerStack = null,
    ) {
        $this->timeouts = $timeouts ?? new AnalyticsHttpTimeouts();
    }

    /**
     * Builds a path from a template and its parameters; every parameter is URL-encoded.
     */
    public function buildPath(string $template, string ...$parameters): string
    {
        $encodedParameters = [];
        foreach ($parameters as $parameter) {
            $encodedParameters[] = rawurlencode($parameter);
        }

        return vsprintf($template, $encodedParameters);
    }

    /**
     * @param  array<string, scalar|null>  $query
     * @return array<array-key, mixed>
     */
    public function getJson(string $path, array $query = []): array
    {
        $response = $this->send(self::METHOD_GET, $path, [RequestOptions::QUERY => $query]);

        return $this->decodeJson($response);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function send(string $method, string $path, array $options): ResponseInterface
    {
        $options[RequestOptions::HEADERS] = $this->buildHeaders();

        try {
            $response = $this->getGuzzleClient()->request($method, $path, $options);
        } catch (GuzzleException $exception) {
            // A connect or request timeout is a ConnectException (cURL error 28), so it lands here as well.
            throw new AnalyticsConnectionException('The analytics service could not be reached: '.$exception->getMessage(), 0, '', $exception);
        }

        if ($response->getStatusCode() >= self::FIRST_ERROR_STATUS) {
            throw (new ErrorResponseMapper())->map($response);
        }

        return $response;
    }

    /**
     * @return array<string, string>
     */
    private function buildHeaders(): array
    {
        return [
            'Accept' => self::ACCEPT_JSON,
            RequestHeader::INTERNAL_GATEWAY_TOKEN => $this->getConfiguredToken(),
        ];
    }

    private function getGuzzleClient(): GuzzleClient
    {
        if ($this->guzzleClient !== null) {
            return $this->guzzleClient;
        }

        $options = [
            'base_uri' => $this->getBaseUri(),
            RequestOptions::CONNECT_TIMEOUT => $this->timeouts->getConnectSeconds(),
            RequestOptions::TIMEOUT => $this->timeouts->getRequestSeconds(),
            // Error statuses are mapped to typed exceptions in send().
            RequestOptions::HTTP_ERRORS => false,
        ];

        if ($this->handlerStack !== null) {
            $options['handler'] = $this->handlerStack;
        }

        $this->guzzleClient = new GuzzleClient($options);

        return $this->guzzleClient;
    }

    /**
     * Paths are relative ("api/v1/…"), so a host with a path prefix keeps it.
     */
    private function getBaseUri(): string
    {
        if ($this->host === '') {
            throw new AnalyticsConfigurationException('analytics-api.host is empty: set ANALYTICS_API_URL.');
        }

        return rtrim($this->host, '/').'/';
    }

    private function getConfiguredToken(): string
    {
        if ($this->token === '') {
            throw new AnalyticsConfigurationException('analytics-api.token is empty: set ANALYTICS_API_GATEWAY_TOKEN.');
        }

        return $this->token;
    }

    /**
     * @return array<array-key, mixed>
     */
    private function decodeJson(ResponseInterface $response): array
    {
        $responseBody = (string) $response->getBody();
        $data = json_decode($responseBody, true);
        if (! is_array($data)) {
            throw new AnalyticsUnexpectedResponseException('The analytics service answered with a body that is not a JSON object.', $response->getStatusCode(), $responseBody);
        }

        return $data;
    }
}
