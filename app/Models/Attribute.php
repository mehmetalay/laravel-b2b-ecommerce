<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attribute extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'attribute_group_id',
        'name',
        'name_en',
        'slug',
        'sort_order',
        'show_in_filter',
        'status'
    ];

    protected $casts = [
        'show_in_filter' => 'boolean',
        'status' => 'boolean'
    ];

    public $timestamps = true;

    public function attributeGroup()
    {
        return $this->belongsTo(AttributeGroup::class);
    }

    public function attributeValues()
    {
        return $this->hasMany(AttributeValue::class)->orderBy('name');
    }
}
