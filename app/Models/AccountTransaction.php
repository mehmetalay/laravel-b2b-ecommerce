<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'transaction_date' => 'datetime',
        'due_date' => 'datetime',
        'meta' => 'array',
        'amount' => 'decimal:4',
    ];
}
