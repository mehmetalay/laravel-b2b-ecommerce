<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'content',
        'dealer_type',
        'is_active',
        'version'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public $timestamps = true;

    public function signatures()
    {
        return $this->hasMany(ContractSignature::class, 'template_id');
    }

    public function getDealerTypeLabelAttribute()
    {
        switch ($this->dealer_type) {
            case 'all':
                return 'Tüm';
                break;

            case 'dealer':
                return 'Bayiler';
                break;

            case 'subdealer':
                return 'Alt Bayiler';
                break;

            default:
                return 'Bilinmiyor';
                break;
        }
    }
}
