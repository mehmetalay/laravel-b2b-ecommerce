<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomepageBlock extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'title_tr',
        'subtitle_tr',
        'title_en',
        'subtitle_en',
        'is_active',
        'sort_order'
    ];

    public $timestamps = true;

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'homepage_block_products')
            ->where('products.status', 1);
    }

    public function getRandomProducts($limit = 20)
    {
        return $this->products()
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}
