<?php

declare(strict_types=1);

/**
 * Description of QrScansDTO.php
 *
 * GET qr-scans: scans of the app QR codes of the period (client-web /app?src=<placement>) — the totals, per src
 * (where the QR code was placed), per phone platform and per day, with the store redirects, the apps page views and
 * the store buttons tapped there. Installs are not here: they are in Play Console / App Store Connect, campaign = src.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\QrScans;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\DTO\Common\StatsFiltersDTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class QrScansDTO extends DTO
{
    public const string PLATFORM_IOS = 'ios';

    public const string PLATFORM_ANDROID = 'android';

    public const string PLATFORM_OTHER = 'other';

    protected StatsFiltersDTO $filters;

    protected QrScanCountsDTO $totals;

    /** @var list<QrScanCountsDTO> */
    protected array $sources = [];

    /** @var list<QrScanCountsDTO> */
    protected array $platforms = [];

    /** @var list<QrScanCountsDTO> */
    protected array $days = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        $qrScans = ResponseField::object($data, 'qr_scans');

        $sources = [];
        foreach (ResponseField::listOfObjects($qrScans, 'sources') as $source) {
            $sources[] = QrScanCountsDTO::fromResponse($source, ResponseField::string($source, 'src'));
        }

        $platforms = [];
        foreach (ResponseField::listOfObjects($qrScans, 'platforms') as $platform) {
            $platforms[] = QrScanCountsDTO::fromResponse($platform, ResponseField::string($platform, 'platform'));
        }

        $days = [];
        foreach (ResponseField::listOfObjects($qrScans, 'days') as $day) {
            $days[] = QrScanCountsDTO::fromResponse($day, ResponseField::string($day, 'date'));
        }

        return static::fromArray([
            'filters' => StatsFiltersDTO::fromResponse(ResponseField::object($data, 'filters')),
            'totals' => QrScanCountsDTO::fromResponse(ResponseField::object($qrScans, 'totals'), 'total'),
            'sources' => $sources,
            'platforms' => $platforms,
            'days' => $days,
        ]);
    }

    public function getFilters(): StatsFiltersDTO
    {
        return $this->filters;
    }

    public function getTotals(): QrScanCountsDTO
    {
        return $this->totals;
    }

    /**
     * @return list<QrScanCountsDTO> one per src, most scans first, at most the request limit
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    /**
     * @return list<QrScanCountsDTO> ios, android, other — a platform without scans or clicks is left out
     */
    public function getPlatforms(): array
    {
        return $this->platforms;
    }

    /**
     * @return list<QrScanCountsDTO> every day of the period, days without scans as zeros
     */
    public function getDays(): array
    {
        return $this->days;
    }

    public function isEmpty(): bool
    {
        if ($this->totals->getScans() > 0) {
            return false;
        }

        return $this->totals->getStoreClicks() === 0;
    }
}
