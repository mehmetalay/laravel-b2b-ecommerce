<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Collection;

class CampaignService
{
    public function checkEligibilityForCart(Collection $cartItems, Campaign $campaign) // VAR
    {
        $rule = $campaign->rules->first();
        $extra = $rule->extra ?? [];

        $qty = $this->getCampaignTotalQuantity($cartItems, $campaign);

        $campaignProductIds = $campaign->products->pluck('id')->toArray();

        $price = (float) $cartItems
            ->where('is_campaign_gift', 0)
            ->whereIn('product_id', $campaignProductIds)
            ->reduce(
                fn($s, $i) => $s + ((float) $i->productPrice() * (int) $i->quantity),
                0.0
            );

        switch ($campaign->sub_type) {

            case 'tiered_price':
                return $this->eligibleTiered($qty, $extra);

            case 'free_product':
                return $qty >= ($extra['min_quantity'] ?? 1);

            case 'free_shipping':
                if (isset($extra['min_quantity']) && $qty < (int) $extra['min_quantity']) {
                    return false;
                }
                if (isset($extra['min_amount']) && $price < (float) $extra['min_amount']) {
                    return false;
                }
                return true;

            case 'bonus_product':
                return $qty >= (int) ($extra['min_quantity'] ?? 1);

            default:
                return false;
        }
    }

    private function eligibleTiered($qty, $extra) // VAR
    {
        foreach ($extra['tiers'] ?? [] as $tier) {
            if ($qty >= $tier['min_quantity']) {
                return true;
            }
        }
        return false;
    }

    public function isCampaignEligibleForCart($cartItems, Campaign $campaign) // VAR
    {
        $rule = $campaign->rules->first();
        $extra = $rule->extra ?? [];

        $campaignProductIds = $campaign->products->pluck('id')->toArray();

        $matchingItems = $cartItems->whereIn('product_id', $campaignProductIds);

        if ($matchingItems->isEmpty()) {
            return false;
        }

        $totalQty = (int) $matchingItems->sum('quantity');
        $totalAmount = (float) $matchingItems->reduce(fn ($s, $i) => $s + ((float) $i->productPrice() * (int) $i->quantity), 0.0);

        switch ($campaign->sub_type) {

            case 'tiered_price':
                return $this->eligibleTiered($totalQty, $extra);

            case 'free_product':
                return $totalQty >= (int) ($extra['min_quantity'] ?? 1);

            case 'free_shipping':
                if (!empty($extra['min_quantity']) && $totalQty < (int) $extra['min_quantity']) return false;
                if (!empty($extra['min_amount']) && $totalAmount < (float) $extra['min_amount']) return false;
                return true;

            case 'bonus_product':
                return $totalQty >= (int) ($extra['min_quantity'] ?? 1);
        }

        return false;
    }

    public function getCartCampaignExplanation(Campaign $campaign) // VAR
    {
        $rule = $campaign->rules->first();
        $extra = $rule->extra ?? [];

        switch ($campaign->sub_type) {

            case 'bonus_product':
                return "
                    Bu ürünlerden en az <strong>{$extra['min_quantity']}</strong> adet satın alındığında,
                    <strong>{$extra['bonus_quantity']}</strong> adet bedelsiz.
                ";

                case 'free_product':

                    $giftProducts = collect($extra['gifts'] ?? [])
                        ->map(fn ($id) => Product::find($id))
                        ->filter();

                    $giftListHtml = '<ul class="list-group mt-2">';

                    foreach ($giftProducts as $gift) {
                        $giftListHtml .= '
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>' . e($gift->name) . '</span>
                                <small class="text-muted">' . e($gift->code) . '</small>
                            </li>
                        ';
                    }

                    $giftListHtml .= '</ul>';

                    return "
                        Bu ürünlerden en az <strong>{$extra['min_quantity']}</strong> adet alındığında,
                        <strong>{$extra['gift_quantity']}</strong> adet hediye ürün kazanırsınız:
                        <br>
                        {$giftListHtml}
                    ";

            case 'free_shipping':
                $text = "Bu ürünlerde ücretsiz kargo kampanyası geçerlidir.<br>";

                if (!empty($extra['min_quantity'])) {
                    $text .= "Minimum <strong>{$extra['min_quantity']}</strong> adet satın alma şartı bulunmaktadır.<br>";
                }

                if (!empty($extra['min_amount'])) {
                    $text .= "Toplam sepet tutarının en az <strong>{$extra['min_amount']} ₺</strong> olması gerekmektedir.<br>";
                }

                return $text;

            case 'tiered_price':
                $text = "Bu ürünlerde kademeli indirim uygulanır:<br>";

                foreach ($extra['tiers'] ?? [] as $tier) {
                    $value = match ($tier['price_type'] ?? null) {
                        'percent' => "%{$tier['action_value']} indirim",
                        'fixed'   => "{$tier['action_value']} ₺ indirim",
                        'net'     => "Net fiyat: {$tier['action_value']} ₺",
                        default   => "{$tier['action_value']}",
                    };

                    $text .= "<strong>{$tier['min_quantity']}+</strong> adet → {$value}<br>";
                }

                return $text;
        }

        return "";
    }

