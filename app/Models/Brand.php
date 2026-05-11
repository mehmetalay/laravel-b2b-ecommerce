<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'image',
        'status',
        'allowed_payment_methods',
        'sort_order',
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    public $timestamps = true;

    public function scopeFilter($query, $filters) { return $filters->apply($query); }
    public function scopeActive($q) { return $q->where('status', 1); }
    public function scopeImage($q) { return $q->whereNotNull('image'); }
    public function getUrlAttribute() { return route('product.brand', $this->slug); }
    public function getImageUrlAttribute() { return $this->image ? image_url($this->image, 'brand') : null; }

    public function isPaymentMethodAllowed(string $method): bool
    {
        $allowed = $this->allowed_payment_methods ?? 'cash,credit,term';
        return in_array($method, explode(',', $allowed));
    }

}
