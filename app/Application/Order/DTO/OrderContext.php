<?php

namespace App\Application\Order\DTO;

class OrderContext
{
    public function __construct(
        public string $paymentType,
        public float $accountRate,
        public int $accountRateType,
        public float $cartDiscountRate1,
        public string $currency,
        public int $userId
    ) {}
}
