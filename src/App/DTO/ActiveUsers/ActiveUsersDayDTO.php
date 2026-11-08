<?php

declare(strict_types=1);

/**
 * Description of ActiveUsersDayDTO.php
 *
 * One day of the active users series: devices active that day (DAU), in the 7 days (WAU) and 30 days (MAU) ending
 * that day. For all accounts the per-account numbers are summed.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\ActiveUsers;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class ActiveUsersDayDTO extends DTO
{
    protected string $date;

    protected int $dau;

    protected int $wau;

    protected int $mau;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'date' => ResponseField::string($data, 'date'),
            'dau' => ResponseField::int($data, 'dau'),
            'wau' => ResponseField::int($data, 'wau'),
            'mau' => ResponseField::int($data, 'mau'),
        ]);
    }

    /**
     * Y-m-d.
     */
    public function getDate(): string
    {
        return $this->date;
    }

    public function getDau(): int
    {
        return $this->dau;
    }

    public function getWau(): int
    {
        return $this->wau;
    }

    public function getMau(): int
    {
        return $this->mau;
    }
}
