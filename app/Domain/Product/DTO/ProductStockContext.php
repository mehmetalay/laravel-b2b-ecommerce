<?php

namespace App\Domain\Product\DTO;

class ProductStockContext
{
    public function __construct(
        public bool $allowOverOrder = false,
        public ?string $role = null,
        public ?int $maxDisplayForSalesman = null,
        public ?int $maxDisplayForDealer = null,
        public bool $isStockVisible = true,
        public int $categoryStockDisplayLimit = 0,
        public bool $isCriticalStockEnabled = false,
        public int $criticalStockThreshold = 1
    ) {}
}
