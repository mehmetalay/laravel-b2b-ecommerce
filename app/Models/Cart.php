<?php

namespace App\Models;

use App\Services\CartService;
use App\Services\CampaignService;
use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Model;
use App\Services\PriceCalculatorService;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plasiyer_id',
        'sub_dealer_id',
        'product_id',
        'quantity',
        'discount',
        'explanation',
        'ordered',
        'backed_up',
        'backed_up_cart_id',
        'currency',
        'exchange_type',
        'order_separately',
        'payment_type',
        'campaign_id',
        'trigger_cart_id',
        'is_campaign_gift',
        'campaign_rule_type',
        'is_manual_override',
    ];

    protected $casts = [
        'is_campaign_gift' => 'boolean',
        'is_manual_override' => 'boolean',
    ];

    public $timestamps = true;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function scopeActive($query)
    {
        return $query->where('ordered', 0)->where('backed_up', 0);
    }

    public function scopeOrdered($query, $value = 0)
    {
        return $query->where('ordered', $value);
    }

    public function scopeBackedUp($query, $value = 0)
    {
        return $query->where('backed_up', $value);
    }

    public function productPrice($quantity = 0, $discount = 0) // OK
    {
        $price = app(CurrencyService::class)->convert(
            $this->product->productPrice('list'),
            $this->exchange_type,
            $this->product
        );

        return app(PriceCalculatorService::class)->calculate($price, $quantity, $discount);
    }

    public function productPriceShow($quantity = 0, $discount = 0, $applyCampaign = false) // OK
    {
        $currencyService = app(CurrencyService::class);
        $priceCalculatorService = app(PriceCalculatorService::class);

        $currency = $this->currency;

        $base = $currencyService->convert($this->product->productPrice('list'), $this->exchange_type, $this->product);
        $price = $priceCalculatorService->calculate($base, $quantity, $discount);

        if ($applyCampaign) {
            $campaignDiscount = $this->campaign_discount;

            // Per-unit gösterimde (quantity <= 1) toplam kampanya indirimini birim başına böl
            if ($quantity <= 1 && (int) $this->quantity > 0) {
                $campaignDiscount = $campaignDiscount / (int) $this->quantity;
            }

            $price -= $campaignDiscount;
        }

        return $priceCalculatorService->formatPriceForDisplay($price, null, $currency, null !== null);
    }

    public function getProductBoxQuantityAttribute()
    {
        return $this->product->box_quantity <= 0 ? 1 : $this->product->box_quantity;
    }

    /**
     * Kampanya aktifse (tiered_price) ödeme tipi iskontosunu sıfırlar.
     * Tüm total hesaplamalarında $item->discount yerine $item->effective_discount kullanılmalı.
     */
    public function getEffectiveDiscountAttribute(): float
    {
        if ($this->campaign_id && $this->campaign_rule_type === 'tiered_price') {
            return 0;
        }

        return (float) ($this->discount ?? 0);
    }

    public function getLineDiscountAttribute()
    {
        // tiered_price kampanyası aktifse ödeme tipi iskontosunu devre dışı bırak
        if ($this->campaign_id && $this->campaign_rule_type === 'tiered_price') {
            return 0;
        }

        $unitPrice = $this->productPrice($this->quantity, 0);
        $discountedUnitPrice = $this->productPrice($this->quantity, $this->discount);

        return ($unitPrice - $discountedUnitPrice);
    }

    public function getCampaignDiscountAttribute()
    {
        if ((int) $this->is_campaign_gift === 1) {
            $unitPrice = $this->productPrice(1, $this->discount);
            return $unitPrice * (int) $this->quantity;
        }

        if (!(int) $this->campaign_id) return 0;

        $campaign = $this->campaign ?: Campaign::with(['rules','products'])
            ->activeAndValid()
            ->find($this->campaign_id);

        if (!$campaign) return 0;

        if (!$campaign->products->contains($this->product_id)) {
            return 0;
        }

        $rule = $campaign->rules->first();
        $extra = $rule?->extra ?? [];

        switch ($campaign->sub_type) {

            case 'tiered_price':

                $tiers = $extra['tiers'] ?? [];
                if (empty($tiers)) return 0;

                usort($tiers, fn($a, $b) =>
                    ($b['min_quantity'] ?? 0) <=> ($a['min_quantity'] ?? 0)
                );

                $cartItems = app(CartService::class)->carts();
                $totalQty  = app(CampaignService::class)->getCampaignTotalQuantity($cartItems, $campaign);

                $unitPrice = $this->productPrice(1, 0); // iskonto öncesi birim fiyat

                foreach ($tiers as $tier) {
                    $minQ = (int) ($tier['min_quantity'] ?? 0);
                    if ($minQ <= 0) continue;
                    if ($totalQty < $minQ) continue;

                    $type = $tier['price_type'] ?? null;
                    $val  = (float) ($tier['action_value'] ?? 0);
                    $qty  = (int) $this->quantity;

                    if ($type === 'percent') {
                        return ($unitPrice * ($val / 100)) * $qty;
                    }

                    if ($type === 'fixed') {
                        return $val * $qty;
                    }

                    if ($type === 'net') {
                        $discountPerUnit = max(0, $unitPrice - $val);
                        return $discountPerUnit * $qty;
                    }
                }

                return 0;

            case 'bonus_product':

                $minQty   = (int) ($extra['min_quantity'] ?? 0);
                $bonusQty = (int) ($extra['bonus_quantity'] ?? 0);

                if ($minQty <= 0 || $bonusQty <= 0) return 0;

                $cartItems = app(CartService::class)->carts();
                $totalQty  = app(CampaignService::class)->getCampaignTotalQuantity($cartItems, $campaign);

                if ($totalQty < $minQty) return 0;

                $freeItemsTotal = intdiv($totalQty, $minQty) * $bonusQty;
                if ($freeItemsTotal <= 0) return 0;

                $ratio = $totalQty > 0
                    ? ((int)$this->quantity / $totalQty)
                    : 0;

                $freeItemsForRow = $freeItemsTotal * $ratio;

                $unitPrice = $this->productPrice(1, $this->discount);

                return $freeItemsForRow * $unitPrice;

            default:
                return 0;
        }
    }

    public function getCampaignDiscountPercentAttribute(): float
    {
        if ((int) $this->is_campaign_gift === 1) {
            return 100.0;
        }

        if (!(int) $this->campaign_id) return 0.0;

        $campaign = $this->campaign ?: Campaign::with(['rules','products'])
            ->activeAndValid()
            ->find($this->campaign_id);

        if (!$campaign) return 0.0;

        if (!$campaign->products->contains($this->product_id)) {
            return 0.0;
        }

        $rule = $campaign->rules->first();
        $extra = $rule?->extra ?? [];

        // tiered_price ise iskontosuz fiyat üzerinden oranla, değilse iskontolu
        $usedDiscount = ($campaign->sub_type === 'tiered_price') ? 0 : $this->discount;
        $unitPrice = (float) $this->productPrice(1, $usedDiscount);
        if ($unitPrice <= 0) return 0.0;

        switch ($campaign->sub_type) {

            case 'tiered_price':

                $tiers = $extra['tiers'] ?? [];
                if (empty($tiers)) return 0.0;

                usort($tiers, fn($a, $b) =>
                    ($b['min_quantity'] ?? 0) <=> ($a['min_quantity'] ?? 0)
                );

                $cartItems = app(CartService::class)->carts();
                $totalQty = app(CampaignService::class)->getCampaignTotalQuantity($cartItems, $campaign);

                foreach ($tiers as $tier) {
                    $minQ = (int) ($tier['min_quantity'] ?? 0);
                    if ($minQ <= 0) continue;
                    if ($totalQty < $minQ) continue;

                    $type = $tier['price_type'] ?? null;
                    $val  = (float) ($tier['action_value'] ?? 0);

                    if ($unitPrice <= 0) {
                        return 0.0;
                    }

                    if ($type === 'percent') {
                        return round($val, 2);
                    }

                    if ($type === 'fixed') {
                        return round(($val / $unitPrice) * 100, 2);
                    }

                    if ($type === 'net') {
                        $discountAmount = max(0, $unitPrice - $val);
                        return round(($discountAmount / $unitPrice) * 100, 2);
                    }
                }

                return 0.0;

            case 'bonus_product':

                $minQty   = (int) ($extra['min_quantity'] ?? 0);
                $bonusQty = (int) ($extra['bonus_quantity'] ?? 0);

                if ($minQty <= 0 || $bonusQty <= 0) return 0.0;

                $cartItems = app(CartService::class)->carts();
                $totalQty  = app(CampaignService::class)->getCampaignTotalQuantity($cartItems, $campaign);

                if ($totalQty < $minQty) return 0.0;

                $freeItemsTotal = intdiv($totalQty, $minQty) * $bonusQty;
                if ($freeItemsTotal <= 0) return 0.0;

                $percent = ($freeItemsTotal / $totalQty) * 100;

                return round($percent, 2);

            default:
                return 0.0;
        }
    }

    public function getCampaignNoteAttribute(): ?string
    {
        return app(CampaignService::class)->buildCartCampaignNote($this);
    }
}
