<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CargoCompany extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_default',
        'status',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'status' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
