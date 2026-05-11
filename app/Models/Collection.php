<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = true;

    public function collectionCheques()
    {
        return $this->hasMany(CollectionCheque::class);
    }

    public function collectionPromissories()
    {
        return $this->hasMany(CollectionPromissory::class);
    }

    public function plasiyer()
    {
        return $this->belongsTo(User::class, 'plasiyer_id', 'current_account_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'current_account_id');
    }

    public function subDealer()
    {
        return $this->belongsTo(SubDealer::class);
    }
}
