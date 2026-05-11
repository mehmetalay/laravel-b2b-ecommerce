<?php

namespace App\Application\Order;

use App\Models\Campaign;
use App\Models\OrderProduct;
use App\Models\CustomerAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Services\CartService;
use Illuminate\Support\Facades\DB;
use App\Services\CurrentAccountService;
use App\Services\AutoApplyCampaignService;
use App\Services\AccountTransactionService;
use App\Application\Order\DTO\OrderCreationContext;
use App\Application\Order\DTO\OrderContext;
use App\Application\Order\Services\OrderCreator;
use App\Application\Order\Services\OrderProductBuilder;
use App\Application\Order\Services\StockService;

class CreateOrderAction
{
    public function __construct(
        private CurrentAccountService $currentAccountService,
        private OrderValidator $orderValidator,
        private OrderCreator $orderCreator,
        private OrderProductBuilder $orderProductBuilder,
        private StockService $stockService,
        private AutoApplyCampaignService $autoApplyCampaignService,
        private AccountTransactionService $accountTransactionService
    ) {}

    public function handle(CartService $cartService): JsonResponse
    {
        try {
            $context = $this->createContext($cartService);
            $validationResponse = $this->orderValidator->validate($context);

            if ($validationResponse !== null) {
                return $validationResponse;
            }
        } catch (\Exception $e) {
            logException($e, 'OrderController::store 1', true);

            return response()->json([
                'status' => 'error',
                'message' => trans('translations.order_controller.istek_sirasinda_bir_hata_olustu_lutfen_site_yoneticisiyle_iletisime_gecin'),
            ]);
        }

        DB::beginTransaction();

        try {
            logSession('Sipariş oluşturma işlemi başladı.', null, 'info', 'order_logs');

            $this->hydrateContext($context, $cartService);

            $hasFreeShipping = $cartService->hasFreeShipping() ? 1 : 0;

            $cartsByCurrency = $context->carts->groupBy('currency');

            foreach ($cartsByCurrency as $currency => $currencyCarts) {
                if ($context->groupCurrencyStatus['has_tl'] && $context->groupCurrencyStatus['has_usd']) {
                    $userCurrency = $context->currentAccount->currency;

                    if ($currency === $userCurrency) {
                        $userId = $context->userQuery['user_id'];
                    } else {
                        $groupUsers = $this->currentAccountService->getGroupUsers($context->currentAccount);

                        if ($groupUsers->count() === 1) {
                            $userId = $groupUsers->first()->current_account_id;
                        } else {
                            return response()->json([
                                'status' => 'warning',
                                'message' => 'Bir hata oluştu.',
                            ]);
                        }
                    }
                } else {
                    $userId = $context->userQuery['user_id'];
                }

                $orderContext = $this->buildOrderContext(
                    currencyCarts: $currencyCarts,
                    currency: (string) $currency,
                    userId: (int) $userId,
                    account: $context->currentAccount
                );

                $orderProductBuild = $this->orderProductBuilder->build(
                    order: null,
                    currencyCarts: $currencyCarts,
                    cartService: $cartService,
                    context: $orderContext
                );

                $order = $this->orderCreator->create(
                    context: $context,
                    currency: $currency,
                    totals: $orderProductBuild['totals'],
                    userId: $userId,
                    hasFreeShipping: $hasFreeShipping
                );

                $this->accountTransactionService->createDebitForOrder($order);

                $orderProductsPayload = array_map(function ($row) use ($order) {
                    $row['order_id'] = $order->id;
                    return $row;
                }, $orderProductBuild['payload']);

                OrderProduct::insert($orderProductsPayload);

                foreach ($orderProductBuild['stock'] as $row) {
                    $this->stockService->decrement($row['product'], $row['quantity']);
                }

                if (!empty($orderProductBuild['notes'])) {
                    $order->update(['note' => implode("\n", $orderProductBuild['notes'])]);
                }

                $cartService->markAsOrdered($context->userQuery, $currency);
            }

            session()->forget([
                'cart_discount_rate_tl_1', 'cart_discount_rate_tl_2',
                'cart_discount_rate_usd_1', 'cart_discount_rate_usd_2',
                'cart_discount_rate_eur_1', 'cart_discount_rate_eur_2',
                'cart_discount_rate_gbp_1', 'cart_discount_rate_gbp_2',
                'acting_dealer_id', 'acting_subdealer_id',
                'cart_payment_type_text', 'cart_payment_type', 'cart_payment_type_color',
                'product_view_type',
                'cart_free_shipping_active', 'campaign_opt_outs',
            ]);

            $message = trans('translations.order_controller.siparisiniz_basariyla_tarafimiza_ulasti');

            if (!empty($order->note)) {
                $note = nl2br($order->note);
                $message .= '<br><br><small>Not:<br>' . $note . '</small>';
            }

            session()->flash('order-success', $message);

            DB::commit();

            session()->forget('order_preview_token');

            logSession('Sipariş başarıyla oluşturuldu.', ['orderId:' => $order->id], 'info', 'order_logs');

            return response()->json([
                'status' => 'success',
                'redirect' => route('index'),
                'order_id' => $order->id,
                'trigger_order_service' => true,
            ]);
        } catch (\Throwable $e) {
            DB::rollback();
            logException($e, 'OrderController::store 2', true);

            return response()->json([
                'status' => 'error',
                'message' => trans('translations.order_controller.istek_sirasinda_bir_hata_olustu_lutfen_site_yoneticisiyle_iletisime_gecin'),
            ]);
        }
    }

