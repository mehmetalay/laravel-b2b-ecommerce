<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackedUpCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'plasiyer_id',
        'sub_dealer_id',
        'cart_discount_rate_tl_1',
        'cart_discount_rate_tl_2',
        'cart_discount_rate_usd_1',
        'cart_discount_rate_usd_2',
        'cart_discount_rate_eur_1',
        'cart_discount_rate_eur_2',
        'cart_discount_rate_gbp_1',
        'cart_discount_rate_gbp_2',
    ];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'current_account_id');
    }

    public function subDealer()
    {
        return $this->belongsTo(SubDealer::class);
    }
}
