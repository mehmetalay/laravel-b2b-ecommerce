<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerAddress extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'dealer_id',
        'sub_dealer_id',

        'title',
        'company_name',
        'tax_office',
        'tax_number',
        'phone',

        'city_id',
        'district_id',
        'neighborhood_id',

        'address',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /* -------------------------
     | Relationships
     |------------------------- */

    public function dealer()
    {
        return $this->belongsTo(User::class, 'dealer_id', 'current_account_id');
    }

    public function subDealer()
    {
        return $this->belongsTo(SubDealer::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function neighborhood()
    {
        return $this->belongsTo(Neighborhood::class);
    }

    /* -------------------------
     | Helpers
     |------------------------- */

    public function toSnapshot(): array
    {
        return [
            'title' => $this->title,
            'company_name' => $this->company_name,
            'tax_office' => $this->tax_office,
            'tax_number' => $this->tax_number,
            'phone' => $this->phone,
            'city' => $this->city?->name,
            'district' => $this->district?->name,
            'neighborhood' => $this->neighborhood?->name,
            'address' => $this->address,
        ];
    }
}
