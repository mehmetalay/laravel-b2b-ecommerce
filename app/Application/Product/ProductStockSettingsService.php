<?php

namespace App\Application\Product;

class ProductStockSettingsService
{
    public function allowOverOrder(): bool
    {
        return (bool) additional_setting('allow_over_order');
    }

    public function isCriticalStockEnabled(): bool
    {
        return (bool) additional_setting('is_critical_stock_enabled');
    }

    public function criticalStockThreshold(): int
    {
        return (int) additional_setting('critical_stock_threshold', 1);
    }

    public function maxDisplayForSalesman(): ?int
    {
        return additional_setting('maximum_stock_number_display_plasiyer') !== null
            ? (int) additional_setting('maximum_stock_number_display_plasiyer')
            : null;
    }

    public function maxDisplayForDealer(): ?int
    {
        return additional_setting('maximum_stock_number_display_user') !== null
            ? (int) additional_setting('maximum_stock_number_display_user')
            : null;
    }
}
