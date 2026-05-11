<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignRule extends Model
{
    protected $guarded = [];

    protected $casts = [
        'extra' => 'array',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function getRuleType()
    {
        return $this->rule_type;
    }
}
