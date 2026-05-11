<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $timestamps = true;

    public function bankAccounts()
    {
        return $this->hasMany(ContractBankAccount::class)->orderBy('sort_order');
    }

    public function emails()
    {
        return $this->hasMany(ContractEmail::class)->orderBy('sort_order');
    }

    public function gsms()
    {
        return $this->hasMany(ContractGsm::class)->orderBy('sort_order');
    }

    public function shipLocations()
    {
        return $this->hasMany(ContractShipLocation::class)->orderBy('sort_order');
    }
}
