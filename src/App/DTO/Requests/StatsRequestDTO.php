<?php

declare(strict_types=1);

/**
 * Description of StatsRequestDTO.php
 *
 * The filters of every Read API call: the account (an id, or null for all accounts), the period (Y-m-d, both ends
 * included, at most 366 days), the platform and the top-list limit (1–500). The dimension is used only by the
 * platform breakdown. A field left null is not sent, so the service default applies: the last 30 days (Kyiv time),
 * every platform, limit 50, dimension "os".
 *
 *     StatsRequestDTO::forAccount(42, '2026-11-01', '2026-11-07');
 *     StatsRequestDTO::forAllAccounts()->copy(['platform' => Platform::IOS, 'limit' => 10]);
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Requests;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Enums\BreakdownDimension;
use Dotsplatform\AnalyticsApi\Enums\Platform;

class StatsRequestDTO extends DTO
{
    /**
     * The {account} path segment that means "every account, events without a resolved account included".
     */
    public const string ALL_ACCOUNTS = 'all';

    protected ?int $accountId = null;

    protected ?string $dateFrom = null;

    protected ?string $dateTo = null;

    protected ?Platform $platform = null;

    protected ?int $limit = null;

    protected ?BreakdownDimension $dimension = null;

    public static function forAccount(int $accountId, ?string $dateFrom = null, ?string $dateTo = null): static
    {
        return static::fromArray([
            'accountId' => $accountId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public static function forAllAccounts(?string $dateFrom = null, ?string $dateTo = null): static
    {
        return static::fromArray([
            'accountId' => null,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    /**
     * Null means all accounts.
     */
    public function getAccountId(): ?int
    {
        return $this->accountId;
    }

    public function isAllAccounts(): bool
    {
        return $this->accountId === null;
    }

    public function getDateFrom(): ?string
    {
        return $this->dateFrom;
    }

    public function getDateTo(): ?string
    {
        return $this->dateTo;
    }

    public function getPlatform(): ?Platform
    {
        return $this->platform;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getDimension(): ?BreakdownDimension
    {
        return $this->dimension;
    }

    /**
     * The {account} segment of the path: the account id, or "all".
     */
    public function getAccountPathSegment(): string
    {
        if ($this->accountId === null) {
            return self::ALL_ACCOUNTS;
        }

        return (string) $this->accountId;
    }

    /**
     * The query string of every endpoint except the platform breakdown. Null fields are left out.
     *
     * @return array<string, string|int>
     */
    public function toQuery(): array
    {
        $query = [];

        if ($this->dateFrom !== null) {
            $query['date_from'] = $this->dateFrom;
        }

        if ($this->dateTo !== null) {
            $query['date_to'] = $this->dateTo;
        }

        if ($this->platform !== null) {
            $query['platform'] = $this->platform->value;
        }

        if ($this->limit !== null) {
            $query['limit'] = $this->limit;
        }

        return $query;
    }

    /**
     * The query string of the platform breakdown: toQuery() plus the dimension when it is set.
     *
     * @return array<string, string|int>
     */
    public function toPlatformBreakdownQuery(): array
    {
        $query = $this->toQuery();

        if ($this->dimension !== null) {
            $query['dimension'] = $this->dimension->value;
        }

        return $query;
    }
}
