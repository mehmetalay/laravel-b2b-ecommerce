<?php

namespace App\Domain\Product\Factories;

use App\Application\Product\ProductStockSettingsService;
use App\Domain\Product\DTO\ProductStockContext;
use App\Models\Product;
use App\Support\StockVisibility;

class ProductStockContextFactory
{
    public function __construct(
        private ProductStockSettingsService $settings
    ) {}

    public function forAvailability(): ProductStockContext
    {
        return new ProductStockContext(
            allowOverOrder: $this->settings->allowOverOrder()
        );
    }

    public function forDisplay(Product $product): ProductStockContext
    {
        return new ProductStockContext(
            allowOverOrder: $this->settings->allowOverOrder(),
            role: auth('web')->check() ? (string) auth('web')->user()->role : null,
            maxDisplayForSalesman: $this->settings->maxDisplayForSalesman(),
            maxDisplayForDealer: $this->settings->maxDisplayForDealer(),
            isStockVisible: StockVisibility::canSee(),
            categoryStockDisplayLimit: (int) ($product->category?->stock_display_limit ?? 0),
            isCriticalStockEnabled: $this->settings->isCriticalStockEnabled(),
            criticalStockThreshold: $this->settings->criticalStockThreshold()
        );
    }
}
