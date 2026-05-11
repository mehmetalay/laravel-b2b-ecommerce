<?php

namespace App\Services;

use App\Models\Product;
use App\Support\PriceNormalizer;
use App\Domain\Product\ProductPriceCalculator;
use App\Application\Product\DTO\ProductPriceContext;

class PriceCalculatorService
{
    protected $currentAccountService;
    protected $productPriceCalculator;

    public function __construct(
        CurrentAccountService $currentAccountService,
        ProductPriceCalculator $productPriceCalculator
    ) {
        $this->currentAccountService = $currentAccountService;
        $this->productPriceCalculator = $productPriceCalculator;
    }

    public function calculate($basePrice, $quantity = 0, $discount = 0)
    {
        if ($quantity > 0) {
            $basePrice *= $quantity;
        }

        if ($discount > 0) {
            $basePrice *= (1 - $discount / 100);
        }

        return $basePrice;
    }

    public function formatPriceForDisplay($price, $oldPrice = null, $currency, $showOld = false)
    {
        if (auth('subdealer')->check() && !auth('subdealer')->user()->can_view_prices) {
            return format_price(0, null, $currency);
        }

        return format_price($price, $showOld ? $oldPrice : null, $currency, $showOld);
    }

    public function calculateProductPrice(Product $product, string $priceType = 'list', bool $applyCampaign = true): float
    {
        if ($priceType === 'special') {
            return 0.0;
        }

        $account = $this->currentAccountService->currentAccount();

        $context = new ProductPriceContext(
            priceType: $priceType,
            quantity: 0,
            discountRate: 0.0,
            accountRate: (float) ($account->increase_and_decrease_rate ?? 0),
            accountRateType: (int) ($account->increase_and_decrease_type ?? 1),
            currency: $product->getProductCurrency($priceType),
            exchangeType: null,
            applyCampaign: $applyCampaign
        );

        try {
            $price = $this->productPriceCalculator
                ->calculate($product, $context)
                ->finalPrice;
        } catch (\InvalidArgumentException $e) {
            logException($e, 'PriceCalculatorService::calculateProductPrice Invalid Price Type');
            return 0.0;
        }

        return $price > 0 ? $price : 0.0;
    }

    public function getProductPriceDisplay(Product $product, string $priceType = 'list'): string
    {
        $currency = $product->getProductCurrency($priceType);

        if (auth('subdealer')->check() && !auth('subdealer')->user()->can_view_prices) {
            return format_price(0, null, $currency);
        }

        $account = $this->currentAccountService->currentAccount();
        $price = $this->calculateProductPrice($product, $priceType, true);
        $priceShow = format_price($price, null, $currency, false);

        if (
            auth('web')->check() &&
            (
                (auth('web')->user()->role === 'dealer' && auth('web')->user()->hide_all_prices) ||
                (auth('web')->user()->role === 'salesman' && $account && $account->hide_all_prices)
            )
        ) {
            $priceShow = '';
        }

        return $priceShow;
    }

    public function getProductCashAndCreditPriceDisplay(Product $product, string $priceType): string
    {
        $currency = $product->getProductCurrency($priceType);

        if (auth('subdealer')->check() && !auth('subdealer')->user()->can_view_prices) {
            return format_price(0, null, $currency);
        }

        $account = $this->currentAccountService->currentAccount();
        $price = $this->calculateProductPrice($product, $priceType, true);
        $priceShow = format_price($price, null, $currency, false);

        if (
            auth('web')->check() &&
            (
                (auth('web')->user()->role === 'dealer' && auth('web')->user()->hide_all_prices) ||
                (auth('web')->user()->role === 'salesman' && $account && $account->hide_all_prices)
            )
        ) {
            $priceShow = '';
        }

        return $priceShow;
    }

    public function productDiscountRate(Product $product, string $priceType = 'list'): float
    {
        $account = $this->currentAccountService->currentAccount();
        $discountRate = 0.00;

        $customerPrice = null;
        // if ($account) {
        //     $customerPrice = DB::connection('sqlsrv')->selectOne(
        //         "SELECT BayiOzelIskonto1, Fiyat2Iskonto1, Fiyat3Iskonto1, Fiyat4Iskonto1, Fiyat5Iskonto1 FROM [ETA_2810_2025].[dbo].[vw_B2BCariStokFiyatlar] WHERE CARKOD = ? AND STKKOD = ?",
        //         [$account->code, $product->code]
        //     );
        // }

        $fallbackMap = [
            'special' => 0.00,
            'list' => 0.00,
            'cash' => $product->price_2_discount_rate,
            'credit' => $product->price_3_discount_rate,
            'term' => $product->price_4_discount_rate,
        ];

        $cpFieldMap = [
            'special' => 'BayiOzelIskonto1',
            'list' => 'Fiyat2Iskonto1',
            'cash' => 'Fiyat3Iskonto1',
            'credit' => 'Fiyat4Iskonto1',
            'term' => 'Fiyat5Iskonto1',
        ];

        $fallback = $fallbackMap[$priceType] ?? $product->price_1;

        if ($customerPrice) {
            $field = $cpFieldMap[$priceType] ?? $cpFieldMap['list'];
            $raw = $customerPrice->{$field} ?? null;

            $normalized = PriceNormalizer::normalize($raw, true);
            if ($normalized !== null) {
                $discountRate = $normalized;
            } else {
                $productNormalized = PriceNormalizer::normalize($fallback, true);
                $discountRate = $productNormalized !== null ? $productNormalized : 0.0;
            }
        } else {
            $productNormalized = PriceNormalizer::normalize($fallback, true);
            $discountRate = $productNormalized !== null ? $productNormalized : 0.0;
        }

        return $discountRate;
    }
}
