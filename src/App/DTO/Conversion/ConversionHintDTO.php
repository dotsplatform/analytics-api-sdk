<?php

declare(strict_types=1);

/**
 * Description of ConversionHintDTO.php
 *
 * One rule-based suggestion of GET conversion: a code, the channel it is about and its numbers, so the caller writes
 * the text in its own language. Values per code:
 * - website_checkout_gap: website_rate, apps_rate (checkout → order, 0–1), checkout_sessions, extra_orders;
 * - conversion_change: rate, previous_rate (orders / visits, 0–1);
 * - weakest_day: date ('Y-m-d'), rate, period_rate (orders / visits, 0–1).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Conversion;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsUnexpectedResponseException;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class ConversionHintDTO extends DTO
{
    public const string WEBSITE_CHECKOUT_GAP = 'website_checkout_gap';

    public const string CONVERSION_CHANGE = 'conversion_change';

    public const string WEAKEST_DAY = 'weakest_day';

    protected string $code;

    protected string $channel;

    /** @var array<string, int|float|string> */
    protected array $values = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'code' => ResponseField::string($data, 'code'),
            'channel' => ResponseField::string($data, 'channel'),
            'values' => self::valuesFromResponse(ResponseField::object($data, 'values')),
        ]);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * web, ios, android or all.
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @return array<string, int|float|string>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * A number value as a float (rates, counts); null when the hint has no such number.
     */
    public function getNumberValue(string $key): ?float
    {
        $value = $this->values[$key] ?? null;
        if (is_int($value)) {
            return (float) $value;
        }

        if (is_float($value)) {
            return $value;
        }

        return null;
    }

    /**
     * A text value (the date of weakest_day); null when the hint has no such text.
     */
    public function getStringValue(string $key): ?string
    {
        $value = $this->values[$key] ?? null;
        if (is_string($value)) {
            return $value;
        }

        return null;
    }

    /**
     * @param  array<array-key, mixed>  $responseValues
     * @return array<string, int|float|string>
     */
    private static function valuesFromResponse(array $responseValues): array
    {
        $values = [];
        foreach ($responseValues as $key => $value) {
            $isNumberOrText = is_int($value) || is_float($value) || is_string($value);
            if (! $isNumberOrText) {
                throw new AnalyticsUnexpectedResponseException(sprintf('The analytics service response field "values.%s" is not a number or string.', $key));
            }

            $values[(string) $key] = $value;
        }

        return $values;
    }
}
