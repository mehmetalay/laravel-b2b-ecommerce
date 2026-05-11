<?php

namespace App\Models;

use App\Services\CurrentAccountService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BankIntegration extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'bank_code' => 'string',
    ];
    public $timestamps = true;

    public function installments()
    {
        $account = app(CurrentAccountService::class)->currentAccount();

        $query = $this->hasMany(Installment::class)->orderBy('installment');

        if (!$account || !$account->is_installment_allowed) {
            return $query->where('status', 1)
                        ->where('installment', 1);
        }

        if (!$account->show_all_installments) {
            $query->where('status', 1);
        }

        return $query->where('installment', '<=', (int) $account->max_installment);
    }

    public function allInstallments()
    {
        return $this->hasMany(Installment::class);
    }

    public function oneInstallment()
    {
        return $this->hasOne(Installment::class)->where('installment', 1);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function getFullNameAttribute()
    {
        return "{$this->name} ({$this->company->name})";
    }

    public function getFinalColorAttribute()
    {
        return "background-color: {$this->color}15";
    }

    public function getFinalLogoPathAttribute()
    {
        return image_url($this->logo_path, 'bank_logo');
    }
}
