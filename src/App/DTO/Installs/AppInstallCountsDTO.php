<?php

declare(strict_types=1);

/**
 * Description of AppInstallCountsDTO.php
 *
 * Installs and updates of one platform ("ios", "android") or of one day (Y-m-d), depending on the list it is in.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Installs;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class AppInstallCountsDTO extends DTO
{
    protected string $key;

    protected int $installs;

    protected int $updates;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data, string $keyField): static
    {
        return static::fromArray([
            'key' => ResponseField::string($data, $keyField),
            'installs' => ResponseField::int($data, 'installs'),
            'updates' => ResponseField::int($data, 'updates'),
        ]);
    }

    /**
     * The platform ("ios", "android") in the platform list, the day (Y-m-d) in the daily list.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    public function getInstalls(): int
    {
        return $this->installs;
    }

    public function getUpdates(): int
    {
        return $this->updates;
    }
}
