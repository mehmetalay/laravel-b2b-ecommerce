<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttributeValue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'attribute_id',
        'name',
        'name_en',
        'slug',
        'sort_order',
        'show_in_filter',
        'status'
    ];

    protected $casts = [
        'show_in_filter' => 'boolean',
        'status' => 'boolean',
    ];

    public $timestamps = true;

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_attribute_values', 'attribute_value_id', 'product_id');
    }
}
