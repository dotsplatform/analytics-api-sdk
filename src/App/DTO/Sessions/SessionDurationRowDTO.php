<?php

declare(strict_types=1);

/**
 * Description of SessionDurationRowDTO.php
 *
 * Session duration of one platform ("web", "ios", "android", …) or of all platforms ("all"): sessions, their summed
 * duration, the average and how many sessions fall into each duration bucket.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Sessions;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class SessionDurationRowDTO extends DTO
{
    protected string $platform;

    protected string $label;

    protected int $sessions;

    protected int $totalSeconds;

    protected ?float $averageSeconds = null;

    protected ?string $medianBucket = null;

    /** @var array<string, int> */
    protected array $buckets = [];

    /**
     * @param  array<array-key, mixed>  $data
     * @param  list<string>  $bucketKeys
     */
    public static function fromResponse(array $data, array $bucketKeys): static
    {
        $bucketsData = ResponseField::object($data, 'buckets');
        $buckets = [];
        foreach ($bucketKeys as $bucketKey) {
            $buckets[$bucketKey] = ResponseField::int($bucketsData, $bucketKey);
        }

        return static::fromArray([
            'platform' => ResponseField::string($data, 'platform'),
            'label' => ResponseField::string($data, 'label'),
            'sessions' => ResponseField::int($data, 'sessions'),
            'totalSeconds' => ResponseField::int($data, 'total_seconds'),
            'averageSeconds' => ResponseField::nullableFloat($data, 'average_seconds'),
            'medianBucket' => ResponseField::nullableString($data, 'median_bucket'),
            'buckets' => $buckets,
        ]);
    }

    /**
     * "web", "ios", "android", another platform, or "all" for the total row.
     */
    public function getPlatform(): string
    {
        return $this->platform;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isTotal(): bool
    {
        return $this->platform === 'all';
    }

    public function getSessions(): int
    {
        return $this->sessions;
    }

    public function getTotalSeconds(): int
    {
        return $this->totalSeconds;
    }

    public function getAverageSeconds(): ?float
    {
        return $this->averageSeconds;
    }

    /**
     * The bucket key that holds the middle session (the median as a range).
     */
    public function getMedianBucket(): ?string
    {
        return $this->medianBucket;
    }

    /**
     * @return array<string, int> bucket key => sessions
     */
    public function getBuckets(): array
    {
        return $this->buckets;
    }

    public function getBucketSessions(string $bucketKey): int
    {
        return $this->buckets[$bucketKey] ?? 0;
    }

    public function getBucketPercent(string $bucketKey): ?float
    {
        if ($this->sessions === 0) {
            return null;
        }

        return $this->getBucketSessions($bucketKey) / $this->sessions * 100;
    }
}
