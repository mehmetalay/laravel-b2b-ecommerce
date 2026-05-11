<?php

namespace App\Models;

use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderProduct extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = true;

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getTotalPriceAttribute()
    {
        $productPrice = $this->price;
        $quantity = $this->quantity;
        $discount = $this->discount;
        $campaignDiscount = $this->campaign_discount;

        if ($discount != 0) {
            $productPrice *= (1 - $discount / 100);
        }

        $productPrice *= $quantity;

        return $productPrice - $campaignDiscount;
    }

    public function getProductNameAttribute()
    {
        return $this->product->product_name;
    }

    public function getProductSlugAttribute()
    {
        return $this->product->slug;
    }

    public function getProductCodeAttribute()
    {
        return $this->product->code;
    }

    public function getProductBarcodeAttribute()
    {
        return $this->product->barcode;
    }

    public function getProductNameCodeAttribute()
    {
        return "{$this->product_name} {$this->product_code}";
    }

    public function getFormattedPriceAttribute()
    {
        return $this->formatPrice($this->price);
    }

    public function getFormattedTotalPriceAttribute()
    {
        return $this->formatPrice($this->total_price);
    }

    protected function formatPrice($amount)
    {
        if (auth('subdealer')->check() && !auth('subdealer')->user()->can_view_prices) {
            $amount = 0;
        }

        return app(CurrencyService::class)->formatPrice($amount, $this->order->currency);
    }

    // ETA
    public function getEtaCreatedAtAttribute()
    {
        return from_format($this->created_at, 'Y-m-d\TH:i:s');
    }

    public function getEtaCreatedTimeAttribute()
    {
        return from_format($this->created_at, 'H:i');
    }
}
