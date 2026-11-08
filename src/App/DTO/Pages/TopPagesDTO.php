<?php

declare(strict_types=1);

/**
 * Description of TopPagesDTO.php
 *
 * GET pages: the most viewed pages (web) and screens (apps), most viewed first, at most "limit" rows.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Pages;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\DTO\Common\StatsFiltersDTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class TopPagesDTO extends DTO
{
    protected StatsFiltersDTO $filters;

    /** @var list<TopPageDTO> */
    protected array $pages = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'filters' => StatsFiltersDTO::fromResponse(ResponseField::object($data, 'filters')),
            'pages' => self::readPages($data),
        ]);
    }

    public function getFilters(): StatsFiltersDTO
    {
        return $this->filters;
    }

    /**
     * Ordered by views, most first.
     *
     * @return list<TopPageDTO>
     */
    public function getPages(): array
    {
        return $this->pages;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<TopPageDTO>
     */
    private static function readPages(array $data): array
    {
        $rows = [];
        foreach (ResponseField::listOfObjects($data, 'pages') as $rowData) {
            $rows[] = TopPageDTO::fromResponse($rowData);
        }

        return $rows;
    }
}
