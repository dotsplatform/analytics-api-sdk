<?php

declare(strict_types=1);

/**
 * Description of StatsFiltersDTO.php
 *
 * The "filters" object every Read API response echoes: the account (null = all accounts), the period the service
 * used (its defaults filled in) and the platform (null = every platform). The limit and the dimension are not echoed.
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
    protected ?int $accountId = null;

    protected string $dateFrom;

    protected string $dateTo;

    protected ?string $platform = null;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'accountId' => ResponseField::nullableInt($data, 'account_id'),
            'dateFrom' => ResponseField::string($data, 'date_from'),
            'dateTo' => ResponseField::string($data, 'date_to'),
            'platform' => ResponseField::nullableString($data, 'platform'),
        ]);
    }

    /**
     * Null means all accounts.
     */
    public function getAccountId(): ?int
    {
        return $this->accountId;
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
}
