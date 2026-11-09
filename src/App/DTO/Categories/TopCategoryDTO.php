<?php

declare(strict_types=1);

/**
 * Description of TopCategoryDTO.php
 *
 * One menu category of the top categories list: how many of its items were viewed, views, add-to-cart, device-days and the add-to-cart rate. category is null for items the service has not named yet.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Categories;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class TopCategoryDTO extends DTO
{
    protected ?string $category = null;

    protected ?string $categoryToken = null;

    protected int $items;

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
            'category' => ResponseField::nullableString($data, 'category'),
            'categoryToken' => ResponseField::nullableString($data, 'category_token'),
            'items' => ResponseField::int($data, 'items'),
            'views' => ResponseField::int($data, 'views'),
            'addToCart' => ResponseField::int($data, 'add_to_cart'),
            'deviceDays' => ResponseField::int($data, 'device_days'),
            'addToCartRate' => ResponseField::float($data, 'add_to_cart_rate'),
        ]);
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * The livesite company category token; null when unknown.
     */
    public function getCategoryToken(): ?string
    {
        return $this->categoryToken;
    }

    /**
     * How many different items of the category were viewed.
     */
    public function getItems(): int
    {
        return $this->items;
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

    public function getAddToCartRate(): float
    {
        return $this->addToCartRate;
    }
}