    private function buildOrderContext(
        Collection $currencyCarts,
        string $currency,
        int $userId,
        $account
    ): OrderContext {
        return new OrderContext(
            paymentType: (string) ($currencyCarts->first()->payment_type ?? 'list'),
            accountRate: (float) ($account->increase_and_decrease_rate ?? 0),
            accountRateType: (int) ($account->increase_and_decrease_type ?? 1),
            cartDiscountRate1: (float) session()->get('cart_discount_rate_' . strtolower($currency) . '_1', 0),
            currency: $currency,
            userId: $userId
        );
    }

    private function createContext(CartService $cartService): OrderCreationContext
    {
        $isSalesman = auth('web')->check() && auth('web')->user()->role === 'salesman';

        return new OrderCreationContext(
            currentAccount: $this->currentAccountService->currentAccount(),
            userQuery: $this->currentAccountService->userQuery(),
            carts: $cartService->carts(),
            deliveryType: request('delivery_type'),
            shippingAddressId: request('shipping_address_id'),
            paymentPlanId: $isSalesman ? request('payment_plan_id') : 1,
            paymentTypeId: $isSalesman ? request('payment_type_id') : 1,
            cargoCompanyId: request('cargo_company_id'),
            warehouseName: request('warehouse_name'),
            pickupPerson: request('pickup_person'),
            transitNote: request('transit_note'),
            explanation: request('explanation'),
            ipAddress: request()->ip(),
            sendEmail: request('send_email') ? 1 : 0,
            sendSms: request('send_sms') ? 1 : 0
        );
    }

    private function hydrateContext(OrderCreationContext $context, CartService $cartService): void
    {
        $context->shippingAddressSnapshot = null;

        if ($context->shippingAddressId) {
            $address = CustomerAddress::find($context->shippingAddressId);

            if ($address) {
                $context->shippingAddressSnapshot = json_encode(
                    $address->toSnapshot(),
                    JSON_UNESCAPED_UNICODE
                );
            }
        }

        $cartService->forgetCache();
        $this->autoApplyCampaignService->sync($cartService->carts());

        $context->carts = $cartService->carts();
        $context->appliedCampaignIds = $context->carts
            ->where('is_campaign_gift', 0)
            ->pluck('campaign_id')
            ->filter()
            ->unique()
            ->values();

        $context->campaignSnapshot = null;

        if ($context->appliedCampaignIds->count()) {
            $campaigns = Campaign::with('rules')->whereIn('id', $context->appliedCampaignIds)->get();

            $context->campaignSnapshot = $campaigns->map(function ($campaign) {
                $rule = $campaign->rules->first();

                return [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'type' => $campaign->type,
                    'sub_type' => $campaign->sub_type,
                    'auto_apply' => (bool) $campaign->auto_apply,
                    'rule_extra' => $rule?->extra,
                ];
            })->values()->all();
        }

        $context->groupCurrencyStatus = $this->currentAccountService->groupCurrencyStatus();
    }
}



