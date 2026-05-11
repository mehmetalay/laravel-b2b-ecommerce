<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'type',
        'value',
    ];

    public $timestamps = true;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getFileUrlAttribute()
    {
        if ($this->type === 'link') {
            $value = $this->value;
        } else {
            $value = versioned_asset("product-files/{$this->value}");
        }

        return $value;
    }
}
