<?php

declare(strict_types=1);

/**
 * Description of ErrorResponseMapper.php
 *
 * Turns an error response into the typed SDK exception: 401, 404, 422 (with the field errors), 429 (with
 * Retry-After) and everything else as AnalyticsServerException (403 included).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Http;

use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsApiException;
use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsNotFoundException;
use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsRateLimitException;
use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsServerException;
use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsUnauthorizedException;
use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsValidationException;
use Psr\Http\Message\ResponseInterface;

final class ErrorResponseMapper
{
    private const int STATUS_UNAUTHORIZED = 401;

    private const int STATUS_NOT_FOUND = 404;

    private const int STATUS_UNPROCESSABLE = 422;

    private const int STATUS_TOO_MANY_REQUESTS = 429;

    public function map(ResponseInterface $response): AnalyticsApiException
    {
        $statusCode = $response->getStatusCode();
        $responseBody = (string) $response->getBody();
        $message = $this->readMessage($responseBody, $statusCode);

        if ($statusCode === self::STATUS_UNAUTHORIZED) {
            return new AnalyticsUnauthorizedException($message, $statusCode, $responseBody);
        }

        if ($statusCode === self::STATUS_NOT_FOUND) {
            return new AnalyticsNotFoundException($message, $statusCode, $responseBody);
        }

        if ($statusCode === self::STATUS_UNPROCESSABLE) {
            return new AnalyticsValidationException($message, $this->readValidationErrors($responseBody), $responseBody);
        }

        if ($statusCode === self::STATUS_TOO_MANY_REQUESTS) {
            return new AnalyticsRateLimitException($message, $this->readRetryAfterSeconds($response), $responseBody);
        }

        return new AnalyticsServerException($message, $statusCode, $responseBody);
    }

    private function readMessage(string $responseBody, int $statusCode): string
    {
        $data = $this->decode($responseBody);
        $message = $data['message'] ?? null;
        if (is_string($message) && $message !== '') {
            return $message;
        }

        return sprintf('The analytics service answered with HTTP %d.', $statusCode);
    }

    /**
     * @return array<string, list<string>>
     */
    private function readValidationErrors(string $responseBody): array
    {
        $data = $this->decode($responseBody);
        $errors = $data['errors'] ?? null;
        if (! is_array($errors)) {
            return [];
        }

        $validationErrors = [];
        foreach ($errors as $field => $fieldMessages) {
            $validationErrors[(string) $field] = $this->readFieldMessages($fieldMessages);
        }

        return $validationErrors;
    }

    /**
     * @return list<string>
     */
    private function readFieldMessages(mixed $fieldMessages): array
    {
        if (is_string($fieldMessages)) {
            return [$fieldMessages];
        }

        if (! is_array($fieldMessages)) {
            return [];
        }

        $messages = [];
        foreach ($fieldMessages as $fieldMessage) {
            if (is_string($fieldMessage)) {
                $messages[] = $fieldMessage;
            }
        }

        return $messages;
    }

    private function readRetryAfterSeconds(ResponseInterface $response): ?int
    {
        $retryAfter = $response->getHeaderLine('Retry-After');
        if (! ctype_digit($retryAfter)) {
            return null;
        }

        return (int) $retryAfter;
    }

    /**
     * @return array<array-key, mixed>
     */
    private function decode(string $responseBody): array
    {
        $data = json_decode($responseBody, true);
        if (! is_array($data)) {
            return [];
        }

        return $data;
    }
}
