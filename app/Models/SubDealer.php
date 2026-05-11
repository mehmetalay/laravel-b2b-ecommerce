<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubDealer extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $guard = 'subdealer';

    protected $fillable = [
        'dealer_id',
        'name',
        'email',
        'username',
        'phone',
        'password',
        'status',
        'can_place_order',
        'can_approve_order',
        'can_record_payment',
        'can_view_prices',
        'remember_token',
        'last_login_date',
        'last_login_ip',
        'password_must_change',
        'password_reset_code',
        'password_reset_expires_at',
    ];

    protected $casts = [
        'status' => 'boolean',
        'can_place_order' => 'boolean',
        'can_approve_order' => 'boolean',
        'can_record_payment' => 'boolean',
        'can_view_prices' => 'boolean',
        'password_must_change' => 'boolean',
    ];

    protected $hidden = [
        'password',
    ];

    public function dealer()
    {
        return $this->belongsTo(User::class, 'dealer_id', 'current_account_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function getFormattedPhoneAttribute()
    {
        return format_phone_number($this->phone);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'id', 'user_id')->orderBy('id', 'desc');
    }
}
