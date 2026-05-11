<?php

namespace App\Application\Product;

use App\Domain\Product\Factories\ProductStockContextFactory;
use App\Domain\Product\ProductStockPolicy;
use App\Models\Product;

class ProductStockDisplayService
{
    public function __construct(
        private ProductStockPolicy $policy,
        private ProductStockContextFactory $contextFactory
    ) {}

    public function displayData(Product $product): array
    {
        return $this->policy->displayStock(
            $product,
            $this->contextFactory->forDisplay($product)
        );
    }
}
