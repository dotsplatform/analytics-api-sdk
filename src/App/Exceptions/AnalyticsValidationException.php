<?php

declare(strict_types=1);

/**
 * Description of AnalyticsValidationException.php
 *
 * 422: the query string did not pass validation. The errors are keyed by field, Laravel style:
 * ["date_to" => ["date_to must not be before date_from."]].
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Exceptions;

class AnalyticsValidationException extends AnalyticsApiException
{
    /**
     * @param  array<string, list<string>>  $errors
     */
    public function __construct(
        string $message,
        private readonly array $errors,
        string $responseBody = '',
    ) {
        parent::__construct($message, 422, $responseBody);
    }

    /**
     * @return array<string, list<string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return list<string>
     */
    public function getErrorsFor(string $field): array
    {
        return $this->errors[$field] ?? [];
    }
}
