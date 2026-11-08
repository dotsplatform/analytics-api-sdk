<?php

declare(strict_types=1);

/**
 * Description of PlatformBreakdownDTO.php
 *
 * GET platforms: devices and events by one dimension, ordered by device-days (most first), at most "limit" rows.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Platforms;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\DTO\Common\StatsFiltersDTO;
use Dotsplatform\AnalyticsApi\Enums\BreakdownDimension;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class PlatformBreakdownDTO extends DTO
{
    protected StatsFiltersDTO $filters;

    protected string $dimension;

    /** @var list<PlatformBreakdownRowDTO> */
    protected array $rows = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        $dimension = ResponseField::string($data, 'dimension');

        return static::fromArray([
            'filters' => StatsFiltersDTO::fromResponse(ResponseField::object($data, 'filters')),
            'dimension' => $dimension,
            'rows' => self::readRows($data, $dimension),
        ]);
    }

    public function getFilters(): StatsFiltersDTO
    {
        return $this->filters;
    }

    /**
     * The raw dimension the service grouped by (it is "os" when the request sent none).
     */
    public function getDimensionValue(): string
    {
        return $this->dimension;
    }

    /**
     * The dimension, or null for one this SDK version does not know.
     */
    public function getDimension(): ?BreakdownDimension
    {
        return BreakdownDimension::tryFrom($this->dimension);
    }

    /**
     * @return list<PlatformBreakdownRowDTO>
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<PlatformBreakdownRowDTO>
     */
    private static function readRows(array $data, string $dimension): array
    {
        $rows = [];
        foreach (ResponseField::listOfObjects($data, 'rows') as $rowData) {
            $rows[] = PlatformBreakdownRowDTO::fromResponse($rowData, $dimension);
        }

        return $rows;
    }
}
