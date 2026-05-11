<?php

namespace App\Application\Order\Services;

use App\Services\CampaignService;
use Illuminate\Support\Collection;

class CampaignProcessor
{
    public function __construct(private CampaignService $campaignService) {}

    public function process($cart, Collection $cartItems): ?array
    {
        if (!$cart->campaign_id) {
            return null;
        }

        $campaignTotalQty = null;
        $campaignFreeQty = null;
        $campaignRatio = null;
        $campaignContext = null;

        $campaign = $cart->campaign;

        if ($campaign) {
            $campaignTotalQty = $this->campaignService->getCampaignTotalQuantity($cartItems, $campaign);

            if ($campaign->sub_type === 'bonus_product') {
                $rule = $campaign->rules->first();
                $extra = $rule?->extra ?? [];

                $minQty = (int) ($extra['min_quantity'] ?? 0);
                $bonusQty = (int) ($extra['bonus_quantity'] ?? 0);

                if ($minQty > 0 && $bonusQty > 0 && $campaignTotalQty >= $minQty) {
                    $campaignFreeQty = intdiv($campaignTotalQty, $minQty) * $bonusQty;
                }
            }

            if ($campaignTotalQty > 0) {
                $campaignRatio = round($cart->quantity / $campaignTotalQty, 4);
            }
        }

        $campaignProductIds = $campaign->products->pluck('id')->toArray();

        $triggerItems = $cartItems
            ->where('is_campaign_gift', 0)
            ->whereIn('product_id', $campaignProductIds);

        $totalTriggerQty = (int) $triggerItems->sum('quantity');

        $rule = $campaign->rules->first();
        $extra = $rule?->extra ?? [];

        $campaignContext = [
            'campaign_id' => $campaign->id,
            'sub_type' => $campaign->sub_type,
            'trigger_products' => $triggerItems->map(fn ($i) => [
                'product_id' => $i->product_id,
                'quantity' => (int) $i->quantity,
            ])->values()->toArray(),
            'total_trigger_quantity' => $totalTriggerQty,
            'rule' => match ($campaign->sub_type) {
                'tiered_price' => [
                    'tiers' => $extra['tiers'] ?? [],
                ],
                'free_product', 'bonus_product' => [
                    'min_quantity' => $extra['min_quantity'] ?? null,
                ],
                'free_shipping' => [
                    'min_quantity' => $extra['min_quantity'] ?? null,
                    'min_amount' => $extra['min_amount'] ?? null,
                ],
                default => null,
            },
        ];

        return [
            'campaign_total_quantity' => $campaignTotalQty,
            'campaign_free_quantity' => $campaignFreeQty,
            'campaign_row_ratio' => $campaignRatio,
            'campaign_context' => $campaignContext,
        ];
    }
}
