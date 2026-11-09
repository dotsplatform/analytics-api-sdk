<?php

declare(strict_types=1);

/**
 * Description of RetentionDTO.php
 *
 * GET retention: a cohort table, oldest cohort first. repeat_buyers has monthly cohorts and offsets "M1", "M2", …;
 * the device views have weekly cohorts and offsets "W1".."W8". It ignores the request period, so the response echoes
 * only the account, not the filters.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Retention;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\DTO\Requests\StatsRequestDTO;
use Dotsplatform\AnalyticsApi\Enums\RetentionView;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class RetentionDTO extends DTO
{
    public const string PERIOD_MONTH = 'month';

    public const string PERIOD_WEEK = 'week';

    protected ?string $accountToken = null;

    protected string $view;

    protected string $period;

    /** @var list<string> */
    protected array $offsets = [];

    /** @var list<RetentionCohortDTO> */
    protected array $cohorts = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'accountToken' => self::readAccountToken($data),
            'view' => ResponseField::string($data, 'view'),
            'period' => ResponseField::string($data, 'period'),
            'offsets' => ResponseField::listOfStrings($data, 'offsets'),
            'cohorts' => self::readCohorts($data),
        ]);
    }

    /**
     * The livesite account token the request used; null means all accounts.
     */
    public function getAccountToken(): ?string
    {
        return $this->accountToken;
    }

    /**
     * The raw view of the table ("repeat_buyers" when the request sent none).
     */
    public function getViewValue(): string
    {
        return $this->view;
    }

    /**
     * The view, or null for one this SDK version does not know.
     */
    public function getView(): ?RetentionView
    {
        return RetentionView::tryFrom($this->view);
    }

    /**
     * PERIOD_MONTH or PERIOD_WEEK: the length of a cohort and of an offset.
     */
    public function getPeriod(): string
    {
        return $this->period;
    }

    /**
     * The column headers: "M1", "M2", … or "W1".."W8".
     *
     * @return list<string>
     */
    public function getOffsets(): array
    {
        return $this->offsets;
    }

    /**
     * Oldest cohort first.
     *
     * @return list<RetentionCohortDTO>
     */
    public function getCohorts(): array
    {
        return $this->cohorts;
    }

    /**
     * The service echoes "account": the token, or "all".
     *
     * @param  array<array-key, mixed>  $data
     */
    private static function readAccountToken(array $data): ?string
    {
        $account = ResponseField::string($data, 'account');
        if ($account === StatsRequestDTO::ALL_ACCOUNTS) {
            return null;
        }

        return $account;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<RetentionCohortDTO>
     */
    private static function readCohorts(array $data): array
    {
        $cohorts = [];
        foreach (ResponseField::listOfObjects($data, 'cohorts') as $cohortData) {
            $cohorts[] = RetentionCohortDTO::fromResponse($cohortData);
        }

        return $cohorts;
    }
}
