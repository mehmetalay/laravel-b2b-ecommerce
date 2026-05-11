<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'image',
        'image_en',
        'parent_id',
        'status',
        'sort_order',
        'stock_display_limit'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    public $timestamps = true;

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->orderBy('sort_order', 'asc')
            ->activeAndNotDeleted();
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function allChildren()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function scopeFilter($query, $filters) { return $filters->apply($query); }

    public function scopeWithRelations($query)
    {
        return $query->with(['children']);
    }

    public function scopeParent($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeActiveAndNotDeleted($query)
    {
        return $query->where('status', 1);
    }

    public function scopeNotDeleted($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeExcludeHidden($query)
    {
        return $query->whereNotIn('id', hide_category_ids());
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }

        return image_url($this->image, 'category.desktop');
    }

    public function getUrlAttribute()
    {
        return route('product.list', $this->slug);
    }

    public function getHomepageImageAttribute()
    {
        $locale = app()->getLocale();

        if ($locale === 'en' && $this->image_en) {
            return image_url($this->image_en, 'category.desktop');
        }

        if ($this->image) {
            return image_url($this->image, 'category.desktop');
        }

        return null;
    }
}
