<?php

declare(strict_types=1);

/**
 * Description of RetentionCohortDTO.php
 *
 * One cohort of the retention table: its first day, an English label ("September 2026", "Week of 19 Oct"), its size and one percent per
 * offset of the table (null = that period has not happened yet).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Retention;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class RetentionCohortDTO extends DTO
{
    protected string $startDate;

    protected string $label;

    protected int $size;

    /** @var list<float|null> */
    protected array $percents = [];

    protected bool $mixed;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'startDate' => ResponseField::string($data, 'start_date'),
            'label' => ResponseField::string($data, 'label'),
            'size' => ResponseField::int($data, 'size'),
            'percents' => ResponseField::listOfNullableFloats($data, 'percents'),
            'mixed' => ResponseField::bool($data, 'is_mixed'),
        ]);
    }

    /**
     * The first day of the cohort week or month, Y-m-d.
     */
    public function getStartDate(): string
    {
        return $this->startDate;
    }

    /**
     * The English name of the cohort; format getStartDate() yourself to translate it.
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Customers or devices in the cohort.
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * One per offset of the table, percent of the cohort size; null = that period has not happened yet.
     *
     * @return list<float|null>
     */
    public function getPercents(): array
    {
        return $this->percents;
    }

    /**
     * True for the first period of the data: it also holds everyone who was a customer before it.
     */
    public function isMixed(): bool
    {
        return $this->mixed;
    }
}
