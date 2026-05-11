<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'status' => 'boolean',
        'use_date_filter' => 'boolean',
        'auto_apply' => 'boolean',
    ];

    public $timestamps = true;

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeWithinDateRange($query)
    {
        return $query->where(function ($query) {
            $query->where('use_date_filter', 0)
                ->orWhere(function ($query) {
                    $query->where('use_date_filter', 1)
                        ->where('start_date', '<=', now())
                        ->where('end_date', '>=', now());
                });
        });
    }

    public function scopeActiveAndValid($query)
    {
        return $query->where('status', 1)
            ->where(function ($query) {
                $query->where('use_date_filter', 0)
                    ->orWhere(function ($query) {
                        $query->where('use_date_filter', 1)
                            ->where('start_date', '<=', now())
                            ->where('end_date', '>=', now());
                    });
            });
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'campaign_products')
            ->withTimestamps();
    }

    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'campaign_products');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'campaign_products');
    }

    public function rules()
    {
        return $this->hasMany(CampaignRule::class);
    }

    /**
     * Kampanya için özet açıklama döndürür.
     * general_description doluysa onu, boşsa kural verisinden otomatik özet üretir.
     */
    public function getDescriptionSummary(): ?string
    {
        if (!empty($this->general_description)) {
            return $this->general_description;
        }

        return $this->generateAutoDescription();
    }

    /**
     * Kampanya kurallarından otomatik özet metni üretir.
     */
    public function generateAutoDescription(): ?string
    {
        $rule = $this->rules->first();
        if (!$rule) {
            return null;
        }

        $extra = $rule->extra ?? [];

        switch ($this->sub_type) {
            case 'tiered_price':
                $lines = [];

                foreach ($extra['tiers'] ?? [] as $tier) {
                    $discount = match ($tier['price_type'] ?? null) {
                        'percent' => '%' . $tier['action_value'] . ' indirim',
                        'fixed'   => number_format($tier['action_value'], 2, ',', '.') . ' ₺ indirim',
                        'net'     => 'Net fiyat: ' . number_format($tier['action_value'], 2, ',', '.') . ' ₺',
                        default   => (string) $tier['action_value'],
                    };

                    $lines[] = $tier['min_quantity'] . '+ adet → ' . $discount;
                }

                return implode(' | ', $lines) ?: null;

            case 'free_product':
                $minQty = $extra['min_quantity'] ?? 1;
                $giftQty = $extra['gift_quantity'] ?? 1;
                return $minQty . '+ adet alana ' . $giftQty . ' adet hediye ürün';

            case 'free_shipping':
                $minQty = $extra['min_quantity'] ?? null;
                $minAmount = $extra['min_amount'] ?? null;
                if ($minQty) {
                    return $minQty . '+ adet alımda bedelsiz kargo';
                }
                if ($minAmount) {
                    return number_format($minAmount, 2, ',', '.') . ' ₺ üzeri alışverişte bedelsiz kargo';
                }
                return 'Bedelsiz kargo kampanyası';

            case 'bonus_product':
                $minQty = $extra['min_quantity'] ?? 0;
                $bonusQty = $extra['bonus_quantity'] ?? 0;
                return $minQty . ' adet al, ' . $bonusQty . ' adet bedava';

            default:
                return null;
        }
    }
}