    public function buildOrderProductCampaignNote(Cart $cart): ?string // VAR
    {
        if (!$cart->campaign_id) {
            return null;
        }

        $campaign = $cart->campaign;
        if (!$campaign) {
            return null;
        }

        if ((int) $cart->is_campaign_gift === 1) {
            return 'Hediye ürün eklendi. Bu ürün kampanya kapsamında ücretsiz olarak sepete eklenmiştir.';
        }

        $cartItems = app(CartService::class)->carts();
        $totalQty = $this->getCampaignTotalQuantity($cartItems, $campaign);

        $rule = $campaign->rules->first();
        $extra = $rule?->extra ?? [];

        return match ($campaign->sub_type) {

            'tiered_price' => sprintf(
                'Kademeli indirim uygulandı. Bu kampanyaya ait ürünlerden toplam %d adet alındığı için indirim sağlandı.',
                $totalQty
            ),

            'bonus_product' => sprintf(
                'Bonus ürün kampanyası uygulandı. Bu kampanyaya ait ürünlerden toplam %d adet alındığı için bedelsiz ürün kazanıldı.',
                $totalQty
            ),

            'free_product' => sprintf(
                'Hediye ürün kampanyası uygulandı. Bu kampanyaya ait ürünlerden toplam %d adet alındığı için hediye ürün hakkı kazanıldı.',
                $totalQty
            ),

            'free_shipping' => 'Ücretsiz kargo kampanyası uygulandı. Kampanya şartları sağlandığı için kargo ücreti alınmadı.',

            default => null,
        };
    }

    public function freeProductAllowsSameProduct(Campaign $campaign): bool // VAR
    {
        return !empty(
            $campaign->rules->first()?->extra['same_product_gift']
        );
    }

    public function getCampaignTotalQuantity(Collection $cartItems, Campaign $campaign): int // VAR
    {
        $campaignProductIds = $campaign->products->pluck('id');

        return (int) $cartItems
            ->where('is_campaign_gift', 0)
            ->whereIn('product_id', $campaignProductIds)
            ->sum('quantity');
    }

    public function buildCartCampaignNote(Cart $cart): ?string // VAR
    {
        if (!$cart->campaign_id) {
            return null;
        }

        $campaign = $cart->campaign;
        if (!$campaign) {
            return null;
        }

        if ((int) $cart->is_campaign_gift === 1) {
            return 'Bu ürün kampanya kapsamında bedelsiz olarak sepete eklenmiştir.';
        }

        $cartItems = app(CartService::class)->carts();
        $totalQty = $this->getCampaignTotalQuantity($cartItems, $campaign);

        $rule  = $campaign->rules->first();
        $extra = $rule?->extra ?? [];

        return match ($campaign->sub_type) {

            'tiered_price' =>
                "Kademeli indirim uygulandı. Bu kampanyaya ait ürünlerden toplam {$totalQty} adet alındığı için indirim sağlandı.",

            'bonus_product' =>
                "Bonus ürün kampanyası uygulandı. Bu kampanyaya ait ürünlerden toplam {$totalQty} adet alındığı için bedelsiz ürün kazanıldı.",

            'free_product' =>
                "Hediye ürün kampanyası uygulandı. Bu kampanyaya ait ürünlerden toplam {$totalQty} adet alındığı için hediye hakkı kazanıldı.",

            'free_shipping' =>
                "Ücretsiz kargo kampanyası uygulandı. Kampanya şartları sağlandığı için kargo ücreti alınmadı.",

            default => null,
        };
    }
}
