<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol',
        'buy',
        'sell',
        'manual_override',
        'manual_buy',
        'manual_sell',
        'status'
    ];

    protected $casts = [
        'manual_override' => 'boolean',
        'status' => 'boolean',
    ];

    public $timestamps = true;

    public function getSellingPriceAttribute()
    {
        return $this->manual_override
            ? $this->manual_sell
            : $this->sell;
    }

    public function getBuyingPriceAttribute()
    {
        return $this->manual_override
            ? $this->manual_buy
            : $this->buy;
    }
}
