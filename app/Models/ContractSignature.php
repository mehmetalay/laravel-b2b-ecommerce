<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'actor_type',
        'template_id',
        'status',
        'signed_at',
        'ip_address',
        'sms_code',
        'sms_message_id',
        'token',
        'pdf_path'
    ];

    public $timestamps = true;

    public function template()
    {
        return $this->belongsTo(ContractTemplate::class, 'template_id');
    }

    public function getActorNameAttribute()
    {
        if ($this->actor_type === 'dealer') {
            return $this->dealer->name;
        }

        if ($this->actor_type === 'subdealer') {
            return $this->subdealer->name;
        }
    }

    public function dealer()
    {
        return $this->belongsTo(User::class, 'user_id', 'current_account_id');
    }

    public function subdealer()
    {
        return $this->belongsTo(SubDealer::class, 'user_id')->withTrashed();
    }

    public function getActorTypeLabelAttribute()
    {
        switch ($this->actor_type) {
            case 'dealer':
                return 'Bayi';
                break;

            case 'subdealer':
                return 'Alt Bayi';
                break;

            default:
                return 'Bilinmiyor';
                break;
        }
    }
}
