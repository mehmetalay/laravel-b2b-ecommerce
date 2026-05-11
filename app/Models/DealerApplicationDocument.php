<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealerApplicationDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'dealer_application_id',
        'path',
    ];

    public $timestamps = true;

    public function application()
    {
        return $this->belongsTo(DealerApplication::class, 'dealer_application_id');
    }
}
