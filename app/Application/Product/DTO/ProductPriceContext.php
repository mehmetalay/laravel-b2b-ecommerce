<?php

namespace App\Application\Product\DTO;

class ProductPriceContext
{
    public function __construct(
        public string $priceType = 'list',
        public int $quantity = 0,
        public float $discountRate = 0.0,
        public float $accountRate = 0.0,
        public int $accountRateType = 1,
        public ?string $currency = null,
        public ?string $exchangeType = null,
        public bool $applyCampaign = true
    ) {}
}
