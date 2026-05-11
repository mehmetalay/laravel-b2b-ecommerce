<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SurveyAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'survey_question_id',
        'survey_option_id',
        'dealer_id',
        'answer_text'
    ];

    public $timestamps = true;

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function dealer()
    {
        return $this->belongsTo(User::class, 'dealer_id', 'current_account_id');
    }

    public function delaer()
    {
        return $this->dealer();
    }
}
