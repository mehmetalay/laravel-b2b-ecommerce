<?php

namespace App\Application\Product\DTO;

class ProductPriceResult
{
    public function __construct(
        public string $priceType,
        public float $unitPrice,
        public float $linePrice,
        public float $discountRate,
        public float $discountAmount,
        public float $finalPrice,
        public ?string $currency = null,
        public ?string $exchangeType = null,
        public bool $applyCampaign = true
    ) {}
}

