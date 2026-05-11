<?php

namespace App\Application\Cart\Factories;

use App\Application\Cart\DTO\CartContext;
use App\Services\CurrentAccountService;

class CartContextFactory
{
    public function __construct(
        private CurrentAccountService $currentAccountService
    ) {}

    public function fromSession(): CartContext
    {
        $currencies = ['TL', 'USD', 'EUR', 'GBP'];
        $discountRates = [];
        $account = $this->currentAccountService->currentAccount();

        foreach ($currencies as $currency) {
            $currencyKey = strtolower($currency);
            $discountRates[$currencyKey] = [
                1 => (float) session()->get("cart_discount_rate_{$currencyKey}_1", 0),
                2 => (float) session()->get("cart_discount_rate_{$currencyKey}_2", 0),
            ];
        }

        $manualGiftRemoved = collect((array) session('manual_gift_removed', []))
            ->filter(fn ($value) => (bool) $value)
            ->keys()
            ->map(fn ($key) => (int) $key)
            ->values()
            ->all();

        return new CartContext(
            currencies: $currencies,
            discountRates: $discountRates,
            campaignDiscountTotals: [],
            paymentType: session()->get('cart_payment_type'),
            accountRate: (float) ($account->increase_and_decrease_rate ?? 0),
            accountRateType: (int) ($account->increase_and_decrease_type ?? 1),
            paymentTypeText: session()->get('cart_payment_type_text'),
            paymentTypeColor: session()->get('cart_payment_type_color'),
            freeShippingActive: session()->has('cart_free_shipping_active'),
            manualGiftRemovedCampaignIds: $manualGiftRemoved,
            campaignOptOuts: collect(session('campaign_opt_outs', []))
                ->map(fn ($value) => (int) $value)
                ->values()
                ->all()
        );
    }

    public function discountSessionKeys(?string $currency = null): array
    {
        $all = [
            'cart_discount_rate_tl_1',
            'cart_discount_rate_tl_2',
            'cart_discount_rate_usd_1',
            'cart_discount_rate_usd_2',
            'cart_discount_rate_eur_1',
            'cart_discount_rate_eur_2',
            'cart_discount_rate_gbp_1',
            'cart_discount_rate_gbp_2',
        ];

        if ($currency === null) {
            return $all;
        }

        $currencyKey = strtolower($currency);

        return [
            "cart_discount_rate_{$currencyKey}_1",
            "cart_discount_rate_{$currencyKey}_2",
        ];
    }
}
