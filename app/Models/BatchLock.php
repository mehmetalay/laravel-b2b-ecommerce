<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchLock extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_name',
        'is_running',
        'started_at',
        'finished_at',
    ];

    public $timestamps = true;
}
