<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class DealerApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name',
        'tax_office',
        'tax_number',
        'city',
        'district',
        'address',
        'authorized_name_surname',
        'identity_number',
        'phone_number',
        'mobile_phone_number',
        'fax_number',
        'email_address',
        'web_address',
        'ip_address',
        'email_sent',
    ];

    protected $casts = [
        'email_sent' => 'boolean',
    ];

    public $timestamps = true;

    protected static function booted(): void
    {
        static::deleting(function (self $application): void {
            if (!$application->isForceDeleting()) {
                return;
            }

            $paths = $application->documents()
                ->pluck('path')
                ->filter(fn ($path) => is_string($path) && $path !== '')
                ->values()
                ->all();

            if (!empty($paths)) {
                Storage::delete($paths);
            }

            $application->documents()->delete();
        });
    }

    public function documents()
    {
        return $this->hasMany(DealerApplicationDocument::class, 'dealer_application_id')
            ->orderBy('id');
    }
}
