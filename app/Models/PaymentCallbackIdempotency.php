<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentCallbackIdempotency extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'result_success' => 'boolean',
        'resolved_payload' => 'array',
        'processed_at' => 'datetime',
    ];
}
