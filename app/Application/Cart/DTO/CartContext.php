<?php

namespace App\Application\Cart\DTO;

class CartContext
{
    public function __construct(
        public array $currencies = ['TL', 'USD', 'EUR', 'GBP'],
        public array $discountRates = [],
        public array $campaignDiscountTotals = [],
        public ?string $paymentType = null,
        public float $accountRate = 0.0,
        public int $accountRateType = 1,
        public ?string $paymentTypeText = null,
        public ?string $paymentTypeColor = null,
        public bool $freeShippingActive = false,
        public array $manualGiftRemovedCampaignIds = [],
        public array $campaignOptOuts = []
    ) {}

    public function discountRate(string $currency, int $level): float
    {
        $currencyKey = strtolower($currency);

        return (float) ($this->discountRates[$currencyKey][$level] ?? 0);
    }

    public function campaignDiscountTotal(string $currency): float
    {
        return (float) ($this->campaignDiscountTotals[strtolower($currency)] ?? 0);
    }

    public function isManualGiftRemoved(int $campaignId): bool
    {
        return in_array($campaignId, $this->manualGiftRemovedCampaignIds, true);
    }
}
