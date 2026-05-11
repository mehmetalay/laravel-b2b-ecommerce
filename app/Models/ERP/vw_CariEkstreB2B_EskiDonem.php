<?php

namespace App\Models\ERP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vw_CariEkstreB2B_EskiDonem extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv_legacy';
    protected $table = 'vw_CariEkstreB2B_EskiDonem';
}
