<?php

declare(strict_types=1);

/**
 * Description of TopCategoriesDTO.php
 *
 * GET categories: menu categories by item views (limit applies).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Categories;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\DTO\Common\StatsFiltersDTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class TopCategoriesDTO extends DTO
{
    protected StatsFiltersDTO $filters;

    /** @var list<TopCategoryDTO> */
    protected array $categories = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        $categories = [];
        foreach (ResponseField::listOfObjects($data, 'categories') as $rowData) {
            $categories[] = TopCategoryDTO::fromResponse($rowData);
        }

        return static::fromArray([
            'filters' => StatsFiltersDTO::fromResponse(ResponseField::object($data, 'filters')),
            'categories' => $categories,
        ]);
    }

    public function getFilters(): StatsFiltersDTO
    {
        return $this->filters;
    }

    /**
     * Ordered by views, most first.
     *
     * @return list<TopCategoryDTO>
     */
    public function getCategories(): array
    {
        return $this->categories;
    }
}
