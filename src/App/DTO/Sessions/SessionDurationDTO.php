<?php

declare(strict_types=1);

/**
 * Description of SessionDurationDTO.php
 *
 * GET session-duration: how long sessions last per platform. A session's duration is its last event minus its first
 * event (a lower bound: the time after the last event is unknown; a one-page web visit counts as 0 s), each session
 * capped at getMaxSessionSeconds(). Buckets in order: under_10s, from_10s_to_1m, from_1m_to_3m, from_3m_to_10m,
 * from_10m_to_30m, over_30m.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Sessions;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\DTO\Common\StatsFiltersDTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class SessionDurationDTO extends DTO
{
    protected StatsFiltersDTO $filters;

    /** @var list<string> */
    protected array $bucketKeys = [];

    protected int $maxSessionSeconds;

    /** @var list<SessionDurationRowDTO> */
    protected array $rows = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        $sessionDuration = ResponseField::object($data, 'session_duration');
        $bucketKeys = ResponseField::listOfStrings($sessionDuration, 'buckets');

        $rows = [];
        foreach (ResponseField::listOfObjects($sessionDuration, 'rows') as $row) {
            $rows[] = SessionDurationRowDTO::fromResponse($row, $bucketKeys);
        }

        return static::fromArray([
            'filters' => StatsFiltersDTO::fromResponse(ResponseField::object($data, 'filters')),
            'bucketKeys' => $bucketKeys,
            'maxSessionSeconds' => ResponseField::int($sessionDuration, 'max_session_seconds'),
            'rows' => $rows,
        ]);
    }

    public function getFilters(): StatsFiltersDTO
    {
        return $this->filters;
    }

    /**
     * @return list<string>
     */
    public function getBucketKeys(): array
    {
        return $this->bucketKeys;
    }

    public function getMaxSessionSeconds(): int
    {
        return $this->maxSessionSeconds;
    }

    /**
     * Per platform (web, ios, android, then others), the "all" row last.
     *
     * @return list<SessionDurationRowDTO>
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    public function isEmpty(): bool
    {
        return $this->rows === [];
    }
}
