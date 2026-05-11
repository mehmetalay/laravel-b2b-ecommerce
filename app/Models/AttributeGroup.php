<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttributeGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    public $timestamps = true;

    public function attributes()
    {
        return $this->hasMany(Attribute::class)->orderBy('name');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
