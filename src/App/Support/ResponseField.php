<?php

declare(strict_types=1);

/**
 * Description of ResponseField.php
 *
 * Typed reads from a decoded JSON response. A field with another type than documented throws
 * AnalyticsUnexpectedResponseException, so a changed API fails loudly instead of filling DTOs with wrong values.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Support;

use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsUnexpectedResponseException;

final class ResponseField
{
    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function string(array $data, string $key): string
    {
        $value = $data[$key] ?? null;
        if (! is_string($value)) {
            throw self::unexpectedType($key, 'string');
        }

        return $value;
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function nullableString(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;
        if ($value === null) {
            return null;
        }

        if (! is_string($value)) {
            throw self::unexpectedType($key, 'string or null');
        }

        return $value;
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function int(array $data, string $key): int
    {
        $value = $data[$key] ?? null;
        if (! is_int($value)) {
            throw self::unexpectedType($key, 'integer');
        }

        return $value;
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function nullableInt(array $data, string $key): ?int
    {
        $value = $data[$key] ?? null;
        if ($value === null) {
            return null;
        }

        if (! is_int($value)) {
            throw self::unexpectedType($key, 'integer or null');
        }

        return $value;
    }

    /**
     * A JSON number as a float. Whole numbers decode as int (PHP's json_encode writes 0.0 as 0), so both are accepted.
     *
     * @param  array<array-key, mixed>  $data
     */
    public static function float(array $data, string $key): float
    {
        $value = $data[$key] ?? null;
        if (is_int($value)) {
            return (float) $value;
        }

        if (is_float($value)) {
            return $value;
        }

        throw self::unexpectedType($key, 'number');
    }

    /**
     * A JSON number as a float, or null. Whole numbers are accepted, like float().
     *
     * @param  array<array-key, mixed>  $data
     */
    public static function nullableFloat(array $data, string $key): ?float
    {
        $value = $data[$key] ?? null;
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return (float) $value;
        }

        if (is_float($value)) {
            return $value;
        }

        throw self::unexpectedType($key, 'number or null');
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function bool(array $data, string $key): bool
    {
        $value = $data[$key] ?? null;
        if (! is_bool($value)) {
            throw self::unexpectedType($key, 'boolean');
        }

        return $value;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<string>
     */
    public static function listOfStrings(array $data, string $key): array
    {
        $strings = [];
        foreach (self::arrayField($data, $key) as $item) {
            if (! is_string($item)) {
                throw self::unexpectedType($key.'[]', 'string');
            }

            $strings[] = $item;
        }

        return $strings;
    }

    /**
     * A JSON array of numbers as floats. Whole numbers are accepted, like float().
     *
     * @param  array<array-key, mixed>  $data
     * @return list<float>
     */
    public static function listOfFloats(array $data, string $key): array
    {
        $floats = [];
        foreach (self::arrayField($data, $key) as $item) {
            if (is_int($item)) {
                $floats[] = (float) $item;

                continue;
            }

            if (! is_float($item)) {
                throw self::unexpectedType($key.'[]', 'number');
            }

            $floats[] = $item;
        }

        return $floats;
    }

    /**
     * A JSON array of numbers or nulls, the numbers as floats. Whole numbers are accepted, like float().
     *
     * @param  array<array-key, mixed>  $data
     * @return list<float|null>
     */
    public static function listOfNullableFloats(array $data, string $key): array
    {
        $floats = [];
        foreach (self::arrayField($data, $key) as $item) {
            if ($item === null) {
                $floats[] = null;

                continue;
            }

            if (is_int($item)) {
                $floats[] = (float) $item;

                continue;
            }

            if (! is_float($item)) {
                throw self::unexpectedType($key.'[]', 'number or null');
            }

            $floats[] = $item;
        }

        return $floats;
    }

    /**
     * A JSON object as a PHP array (an empty object decodes to an empty array).
     *
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    public static function object(array $data, string $key): array
    {
        $value = $data[$key] ?? null;
        if (! is_array($value)) {
            throw self::unexpectedType($key, 'object');
        }

        return $value;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<array<array-key, mixed>>
     */
    public static function listOfObjects(array $data, string $key): array
    {
        $value = $data[$key] ?? null;
        if (! is_array($value)) {
            throw self::unexpectedType($key, 'array');
        }

        $items = [];
        foreach ($value as $item) {
            if (! is_array($item)) {
                throw self::unexpectedType($key.'[]', 'object');
            }

            $items[] = $item;
        }

        return $items;
    }

    /**
     * The JSON array of a list field, before its items are checked.
     *
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    private static function arrayField(array $data, string $key): array
    {
        $value = $data[$key] ?? null;
        if (! is_array($value)) {
            throw self::unexpectedType($key, 'array');
        }

        return $value;
    }

    private static function unexpectedType(string $key, string $expectedType): AnalyticsUnexpectedResponseException
    {
        return new AnalyticsUnexpectedResponseException(sprintf('The analytics service response field "%s" is not a %s.', $key, $expectedType));
    }
}
