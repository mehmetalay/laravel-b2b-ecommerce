<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExportJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type',
        'user_id',
        'type',
        'format',
        'scope',
        'filters',
        'selected_ids',
        'status',
        'file_path',
        'file_name',
        'total_rows',
        'error',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'selected_ids' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
