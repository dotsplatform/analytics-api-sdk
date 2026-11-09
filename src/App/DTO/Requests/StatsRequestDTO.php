<?php

declare(strict_types=1);

/**
 * Description of StatsRequestDTO.php
 *
 * The filters of every Read API call: the account (its livesite token, a uuid, or null for all accounts), the period
 * (Y-m-d, both ends included, at most 366 days), the platform and the top-list limit (1–500). The dimension is used
 * only by the platform breakdown. A field left null is not sent, so the service default applies: the last 30 days
 * (Kyiv time), every platform, limit 50, dimension "os". A token that is not a uuid is rejected here; a uuid the
 * service does not know answers 404 (AnalyticsNotFoundException).
 *
 *     StatsRequestDTO::forAccount($account->token, '2026-11-01', '2026-11-07');
 *     StatsRequestDTO::forAllAccounts()->copy(['platform' => Platform::IOS, 'limit' => 10]);
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Requests;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Enums\BreakdownDimension;
use Dotsplatform\AnalyticsApi\Enums\Platform;
use InvalidArgumentException;

class StatsRequestDTO extends DTO
{
    /**
     * The {account} path segment that means "every account, events without a resolved account included".
     */
    public const string ALL_ACCOUNTS = 'all';

    private const string UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    protected ?string $accountToken = null;

    protected ?string $dateFrom = null;

    protected ?string $dateTo = null;

    protected ?Platform $platform = null;

    protected ?int $limit = null;

    protected ?BreakdownDimension $dimension = null;

    /**
     * @param  string  $accountToken  the livesite account token (accounts.token, a uuid)
     *
     * @throws InvalidArgumentException when the token is not a uuid
     */
    public static function forAccount(string $accountToken, ?string $dateFrom = null, ?string $dateTo = null): static
    {
        return static::fromArray([
            'accountToken' => $accountToken,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public static function forAllAccounts(?string $dateFrom = null, ?string $dateTo = null): static
    {
        return static::fromArray([
            'accountToken' => null,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    /**
     * The livesite account token; null means all accounts.
     */
    public function getAccountToken(): ?string
    {
        return $this->accountToken;
    }

    public function isAllAccounts(): bool
    {
        return $this->accountToken === null;
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
     * The {account} segment of the path: the account token (lower case), or "all".
     */
    public function getAccountPathSegment(): string
    {
        if ($this->accountToken === null) {
            return self::ALL_ACCOUNTS;
        }

        return strtolower($this->accountToken);
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

    /**
     * Runs for every way of building the DTO (forAccount, fromArray, copy), so an invalid token never reaches the path.
     *
     * @param  array<array-key, mixed>  $data
     */
    protected function assertConstructDataIsValid(array $data): void
    {
        $accountToken = $data['accountToken'] ?? null;
        if ($accountToken === null) {
            return;
        }

        if (! is_string($accountToken)) {
            throw new InvalidArgumentException('The account token must be a string (a uuid) or null (all accounts).');
        }

        if (preg_match(self::UUID_PATTERN, $accountToken) !== 1) {
            throw new InvalidArgumentException(sprintf('The account token must be a uuid, "%s" given.', $accountToken));
        }
    }
}
