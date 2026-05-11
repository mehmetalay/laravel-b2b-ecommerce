<?php

namespace App\Application\Cart\Services;

use App\Application\Cart\DTO\CartContext;
use App\Application\Cart\DTO\CartTotalsResult;
use App\Services\CurrencyService;
use App\Domain\Product\ProductPriceCalculator;
use App\Application\Product\DTO\ProductPriceContext;
use Illuminate\Support\Collection;

class CartCalculator
{
    private $priceCache = [];

    public function __construct(
        private ProductPriceCalculator $productPriceCalculator,
        private CurrencyService $currencyService
    ) {}

    public function calculate(Collection $carts, CartContext $context): CartTotalsResult
    {
        $this->priceCache = [];
        $totals = [];

        foreach ($context->currencies as $currency) {
            $subtotal = $this->totalProductPriceBeforeDiscount($carts, $currency, $context);
            $lineDiscount = $this->totalLineDiscount($carts, $currency, $context);
            $afterLineDiscount = $subtotal - $lineDiscount;
            $campaignDiscounts = $context->campaignDiscountTotal($currency);

            $discount1 = $this->cartDiscount1($carts, $currency, $context);
            $discount2 = 0.0;
            $vat = $this->totalVat($carts, $currency, $context, $discount1, $discount2);

            $grand = $afterLineDiscount - $campaignDiscounts - $discount1 - $discount2 + $vat;

            $totals[strtolower($currency)] = [
                'subtotal' => $subtotal,
                'line_discount_total' => $lineDiscount,
                'subtotal_after_line_discount' => $afterLineDiscount,
                'campaign_discount_total' => $campaignDiscounts,
                'cart_discount_1' => $discount1,
                'cart_discount_2' => $discount2,
                'vat_total' => $vat,
                'grand_total' => $grand,
            ];
        }

        return new CartTotalsResult($totals);
    }

    private function totalProductPriceBeforeDiscount(Collection $carts, string $currency, CartContext $context): float
    {
        return (float) $this->cartsByCurrency($carts, $currency)
            ->where('is_campaign_gift', 0)
            ->reduce(function ($total, $item) use ($context, $currency) {
                return $total + $this->calculateCartItemPrice(
                    $item,
                    (int) $item->quantity,
                    0.0,
                    $context,
                    $currency
                );
            }, 0);
    }

    private function totalProductPrices(Collection $carts, string $currency, CartContext $context): float
    {
        return (float) $this->cartsByCurrency($carts, $currency)->reduce(function ($total, $item) use ($context, $currency) {
            return $total + $this->calculateCartItemPrice(
                $item,
                (int) $item->quantity,
                (float) $item->effective_discount,
                $context,
                $currency
            );
        }, 0);
    }

    private function totalLineDiscount(Collection $carts, string $currency, CartContext $context): float
    {
        return (float) $this->cartsByCurrency($carts, $currency)->reduce(function ($total, $item) use ($context, $currency) {
            $quantity = (int) $item->quantity;
            $discountRate = (float) $item->effective_discount;

            if ($discountRate == 0.0) {
                return $total;
            }

            $lineBeforeDiscount = $this->calculateCartItemPrice($item, $quantity, 0.0, $context, $currency);
            $lineAfterDiscount = $this->calculateCartItemPrice($item, $quantity, $discountRate, $context, $currency);

            return $total + max(0.0, $lineBeforeDiscount - $lineAfterDiscount);
        }, 0);
    }

    private function cartDiscount1(Collection $carts, string $currency, CartContext $context): float
    {
        return $this->totalProductPrices($carts, $currency, $context) * ($context->discountRate($currency, 1) / 100);
    }

    private function totalVat(
        Collection $carts,
        string $currency,
        CartContext $context,
        float $discount1,
        float $discount2
    ): float {
        $lines = $this->cartsByCurrency($carts, $currency)
            ->where('is_campaign_gift', 0)
            ->map(function ($item) use ($context, $currency) {
                $lineMatrah = $this->calculateCartItemPrice(
                    $item,
                    (int) $item->quantity,
                    (float) $item->effective_discount,
                    $context,
                    $currency
                );

                return [
                    'matrah' => $lineMatrah,
                    'vat_rate' => (float) ($item->product->vat_rate ?? 0),
                ];
            });

        $matrahAfterLineDiscount = (float) $lines->sum('matrah');

        if ($matrahAfterLineDiscount <= 0) {
            return 0.0;
        }

        $extraDiscount =
            $context->campaignDiscountTotal($currency)
            + $discount1
            + $discount2;

        $netMatrah = max(0, $matrahAfterLineDiscount - $extraDiscount);
        $vatTotal = 0;

        foreach ($lines as $line) {
            $rate = (float) $line['vat_rate'];
            $matrah = (float) $line['matrah'];

            if ($matrah <= 0 || $rate <= 0) {
                continue;
            }

            $ratio = $matrah / $matrahAfterLineDiscount;
            $netLineMatrah = $netMatrah * $ratio;

            $vatTotal += $netLineMatrah * ($rate / 100);
        }

        return $vatTotal;
    }

    private function cartsByCurrency(Collection $carts, string $currency): Collection
    {
        return $carts->where('currency', $currency);
    }

    private function calculateCartItemPrice(
        $item,
        int $quantity,
        float $discountRate,
        CartContext $cartContext,
        string $currency
    ): float
    {
        if (!$item->product) {
            return 0.0;
        }

        $priceType = $cartContext->paymentType ?: 'list';
        $currency = (string) $currency;

        $key = implode('|', [
            ($item->id ?? spl_object_hash($item)),
            $quantity,
            $discountRate,
            $priceType,
            (string) $cartContext->accountRate,
            (string) $cartContext->accountRateType,
            $currency,
        ]);

        if (isset($this->priceCache[$key])) {
            return $this->priceCache[$key];
        }

        $priceContext = new ProductPriceContext(
            priceType: $priceType,
            quantity: $quantity,
            discountRate: $discountRate,
            accountRate: (float) $cartContext->accountRate,
            accountRateType: (int) $cartContext->accountRateType,
            currency: $currency,
            exchangeType: $item->exchange_type,
            applyCampaign: false
        );

        $priceResult = $this->productPriceCalculator->calculate($item->product, $priceContext);
        $price = $this->currencyService->convert(
            $priceResult->finalPrice,
            $priceResult->exchangeType,
            $item->product
        );

        return $this->priceCache[$key] = (float) $price;
    }
}
