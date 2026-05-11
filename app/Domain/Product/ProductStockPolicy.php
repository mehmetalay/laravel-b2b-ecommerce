<?php

namespace App\Domain\Product;

use App\Domain\Product\DTO\ProductStockContext;
use App\Models\Product;

class ProductStockPolicy
{
    public function checkAvailability(Product $product, int $qty, ProductStockContext $context): array
    {
        $requestedQuantity = max(0, $qty);
        $availableStock = max(0, (int) $product->stock);
        $allowOverOrder = (bool) $context->allowOverOrder;

        $isAvailable = $allowOverOrder || $requestedQuantity <= $availableStock;
        $acceptedQuantity = $allowOverOrder
            ? $requestedQuantity
            : min($requestedQuantity, $availableStock);

        return [
            'requested_quantity' => $requestedQuantity,
            'available_stock' => $availableStock,
            'allow_over_order' => $allowOverOrder,
            'is_available' => $isAvailable,
            'accepted_quantity' => $acceptedQuantity,
            'overflow_quantity' => max(0, $requestedQuantity - $availableStock),
            'remaining_quantity' => max(0, $availableStock - $requestedQuantity),
        ];
    }

    public function resolveDecrementQuantity(int $qty): int
    {
        return max(0, $qty);
    }

    public function displayStock(Product $product, ProductStockContext $context): array
    {
        $stock = (int) $product->stock;
        $productStock = $stock;

        if ($context->role === 'salesman' && $context->maxDisplayForSalesman !== null) {
            $productStock = min($productStock, (int) $context->maxDisplayForSalesman);
        } elseif ($context->role === 'dealer' && $context->maxDisplayForDealer !== null) {
            $productStock = min($productStock, (int) $context->maxDisplayForDealer);
        }

        $stockValue = null;
        if ($context->isStockVisible) {
            $limit = max(0, (int) $context->categoryStockDisplayLimit);
            $stockValue = ($limit > 0 && $productStock > $limit)
                ? "{$limit}+"
                : $productStock;
        }

        $availability = $this->checkAvailability($product, 1, $context);

        $status = 'in_stock';
        if ($stock <= 0) {
            $status = 'out_of_stock';
        } elseif ($context->isCriticalStockEnabled && $stock <= (int) $context->criticalStockThreshold) {
            $status = 'critical_stock';
        }

        return [
            'can_add_to_cart' => $availability['is_available'],
            'product_stock' => $productStock,
            'stock_value' => $stockValue,
            'status' => $status,
        ];
    }
}
