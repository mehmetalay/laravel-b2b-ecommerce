<?php

namespace App\Application\Order\Services;

use App\Application\Order\DTO\OrderContext;
use App\Application\Product\DTO\ProductPriceContext;
use App\Domain\Product\Factories\ProductStockContextFactory;
use App\Domain\Product\ProductPriceCalculator;
use App\Domain\Product\ProductStockPolicy;
use App\Services\CartService;
use App\Services\CampaignService;
use App\Services\CurrencyService;
use Illuminate\Support\Collection;

class OrderProductBuilder
{
    public function __construct(
        private CampaignProcessor $campaignProcessor,
        private CampaignService $campaignService,
        private ProductPriceCalculator $productPriceCalculator,
        private CurrencyService $currencyService,
        private ProductStockPolicy $productStockPolicy,
        private ProductStockContextFactory $productStockContextFactory
    ) {}

    public function build(
        $order,
        Collection $currencyCarts,
        CartService $cartService,
        OrderContext $context
    ): array
    {
        $orderProductsPayload = [];
        $orderNotes = [];
        $stockRows = [];
        $uniqueProductIds = [];
        $cartItems = $cartService->carts();
        $campaignTotalQty = null;
        $campaignFreeQty = null;
        $campaignRatio = null;
        $campaignContext = null;
        $subtotal = 0.0;
        $lineDiscountTotal = 0.0;
        $campaignDiscountTotal = 0.0;
        $totalProductPriceAfterLineDiscount = 0.0;
        $quantityTotal = 0;
        $vatLines = [];

        foreach ($currencyCarts as $cart) {
            $product = $cart->product;
            $quantity = (int) $cart->quantity;
            $quantityTotal += $quantity;
            $uniqueProductIds[(int) $cart->product_id] = true;
            $stockRows[] = [
                'product' => $product,
                'quantity' => $quantity,
            ];

            if ((int) $cart->is_campaign_gift === 1) {
                $unitPrice = $this->calculateCartItemPrice(
                    cart: $cart,
                    quantity: 1,
                    discountRate: (float) $cart->discount,
                    context: $context
                );
                $campaignDiscountRow = $unitPrice * $quantity;
            } else {
                $campaignDiscountRow = (float) ($cart->campaign_discount ?? 0);
            }

            $campaignData = $this->campaignProcessor->process(
                cart: $cart,
                cartItems: $cartItems
            );

            if ($campaignData !== null) {
                $campaignTotalQty = $campaignData['campaign_total_quantity'];
                $campaignFreeQty = $campaignData['campaign_free_quantity'];
                $campaignRatio = $campaignData['campaign_row_ratio'];
                $campaignContext = $campaignData['campaign_context'];
            }

            $unitPrice = $this->calculateCartItemPrice(
                cart: $cart,
                quantity: 1,
                discountRate: 0.0,
                context: $context
            );

            $unitPriceAfterDiscount = $this->calculateCartItemPrice(
                cart: $cart,
                quantity: 1,
                discountRate: (float) $cart->effective_discount,
                context: $context
            );

            $lineBeforeDiscount = $unitPrice * $quantity;
            $lineAfterDiscount = $unitPriceAfterDiscount * $quantity;
            $lineDiscountTotal += max(0.0, $lineBeforeDiscount - $lineAfterDiscount);
            $totalProductPriceAfterLineDiscount += $lineAfterDiscount;
            $campaignDiscountTotal += $campaignDiscountRow;

            if ((int) $cart->is_campaign_gift === 0) {
                $subtotal += $lineBeforeDiscount;
                $vatLines[] = [
                    'matrah' => $lineAfterDiscount,
                    'vat_rate' => (float) ($product->vat_rate ?? 0),
                ];
            }

            $payloadRow = [
                'product_id' => $cart->product_id,
                'price' => $unitPrice,
                'discount' => $cart->effective_discount,
                'unit_price' => $unitPrice,
                'discount_rate' => $cart->effective_discount,
                'unit_price_after_discount' => $unitPriceAfterDiscount,
                'vat_rate' => $cart->product->vat_rate,
                'quantity' => $cart->quantity,
                'explanation' => $cart->explanation,
                'campaign_id' => $cart->campaign_id,
                'is_campaign_gift' => (int) $cart->is_campaign_gift,
                'campaign_discount' => $campaignDiscountRow,
                'campaign_discount_percent' => $cart->campaign_discount_percent,
                'campaign_note' => $this->campaignService->buildOrderProductCampaignNote($cart),
                'campaign_total_quantity' => $campaignTotalQty,
                'campaign_free_quantity' => $campaignFreeQty,
                'campaign_row_ratio' => $campaignRatio,
                'campaign_context' => $campaignContext ? json_encode($campaignContext) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($order !== null) {
                $payloadRow['order_id'] = $order->id;
            }

            $orderProductsPayload[] = $payloadRow;

            $availability = $this->productStockPolicy->checkAvailability(
                $product,
                $quantity,
                $this->productStockContextFactory->forAvailability()
            );

            if ($availability['allow_over_order'] && $availability['overflow_quantity'] > 0) {
                $immediateQty = (int) min($quantity, $availability['available_stock']);
                $delayedQty = (int) $availability['overflow_quantity'];
                $immediateText = $immediateQty > 0
                    ? "{$immediateQty} adet hemen gönderilecek, k"
                    : 'K';

                $orderNotes[] =
                    "{$product->name} {$product->code}: {$immediateText}alan {$delayedQty} adet 1-3 iş günü termin süresi içinde gönderilecektir.";
            }

        }

        $cartDiscount1 = $totalProductPriceAfterLineDiscount * ($context->cartDiscountRate1 / 100);
        $subtotalAfterLineDiscount = $subtotal - $lineDiscountTotal;
        $vatTotal = $this->calculateVat($vatLines, $campaignDiscountTotal + $cartDiscount1);
        $grandTotal = $subtotalAfterLineDiscount - $campaignDiscountTotal - $cartDiscount1 + $vatTotal;
        $totalDiscountAmount = $lineDiscountTotal + $campaignDiscountTotal + $cartDiscount1;

        return [
            'payload' => $orderProductsPayload,
            'notes' => $orderNotes,
            'stock' => $stockRows,
            'totals' => [
                'total_product_price' => $subtotal,
                'total_price' => $grandTotal,
                'total_price_excl_vat' => $grandTotal,
                'total_quantity' => $quantityTotal,
                'unique_product_count' => count($uniqueProductIds),
                'total_vat_amount' => $vatTotal,
                'cart_discount_rate_1' => $context->cartDiscountRate1,
                'cart_discount_1' => $cartDiscount1,
                'cart_discount_rate_2' => 0.0,
                'cart_discount_2' => 0.0,
                'total_discount_amount' => $totalDiscountAmount,
                'campaign_discount_total' => $campaignDiscountTotal,
                'subtotal' => $subtotal,
                'subtotal_after_line_discount' => $subtotalAfterLineDiscount,
                'line_discount_total' => $lineDiscountTotal,
                'grand_discount_total' => $totalDiscountAmount,
                'vat_total' => $vatTotal,
                'grand_total' => $grandTotal,
                'payment_type' => $context->paymentType,
            ],
        ];
    }

    private function calculateVat(array $lines, float $extraDiscount): float
    {
        $matrahAfterLineDiscount = (float) collect($lines)->sum('matrah');

        if ($matrahAfterLineDiscount <= 0) {
            return 0.0;
        }

        $netMatrah = max(0.0, $matrahAfterLineDiscount - $extraDiscount);
        $vatTotal = 0.0;

        foreach ($lines as $line) {
            $rate = (float) ($line['vat_rate'] ?? 0.0);
            $matrah = (float) ($line['matrah'] ?? 0.0);

            if ($matrah <= 0 || $rate <= 0) {
                continue;
            }

            $ratio = $matrah / $matrahAfterLineDiscount;
            $netLineMatrah = $netMatrah * $ratio;

            $vatTotal += $netLineMatrah * ($rate / 100);
        }

        return $vatTotal;
    }

    private function calculateCartItemPrice(
        $cart,
        int $quantity,
        float $discountRate,
        OrderContext $context
    ): float {
        if (!$cart->product) {
            return 0.0;
        }

        $priceContext = new ProductPriceContext(
            priceType: $context->paymentType,
            quantity: $quantity,
            discountRate: $discountRate,
            accountRate: $context->accountRate,
            accountRateType: $context->accountRateType,
            currency: $cart->product->getProductCurrency($context->paymentType),
            exchangeType: $cart->exchange_type,
            applyCampaign: false
        );

        $priceResult = $this->productPriceCalculator->calculate($cart->product, $priceContext);

        return (float) $this->currencyService->convert(
            $priceResult->finalPrice,
            $priceResult->exchangeType,
            $cart->product
        );
    }
}


