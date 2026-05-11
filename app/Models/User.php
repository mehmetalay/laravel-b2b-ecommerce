<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $guarded = [];
    public $timestamps = true;

    protected $casts = [
        'status' => 'boolean',
        'report_access' => 'boolean',
        'block_entry' => 'boolean',
        'show_all_installments' => 'boolean',
        'hide_all_prices' => 'boolean',
        'hide_all_stock_quantities' => 'boolean',
        'group_by_product_code' => 'boolean',
        'is_order_closed' => 'boolean',
        'password_must_change' => 'boolean',
        'receipt_enabled' => 'boolean',
        'can_collect_payments' => 'boolean',
        'can_edit_price' => 'boolean',
        'can_edit_discount' => 'boolean',
        'is_installment_allowed' => 'boolean',
    ];

    protected $dates = [
        'password_reset_expires_at',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeSalesman($query)
    {
        return $query->where('role', 'salesman')->whereBetween('current_account_id', [1, 240]);
    }

    public function scopeCustomer($query)
    {
        return $query->where('role', 'dealer')->where('current_account_id', '>=', 241);
    }

    public function scopeNotBlocked($query)
    {
        return $query->where('block_entry', 0);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'current_account_id', 'user_id')->orderBy('id', 'desc');
    }

    public function salesmann()
    {
        return $this->belongsTo(User::class, 'plasiyer1', 'code');
    }

    public function getSalesmanNameAttribute()
    {
        return $this->salesmann ? $this->salesmann->name : '';
    }

    public function getSalesmanCodeAttribute()
    {
        return $this->salesmann ? $this->salesmann->code : '';
    }

    public function getSalesmanPhoneAttribute()
    {
        return $this->salesmann ? $this->salesmann->phone : '';
    }

    public function subDealer()
    {
        return $this->belongsTo(SubDealer::class, 'dealer_id');
    }

    public function subDealers()
    {
        return $this->hasMany(SubDealer::class, 'dealer_id');
    }
}
