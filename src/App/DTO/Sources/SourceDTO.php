<?php

declare(strict_types=1);

/**
 * Description of SourceDTO.php
 *
 * One traffic source of the period (first touch of the session): its name, its kind, sessions, sessions with an
 * order and that conversion in percent (null when there were no sessions). The name is a network name or a domain,
 * or one of the special names "Direct", "Back from payment", "App (iOS)" and "App (Android)".
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Sources;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Enums\SourceKind;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class SourceDTO extends DTO
{
    public const string NAME_DIRECT = 'Direct';

    public const string NAME_BACK_FROM_PAYMENT = 'Back from payment';

    public const string NAME_APP_IOS = 'App (iOS)';

    public const string NAME_APP_ANDROID = 'App (Android)';

    protected string $name;

    protected string $kind;

    protected int $sessions;

    protected int $orderingSessions;

    protected ?float $conversionPercent = null;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'name' => ResponseField::string($data, 'name'),
            'kind' => ResponseField::string($data, 'kind'),
            'sessions' => ResponseField::int($data, 'sessions'),
            'orderingSessions' => ResponseField::int($data, 'ordering_sessions'),
            'conversionPercent' => ResponseField::nullableFloat($data, 'conversion_percent'),
        ]);
    }

    /**
     * A network name ("Instagram"), a domain, or one of the NAME_* constants (translate those).
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * The raw kind code, e.g. "paid".
     */
    public function getKindValue(): string
    {
        return $this->kind;
    }

    /**
     * The kind, or null for one this SDK version does not know.
     */
    public function getKind(): ?SourceKind
    {
        return SourceKind::tryFrom($this->kind);
    }

    public function getSessions(): int
    {
        return $this->sessions;
    }

    /**
     * Sessions of this source that ended with an order.
     */
    public function getOrderingSessions(): int
    {
        return $this->orderingSessions;
    }

    public function getConversionPercent(): ?float
    {
        return $this->conversionPercent;
    }
}
