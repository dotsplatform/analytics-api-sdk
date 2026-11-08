<?php

declare(strict_types=1);

/**
 * Description of ActiveUsersDTO.php
 *
 * GET active-users: DAU / WAU / MAU per day. Without a platform filter the numbers cover every platform.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\ActiveUsers;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\DTO\Common\StatsFiltersDTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class ActiveUsersDTO extends DTO
{
    protected StatsFiltersDTO $filters;

    /** @var list<ActiveUsersDayDTO> */
    protected array $daily = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'filters' => StatsFiltersDTO::fromResponse(ResponseField::object($data, 'filters')),
            'daily' => self::readDaily($data),
        ]);
    }

    public function getFilters(): StatsFiltersDTO
    {
        return $this->filters;
    }

    /**
     * Ordered by date; days without data are missing.
     *
     * @return list<ActiveUsersDayDTO>
     */
    public function getDaily(): array
    {
        return $this->daily;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<ActiveUsersDayDTO>
     */
    private static function readDaily(array $data): array
    {
        $rows = [];
        foreach (ResponseField::listOfObjects($data, 'daily') as $rowData) {
            $rows[] = ActiveUsersDayDTO::fromResponse($rowData);
        }

        return $rows;
    }
}
