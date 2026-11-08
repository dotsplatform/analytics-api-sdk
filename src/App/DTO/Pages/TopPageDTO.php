<?php

declare(strict_types=1);

/**
 * Description of TopPageDTO.php
 *
 * One page (web: host + path) or app screen of the top pages list.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Pages;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class TopPageDTO extends DTO
{
    protected ?string $page = null;

    protected int $views;

    protected int $deviceDays;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'page' => ResponseField::nullableString($data, 'page'),
            'views' => ResponseField::int($data, 'views'),
            'deviceDays' => ResponseField::int($data, 'device_days'),
        ]);
    }

    /**
     * Web: host + path, e.g. "shop.test/menu"; apps: the screen name; null when the events carried none.
     */
    public function getPage(): ?string
    {
        return $this->page;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function getDeviceDays(): int
    {
        return $this->deviceDays;
    }
}
