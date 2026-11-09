<?php

declare(strict_types=1);

/**
 * Description of TopItemDTO.php
 *
 * One item of the top items list: views, add-to-cart count, device-days and the add-to-cart rate
 * (add_to_cart / views, rounded to 4 decimals, 0 when there were no views).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Items;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class TopItemDTO extends DTO
{
    protected ?string $itemId = null;

    protected ?string $name = null;

    protected ?string $category = null;

    protected int $views;

    protected int $addToCart;

    protected int $deviceDays;

    protected float $addToCartRate;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'itemId' => ResponseField::nullableString($data, 'item_id'),
            'name' => ResponseField::nullableString($data, 'name'),
            'category' => ResponseField::nullableString($data, 'category'),
            'views' => ResponseField::int($data, 'views'),
            'addToCart' => ResponseField::int($data, 'add_to_cart'),
            'deviceDays' => ResponseField::int($data, 'device_days'),
            'addToCartRate' => ResponseField::float($data, 'add_to_cart_rate'),
        ]);
    }

    /**
     * The item id as the client apps send it (a string); null when the events carried none.
     */
    public function getItemId(): ?string
    {
        return $this->itemId;
    }

    /**
     * The livesite dish name; null while the service has not named the item yet (it reads names hourly).
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * The livesite menu category name; null when unknown.
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function getAddToCart(): int
    {
        return $this->addToCart;
    }

    public function getDeviceDays(): int
    {
        return $this->deviceDays;
    }

    /**
     * add_to_cart / views, rounded to 4 decimals; 0.0 when there were no views.
     */
    public function getAddToCartRate(): float
    {
        return $this->addToCartRate;
    }
}
