<?php

namespace App\Services;

use App\Models\{Cart, Product, Campaign};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AutoApplyCampaignService
{
    public function __construct(
        protected CampaignService $campaignService,
        protected CartService $cartService
    ) {}

    public function sync(?Collection $cartItems = null): void
    {
        if (app()->has('campaign_sync_running')) {
            return;
        }

        app()->instance('campaign_sync_running', true);

        try {
            $cartItems ??= $this->cartService->carts();

            foreach ($cartItems->where('is_campaign_gift', 0) as $item) {

                if ((int) $item->is_campaign_gift === 1) {
                    continue;
                }

                if ($item->campaign_id) {

                    $currentCampaign = Campaign::with(['rules','products'])
                        ->activeAndValid()
                        ->find((int) $item->campaign_id);

                    if (!$currentCampaign) {
                        $this->cartService->removeCampaignGifts((int) $item->campaign_id);
                        $item->update(['campaign_id' => null, 'campaign_rule_type' => null]);
                        continue;
                    }

                    if ((int) $currentCampaign->auto_apply === 0) {
                        if (!$this->campaignService->checkEligibilityForCart($cartItems, $currentCampaign)) {
                            $this->cartService->removeCampaignGifts((int) $currentCampaign->id);
                            $item->update(['campaign_id' => null, 'campaign_rule_type' => null]);
                        } else {
                            if ($item->campaign_rule_type !== $currentCampaign->sub_type) {
                                $item->update(['campaign_rule_type' => $currentCampaign->sub_type]);
                            }
                        }
                        continue;
                    }
                }

                $autoCampaign = $this->pickAutoCampaignForCartItem($item);

                if (!$autoCampaign) {
                    if ($item->campaign_id || $item->campaign_rule_type) {
                        $this->cartService->removeCampaignGifts((int) $item->campaign_id);
                        $item->update(['campaign_id' => null, 'campaign_rule_type' => null]);
                    }
                    continue;
                }

                if ($item->campaign_id && (int) $item->campaign_id !== (int) $autoCampaign->id) {
                    $this->cartService->removeCampaignGifts((int) $item->campaign_id);
                }

                if ((int) $item->campaign_id !== (int) $autoCampaign->id || $item->campaign_rule_type !== $autoCampaign->sub_type) {
                    $item->update([
                        'campaign_id' => $autoCampaign->id,
                        'campaign_rule_type' => $autoCampaign->sub_type,
                    ]);
                }
            }

            $hasFreeShipping = $this->cartService->carts()
                ->where('is_campaign_gift', 0)
                ->where('campaign_rule_type', 'free_shipping')
                ->count() > 0;

            if ($hasFreeShipping) session()->put('cart_free_shipping_active', true);
            else session()->forget('cart_free_shipping_active');

            $this->cleanupInvalidGifts($this->cartService->carts());
        } finally {
            app()->forgetInstance('campaign_sync_running');
        }
    }

    protected function pickAutoCampaignForCartItem(Cart $cart): ?Campaign
    {
        $optOuts = collect(session('campaign_opt_outs', []))->map(fn($v) => (int) $v);

        $candidates = Campaign::query()
            ->activeAndValid()
            ->where('type', 'product')
            ->where('auto_apply', 1)
            ->whereNotIn('id', $optOuts)
            ->whereHas('products', fn($q) => $q->where('product_id', $cart->product_id))
            ->with(['rules', 'products'])
            ->orderByDesc('id')
            ->get();

        $cartItems = $this->cartService->carts();

        foreach ($candidates as $campaign) {
            if ($this->campaignService->checkEligibilityForCart($cartItems, $campaign)) {
                return $campaign;
            }
        }

        return null;
    }

    protected function cleanupInvalidGifts(Collection $cartItems): void
    {
        $campaignIds = $cartItems
            ->pluck('campaign_id')
            ->filter()
            ->unique()
            ->values();

        foreach ($campaignIds as $cid) {
            if (session()->get('manual_gift_removed.' . $cid)) {
                continue;
            }

            $triggerItems = $cartItems
                ->where('is_campaign_gift', 0)
                ->where('campaign_id', (int) $cid);

            if ($triggerItems->isEmpty()) {
                $this->cartService->removeCampaignGifts((int) $cid);
                continue;
            }

            $campaign = Campaign::with(['rules','products'])
                ->activeAndValid()
                ->find((int)$cid);

            if (!$campaign || $campaign->sub_type !== 'free_product') {
                $this->cartService->removeCampaignGifts((int) $cid);
                continue;
            }

            $rule = $campaign->rules->first();
            $extra = $rule?->extra ?? [];

            $expectedTotal = $this->cartService->expectedFreeProductTotalGifts($cartItems, $campaign);

            if ($expectedTotal <= 0) {
                $this->cartService->removeCampaignGifts((int) $cid);
                continue;
            }

            $giftIds = $extra['gifts'] ?? [];
            if (!is_array($giftIds)) $giftIds = [$giftIds];
            $giftIds = array_values(array_unique(array_map('intval', $giftIds)));

            if (count($giftIds) === 1) {

                $giftProductId = (int) $giftIds[0];

                $currentAccountService = app(CurrentAccountService::class);
                $userQuery = $currentAccountService->userQuery();
                $paymentType = session()->get('cart_payment_type');

                $base = Cart::query()
                    ->where('plasiyer_id', ($userQuery['plasiyer_id'] ?? null))
                    ->where('user_id', $userQuery['user_id'])
                    ->where('sub_dealer_id', ($userQuery['sub_dealer_id'] ?? null))
                    ->where('payment_type', $paymentType)
                    ->active();

                (clone $base)
                    ->where('campaign_id', (int) $cid)
                    ->where('is_campaign_gift', 1)
                    ->where('product_id', '!=', $giftProductId)
                    ->delete();

                $existing = (clone $base)
                    ->where('campaign_id', (int) $cid)
                    ->where('is_campaign_gift', 1)
                    ->where('product_id', $giftProductId)
                    ->first();

                if ($existing) {
                    if ((int) $existing->quantity !== (int) $expectedTotal) {
                        DB::table('carts')
                            ->where('id', (int) $existing->id)
                            ->update([
                                'quantity' => (int) $expectedTotal,
                                'updated_at' => now(),
                            ]);
                    }
                } else {
                    $product = Product::find($giftProductId);
                    if ($product) {
                        $result = app(CurrencyResolverService::class)->resolve(
                            $product,
                            $paymentType,
                            $currentAccountService
                        );

                        $productCurrency = $result['productCurrency'];
                        $exchangeType = $result['exchangeType'];
                        $orderSeparately = $result['orderSeparately'];

                        $this->cartService->createRaw([
                            'product_id' => $giftProductId,
                            'quantity' => (int)$expectedTotal,
                            'plasiyer_id' => $userQuery['plasiyer_id'] ?? null,
                            'user_id' => $userQuery['user_id'],
                            'sub_dealer_id' => $userQuery['sub_dealer_id'] ?? null,
                            'currency' => $productCurrency,
                            'exchange_type' => $exchangeType,
                            'order_separately' => $orderSeparately,
                            'payment_type' => $paymentType,
                            'campaign_id' => (int) $cid,
                            'is_campaign_gift' => 1,
                            'campaign_rule_type' => 'free_product',
                        ]);
                    }
                }

                continue;
            }

            $giftRows = $cartItems
                ->where('is_campaign_gift', 1)
                ->where('campaign_id', (int)$cid)
                ->values();

            if ($giftRows->isEmpty()) {
                continue;
            }

            $isStackable = (int)($extra['is_stackable'] ?? 0) === 1;

            if (!$isStackable) {

                $rowCount = $giftRows->count();
                $currentTotal = (int) $giftRows->sum('quantity');

                if ($rowCount > $expectedTotal) {
                    $this->cartService->removeCampaignGifts((int)$cid);
                    continue;
                }

                if ($rowCount === 1) {
                    $g = $giftRows->first();
                    if ((int) $g->quantity !== (int)$expectedTotal) {
                        DB::table('carts')->where('id', (int) $g->id)->update([
                            'quantity' => (int) $expectedTotal,
                            'updated_at' => now(),
                        ]);
                    }
                    continue;
                }

                if ($currentTotal <= 0) {
                    $baseQty = intdiv($expectedTotal, $rowCount);
                    $rem = $expectedTotal % $rowCount;

                    foreach ($giftRows as $g) {
                        $qty = $baseQty + ($rem-- > 0 ? 1 : 0);
                        DB::table('carts')->where('id', (int) $g->id)->update([
                            'quantity' => (int) $qty,
                            'updated_at' => now(),
                        ]);
                    }
                    continue;
                }

                $remaining = $expectedTotal - $rowCount;

                $weights = [];
                $sumW = 0;
                foreach ($giftRows as $g) {
                    $w = max(0, (int) $g->quantity);
                    $weights[$g->id] = $w;
                    $sumW += $w;
                }
                if ($sumW <= 0) $sumW = 1;

                $floats = [];
                foreach ($giftRows as $g) {
                    $floats[$g->id] = ($weights[$g->id] / $sumW) * $remaining;
                }

                $adds = [];
                $sumAdds = 0;
                foreach ($giftRows as $g) {
                    $baseAdd = (int) floor($floats[$g->id]);
                    $adds[$g->id] = $baseAdd;
                    $sumAdds += $baseAdd;
                }

                $left = $remaining - $sumAdds;

                if ($left > 0) {
                    $remainders = [];
                    foreach ($giftRows as $g) {
                        $remainders[$g->id] = $floats[$g->id] - floor($floats[$g->id]);
                    }
                    arsort($remainders);

                    foreach ($remainders as $rowId => $remFloat) {
                        if ($left <= 0) break;
                        $adds[$rowId] += 1;
                        $left--;
                    }
                }

                foreach ($giftRows as $g) {
                    $newQty = 1 + (int) ($adds[$g->id] ?? 0);
                    if ((int) $g->quantity !== (int) $newQty) {
                        DB::table('carts')->where('id', (int)$g->id)->update([
                            'quantity' => (int) $newQty,
                            'updated_at' => now(),
                        ]);
                    }
                }

                continue;
            }

            $n = $giftRows->count();

            if ($expectedTotal < $n) {
                $this->cartService->removeCampaignGifts((int) $cid);
                continue;
            }

            $currentTotal = (int) $giftRows->sum('quantity');

            if ($currentTotal <= 0) {
                $this->cartService->removeCampaignGifts((int) $cid);
                continue;
            }

            if ($currentTotal === $expectedTotal) {
                continue;
            }

            $ratios = [];
            foreach ($giftRows as $g) {
                $ratios[$g->id] = ((int) $g->quantity) / $currentTotal;
            }

            $floats = [];
            foreach ($giftRows as $g) {
                $floats[$g->id] = $ratios[$g->id] * $expectedTotal;
            }

            $targets = [];
            $sumBase = 0;
            foreach ($giftRows as $g) {
                $baseQty = (int) floor($floats[$g->id]);
                $targets[$g->id] = $baseQty;
                $sumBase += $baseQty;
            }

            $remaining = $expectedTotal - $sumBase;

            if ($remaining > 0) {
                $remainders = [];
                foreach ($giftRows as $g) {
                    $remainders[$g->id] = $floats[$g->id] - floor($floats[$g->id]);
                }
                arsort($remainders);

                foreach ($remainders as $rowId => $remFloat) {
                    if ($remaining <= 0) break;
                    $targets[$rowId] += 1;
                    $remaining--;
                }
            }

            foreach ($giftRows as $g) {
                $newQty = (int) ($targets[$g->id] ?? 0);
                if ($newQty < 0) $newQty = 0;

                if ((int) $g->quantity !== (int) $newQty) {
                    DB::table('carts')
                        ->where('id', (int) $g->id)
                        ->update([
                            'quantity' => $newQty,
                            'updated_at' => now(),
                        ]);
                }
            }
        }
    }
}
