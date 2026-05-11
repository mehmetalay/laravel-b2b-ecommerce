<?php

namespace App\Application\Order\Services;

use App\Services\OrderService;
use App\Services\CurrencyService;
use App\Application\Order\DTO\OrderCreationContext;

class OrderCreator
{
    public function __construct(
        private OrderService $orderService,
        private CurrencyService $currencyService
    ) {}

    public function create(
        OrderCreationContext $context,
        string $currency,
        array $totals,
        $userId,
        int $hasFreeShipping
    ) {
        return $this->orderService->createRaw([
            'plasiyer_id' => ($context->userQuery['plasiyer_id'] ?? null),
            'user_id' => $userId,
            'sub_dealer_id' => ($context->userQuery['sub_dealer_id'] ?? null),
            'creator_type' => $context->userQuery['creator_type'],
            'status' => $context->userQuery['order_status'],

            'total_product_price' => $totals['total_product_price'],
            'total_price' => $totals['total_price'],
            'total_price_excl_vat' => $totals['total_price_excl_vat'],
            'total_quantity' => $totals['total_quantity'],
            'unique_product_count' => $totals['unique_product_count'],
            'total_vat_amount' => $totals['total_vat_amount'],

            'cart_discount_rate_1' => $totals['cart_discount_rate_1'],
            'cart_discount_1' => $totals['cart_discount_1'],
            'cart_discount_rate_2' => 0,
            'cart_discount_2' => 0.00,
            'total_discount_amount' => $totals['total_discount_amount'],

            'campaign_id' => null,
            'campaign_type' => null,
            'campaign_discount_total' => $totals['campaign_discount_total'],
            'has_free_shipping' => $hasFreeShipping,
            'campaign_snapshot' => $context->campaignSnapshot ? json_encode($context->campaignSnapshot, JSON_UNESCAPED_UNICODE) : null,
            'has_campaign' => $context->appliedCampaignIds->count() ? 1 : 0,

            'subtotal_before_discount' => $totals['subtotal'],
            'subtotal_after_line_discount' => $totals['subtotal_after_line_discount'],
            'line_discount_total' => $totals['line_discount_total'],
            'grand_discount_total' => $totals['grand_discount_total'],

            'usd_exchange_rate' => $this->currencyService->getFirstByCode('USD')->selling_price,
            'eur_exchange_rate' => $this->currencyService->getFirstByCode('EUR')->selling_price,
            'gbp_exchange_rate' => $this->currencyService->getFirstByCode('GBP')->selling_price,

            'currency' => $currency,
            'payment_plan_id' => $context->paymentPlanId,
            'payment_type_id' => $context->paymentTypeId,

            'delivery_type' => $context->deliveryType,
            'cargo_company_id' => $context->cargoCompanyId,
            'warehouse_name' => $context->warehouseName,
            'pickup_person' => $context->pickupPerson,
            'transit_note' => $context->transitNote,

            'shipping_address_id' => $context->shippingAddressId,
            'shipping_address_snapshot' => $context->shippingAddressSnapshot,

            'explanation' => $context->explanation,
            'ip_address' => $context->ipAddress,
            'send_email' => $context->sendEmail,
            'send_sms' => $context->sendSms,
            'order_status_id' => 2,
            'payment_type' => $totals['payment_type'],
            'erp_status' => 'pending',
        ]);
    }
}
