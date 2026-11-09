<?php

declare(strict_types=1);

/**
 * Description of FunnelStepDTO.php
 *
 * One step of the funnel or of the checkout funnel: its key (sessions, company_lists, menus, item_views, add_to_cart,
 * cart_views, checkouts, orders; checkout steps: checkouts, verification_shown, sms_sent, captchas, payment_info,
 * orders), an English label, how many sessions reached it and the percents. A percent is null when there is nothing
 * to divide by (the first step, every checkout step's percent of the previous step, or a zero start).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Funnel;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class FunnelStepDTO extends DTO
{
    protected string $key;

    protected string $label;

    protected int $sessions;

    protected ?float $percentOfPrevious = null;

    protected ?float $percentOfStart = null;

    protected bool $optional;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'key' => ResponseField::string($data, 'key'),
            'label' => ResponseField::string($data, 'label'),
            'sessions' => ResponseField::int($data, 'sessions'),
            'percentOfPrevious' => ResponseField::nullableFloat($data, 'percent_of_previous'),
            'percentOfStart' => ResponseField::nullableFloat($data, 'percent_of_start'),
            'optional' => ResponseField::bool($data, 'optional'),
        ]);
    }

    /**
     * A stable code to translate the step by, e.g. "add_to_cart".
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * The English name of the step.
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    public function getSessions(): int
    {
        return $this->sessions;
    }

    /**
     * Percent of the previous step's sessions; null for the first step and for every checkout step.
     */
    public function getPercentOfPrevious(): ?float
    {
        return $this->percentOfPrevious;
    }

    /**
     * Percent of the first step's sessions (visits, or checkouts for the checkout steps); null when that is zero.
     */
    public function getPercentOfStart(): ?float
    {
        return $this->percentOfStart;
    }

    /**
     * True for a step a session may skip (item views), so it can have more sessions than the previous step.
     */
    public function isOptional(): bool
    {
        return $this->optional;
    }
}
