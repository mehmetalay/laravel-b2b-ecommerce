<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Neighborhood extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'district_id',
    ];

    public $timestamps = false;

    /* -------------------------
     | Relationships
     |------------------------- */

    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
