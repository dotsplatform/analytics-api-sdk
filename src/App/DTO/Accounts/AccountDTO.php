<?php

declare(strict_types=1);

/**
 * Description of AccountDTO.php
 *
 * One account the analytics service knows: its livesite token, name, domain and logo URL.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Accounts;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class AccountDTO extends DTO
{
    protected string $token;

    protected string $name;

    protected ?string $domain = null;

    protected ?string $logoUrl = null;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'token' => ResponseField::string($data, 'token'),
            'name' => ResponseField::string($data, 'name'),
            'domain' => ResponseField::nullableString($data, 'domain'),
            'logoUrl' => ResponseField::nullableString($data, 'logo_url'),
        ]);
    }

    /**
     * The livesite account token (uuid): pass it to StatsRequestDTO::forAccount().
     */
    public function getToken(): string
    {
        return $this->token;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }
}
