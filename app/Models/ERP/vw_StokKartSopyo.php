<?php

namespace App\Models\ERP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vw_StokKartSopyo extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv';
    protected $table = 'vw_StokKartSopyo';
}
