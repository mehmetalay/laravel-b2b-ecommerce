<?php

namespace App\Application\Order\Services;

use App\Domain\Product\ProductStockPolicy;
use App\Models\Product;
use RuntimeException;

class StockService
{
    public function __construct(
        private ProductStockPolicy $productStockPolicy
    ) {}

    public function decrement(Product $product, int $quantity): void
    {
        $decrementQuantity = $this->productStockPolicy->resolveDecrementQuantity($quantity);

        if ($decrementQuantity <= 0) {
            return;
        }

        try {
            $affected = Product::where('id', $product->id)
                ->where('stock', '>=', $decrementQuantity)
                ->decrement('stock', $decrementQuantity);

            if ($affected === 0) {
                throw new RuntimeException('Stock decrement failed');
            }

            $product->refresh();
        } catch (\Exception $e) {
            logException($e, 'OrderController::store Stock Drop Error', true);
            throw $e;
        }
    }
}
