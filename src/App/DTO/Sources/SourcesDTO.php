<?php

declare(strict_types=1);

/**
 * Description of SourcesDTO.php
 *
 * GET sources: where the sessions of the period came from (first touch), with the conversion to an order per source,
 * and the top UTM campaigns (at most 10).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Sources;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\DTO\Common\StatsFiltersDTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class SourcesDTO extends DTO
{
    protected StatsFiltersDTO $filters;

    protected int $totalSessions;

    /** @var list<SourceDTO> */
    protected array $sources = [];

    /** @var list<CampaignDTO> */
    protected array $campaigns = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'filters' => StatsFiltersDTO::fromResponse(ResponseField::object($data, 'filters')),
            'totalSessions' => ResponseField::int($data, 'total_sessions'),
            'sources' => self::readSources($data),
            'campaigns' => self::readCampaigns($data),
        ]);
    }

    public function getFilters(): StatsFiltersDTO
    {
        return $this->filters;
    }

    public function getTotalSessions(): int
    {
        return $this->totalSessions;
    }

    /**
     * @return list<SourceDTO>
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    /**
     * At most 10.
     *
     * @return list<CampaignDTO>
     */
    public function getCampaigns(): array
    {
        return $this->campaigns;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<SourceDTO>
     */
    private static function readSources(array $data): array
    {
        $sources = [];
        foreach (ResponseField::listOfObjects($data, 'sources') as $sourceData) {
            $sources[] = SourceDTO::fromResponse($sourceData);
        }

        return $sources;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<CampaignDTO>
     */
    private static function readCampaigns(array $data): array
    {
        $campaigns = [];
        foreach (ResponseField::listOfObjects($data, 'campaigns') as $campaignData) {
            $campaigns[] = CampaignDTO::fromResponse($campaignData);
        }

        return $campaigns;
    }
}
