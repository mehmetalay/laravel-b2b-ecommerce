<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProductPrice extends Model
{
    protected $fillable = [
        'dealer_id',
        'product_id',
        'special_list_price', 'special_discount1', 'special_discount2', 'special_discount3', 'special_net_price',
        'list_price', 'list_discount1', 'list_discount2', 'list_discount3', 'list_net_price', 'list_gross_price',
        'cash_list_price', 'cash_discount1', 'cash_discount2', 'cash_discount3', 'cash_net_price', 'cash_gross_price',
        'credit_list_price', 'credit_discount1', 'credit_discount2', 'credit_discount3', 'credit_net_price', 'credit_gross_price',
        'term_list_price', 'term_discount1', 'term_discount2', 'term_discount3', 'term_net_price', 'term_gross_price',
    ];

    public $timestamps = true;
}
