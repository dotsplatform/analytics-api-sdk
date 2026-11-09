<?php

declare(strict_types=1);

/**
 * Description of FunnelOrdersDTO.php
 *
 * The "orders" object of the funnel: how many orders analytics tracked, what happened to them in livesite
 * (completed and cancelled always, in_progress and not_in_livesite only when not zero) and why the cancelled ones
 * were cancelled, most orders first.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Funnel;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class FunnelOrdersDTO extends DTO
{
    protected int $tracked;

    /** @var list<OrderOutcomeDTO> */
    protected array $outcomes = [];

    /** @var list<OrderOutcomeDTO> */
    protected array $cancelReasons = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'tracked' => ResponseField::int($data, 'tracked'),
            'outcomes' => self::readOutcomes($data, 'outcomes'),
            'cancelReasons' => self::readOutcomes($data, 'cancel_reasons'),
        ]);
    }

    /**
     * Orders analytics saw being placed in the period.
     */
    public function getTracked(): int
    {
        return $this->tracked;
    }

    /**
     * @return list<OrderOutcomeDTO>
     */
    public function getOutcomes(): array
    {
        return $this->outcomes;
    }

    /**
     * Most orders first.
     *
     * @return list<OrderOutcomeDTO>
     */
    public function getCancelReasons(): array
    {
        return $this->cancelReasons;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<OrderOutcomeDTO>
     */
    private static function readOutcomes(array $data, string $key): array
    {
        $outcomes = [];
        foreach (ResponseField::listOfObjects($data, $key) as $outcomeData) {
            $outcomes[] = OrderOutcomeDTO::fromResponse($outcomeData);
        }

        return $outcomes;
    }
}
