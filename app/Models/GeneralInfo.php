<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GeneralInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'company_official_name',
        'company_website',
        'authorized_person',
        'company_phone_number',
        'company_phone_number_2',
        'company_mobile_number',
        'fax_number',
        'email_address',
        'email_address_2',
        'company_full_address',
        'seo_meta_title',
        'seo_meta_description',
        'seo_meta_keywords',
        'google_maps_link',
        'google_maps_embed',
    ];

    public $timestamps = true;
}
