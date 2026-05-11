<?php

namespace App\Domain\Product;

use App\Models\Product;
use InvalidArgumentException;
use App\Support\PriceNormalizer;
use App\Application\Product\DTO\ProductPriceContext;
use App\Application\Product\DTO\ProductPriceResult;

class ProductPriceCalculator
{
    public function calculate(Product $product, ProductPriceContext $context): ProductPriceResult
    {
        $unitPrice = $this->resolveUnitPrice($product, $context->priceType);
        $quantity = (int) $context->quantity;
        $linePrice = $quantity > 0 ? $unitPrice * $quantity : $unitPrice;

        $discountRate = max(0.0, (float) $context->discountRate);
        $discountAmount = $discountRate > 0
            ? $linePrice * ($discountRate / 100)
            : 0.0;

        $discountedPrice = max(0.0, $linePrice - $discountAmount);
        $finalPrice = $this->applyAccountRate(
            $discountedPrice,
            $context->accountRate,
            $context->accountRateType
        );
        $finalPrice = max(0.0, $finalPrice);
        $currency = $context->currency ?: $this->resolveCurrency($product, $context->priceType);

        return new ProductPriceResult(
            priceType: $context->priceType,
            unitPrice: $unitPrice,
            linePrice: $linePrice,
            discountRate: $discountRate,
            discountAmount: $discountAmount,
            finalPrice: $finalPrice,
            currency: $currency,
            exchangeType: $context->exchangeType,
            applyCampaign: $context->applyCampaign
        );
    }

    private function resolveUnitPrice(Product $product, string $priceType): float
    {
        $raw = match ($priceType) {
            'list' => $product->price_1,
            'cash' => $product->price_2,
            'credit' => $product->price_3,
            'term' => $product->price_4,
            default => throw new InvalidArgumentException("Unsupported price type: {$priceType}"),
        };

        return PriceNormalizer::normalize($raw) ?? 0.0;
    }

    private function resolveCurrency(Product $product, string $priceType): ?string
    {
        return match ($priceType) {
            'list' => $product->price_1_currency,
            'cash' => $product->price_2_currency,
            'credit' => $product->price_3_currency,
            'term' => $product->price_4_currency,
            default => null,
        };
    }

    private function applyAccountRate(float $price, float $rate, int $rateType): float
    {
        if ($rate == 0.0) {
            return $price;
        }

        $ratio = $rate / 100;

        return $rateType === 1
            ? $price + ($price * $ratio)
            : $price - ($price * $ratio);
    }
}
