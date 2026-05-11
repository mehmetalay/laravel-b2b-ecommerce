<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractShipLocation extends Model
{
    protected $guarded = [];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
