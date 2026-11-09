<?php

declare(strict_types=1);

/**
 * Description of StatsFiltersDTO.php
 *
 * The "filters" object every Read API response echoes: the account (null = all accounts), the period the service
 * used (its defaults filled in), the platform (null = every platform) and the top-list limit. The dimension is not
 * echoed here; the platform breakdown returns it next to the rows.
 * The platform is the raw string, so a platform this SDK version does not know never breaks a response.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Common;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class StatsFiltersDTO extends DTO
{
    protected ?string $accountToken = null;

    protected string $dateFrom;

    protected string $dateTo;

    protected ?string $platform = null;

    protected int $limit;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'accountToken' => self::readAccountToken($data),
            'dateFrom' => ResponseField::string($data, 'date_from'),
            'dateTo' => ResponseField::string($data, 'date_to'),
            'platform' => ResponseField::nullableString($data, 'platform'),
            'limit' => ResponseField::int($data, 'limit'),
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
     * The service echoes "account": the token, or "all".
     *
     * @param  array<array-key, mixed>  $data
     */
    private static function readAccountToken(array $data): ?string
    {
        $account = ResponseField::nullableString($data, 'account');
        if ($account === null || $account === 'all') {
            return null;
        }

        return $account;
    }

    /**
     * The first day, Y-m-d.
     */
    public function getDateFrom(): string
    {
        return $this->dateFrom;
    }

    /**
     * The last day, Y-m-d (included).
     */
    public function getDateTo(): string
    {
        return $this->dateTo;
    }

    /**
     * Null means every platform.
     */
    public function getPlatform(): ?string
    {
        return $this->platform;
    }

    /**
     * The size of the top lists (the service default 50 when the request sent none).
     */
    public function getLimit(): int
    {
        return $this->limit;
    }
}
