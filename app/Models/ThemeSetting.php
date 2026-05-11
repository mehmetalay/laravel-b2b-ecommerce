<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ThemeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'copyright', 'copyright_en', 'footer_about_us_text', 'footer_about_us_text_en', 'facebook', 'instagram', 'twitter', 'pinterest', 'youtube', 'linkedin', 'whatsapp',
    ];

    protected $casts = [
        //
    ];

    public $timestamps = true;
}
