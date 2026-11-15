<?php

declare(strict_types=1);

/**
 * Description of ItemFunnelCountsDTO.php
 *
 * The counters of one dish (for a period) or of one day of a dish: how often it was opened, added to cart and ordered.
 * Orders come from livesite orders: getOrders() counts website and app orders (of the asked platform),
 * getOrdersAllChannels() also operator, POS, Glovo and API orders; failed and declined orders are not counted.
 * Rates are null when their base is 0 and can be above 1 (adds with the «+» on the menu tile, without opening the dish).
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Items;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class ItemFunnelCountsDTO extends DTO
{
    protected int $views;

    protected int $viewSessions;

    protected int $addToCart;

    protected int $addSessions;

    protected int $addToCartPlus;

    protected int $orders;

    protected int $completedOrders;

    protected int $quantity;

    protected int $ordersAllChannels;

    protected ?float $viewToCartRate = null;

    protected ?float $cartToOrderRate = null;

    protected ?float $viewToOrderRate = null;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        return static::fromArray([
            'views' => ResponseField::int($data, 'views'),
            'viewSessions' => ResponseField::int($data, 'view_sessions'),
            'addToCart' => ResponseField::int($data, 'add_to_cart'),
            'addSessions' => ResponseField::int($data, 'add_sessions'),
            'addToCartPlus' => ResponseField::int($data, 'add_to_cart_plus'),
            'orders' => ResponseField::int($data, 'orders'),
            'completedOrders' => ResponseField::int($data, 'completed_orders'),
            'quantity' => ResponseField::int($data, 'quantity'),
            'ordersAllChannels' => ResponseField::int($data, 'orders_all_channels'),
            'viewToCartRate' => ResponseField::nullableFloat($data, 'view_to_cart_rate'),
            'cartToOrderRate' => ResponseField::nullableFloat($data, 'cart_to_order_rate'),
            'viewToOrderRate' => ResponseField::nullableFloat($data, 'view_to_order_rate'),
        ]);
    }

    public function getViews(): int
    {
        return $this->views;
    }

    /**
     * Sessions that opened the dish card at least once.
     */
    public function getViewSessions(): int
    {
        return $this->viewSessions;
    }

    public function getAddToCart(): int
    {
        return $this->addToCart;
    }

    /**
     * Sessions that added the dish to the cart at least once.
     */
    public function getAddSessions(): int
    {
        return $this->addSessions;
    }

    /**
     * Adds made with the «+» on the menu tile (website), without opening the dish card.
     */
    public function getAddToCartPlus(): int
    {
        return $this->addToCartPlus;
    }

    /**
     * Website and app orders (of the asked platform) that had the dish.
     */
    public function getOrders(): int
    {
        return $this->orders;
    }

    public function getCompletedOrders(): int
    {
        return $this->completedOrders;
    }

    /**
     * Portions ordered in getOrders().
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * Orders from every channel: website, apps, operator, POS, Glovo, API.
     */
    public function getOrdersAllChannels(): int
    {
        return $this->ordersAllChannels;
    }

    /**
     * add sessions / view sessions.
     */
    public function getViewToCartRate(): ?float
    {
        return $this->viewToCartRate;
    }

    /**
     * orders / add sessions.
     */
    public function getCartToOrderRate(): ?float
    {
        return $this->cartToOrderRate;
    }

    /**
     * orders / view sessions.
     */
    public function getViewToOrderRate(): ?float
    {
        return $this->viewToOrderRate;
    }
}
