<?php

declare(strict_types=1);

/**
 * Description of QrScanCountsDTO.php
 *
 * Scans of the app QR codes and the store buttons tapped on the apps page, for one src (where the QR code was
 * placed), one phone platform ("ios", "android", "other"), one day (Y-m-d), or the whole period (key "total").
 * scans = store redirects (iOS + Android) + apps page views.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\QrScans;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class QrScanCountsDTO extends DTO
{
    protected string $key;

    protected int $scans;

    protected int $redirectsIos;

    protected int $redirectsAndroid;

    protected int $fallbackViews;

    protected int $storeClicks;

    protected int $appStoreClicks;

    protected int $googlePlayClicks;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data, string $key): static
    {
        return static::fromArray([
            'key' => $key,
            'scans' => ResponseField::int($data, 'scans'),
            'redirectsIos' => ResponseField::int($data, 'redirects_ios'),
            'redirectsAndroid' => ResponseField::int($data, 'redirects_android'),
            'fallbackViews' => ResponseField::int($data, 'fallback_views'),
            'storeClicks' => ResponseField::int($data, 'store_clicks'),
            'appStoreClicks' => ResponseField::int($data, 'app_store_clicks'),
            'googlePlayClicks' => ResponseField::int($data, 'google_play_clicks'),
        ]);
    }

    /**
     * The src in the sources list, the platform in the platforms list, Y-m-d in the daily list, "total" for the totals.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    public function getScans(): int
    {
        return $this->scans;
    }

    /**
     * Scans sent straight to the App Store.
     */
    public function getRedirectsIos(): int
    {
        return $this->redirectsIos;
    }

    /**
     * Scans sent straight to Google Play.
     */
    public function getRedirectsAndroid(): int
    {
        return $this->redirectsAndroid;
    }

    public function getRedirects(): int
    {
        return $this->redirectsIos + $this->redirectsAndroid;
    }

    /**
     * Scans that opened the apps page (desktop, a phone without a store app or a project without the store link).
     */
    public function getFallbackViews(): int
    {
        return $this->fallbackViews;
    }

    /**
     * Store buttons tapped on the apps page.
     */
    public function getStoreClicks(): int
    {
        return $this->storeClicks;
    }

    public function getAppStoreClicks(): int
    {
        return $this->appStoreClicks;
    }

    public function getGooglePlayClicks(): int
    {
        return $this->googlePlayClicks;
    }
}
