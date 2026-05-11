<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SurveyOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_question_id',
        'option_text',
        'sort_order'
    ];

    public $timestamps = true;

    public function question()
    {
        return $this->belongsTo(SurveyQuestion::class, 'survey_question_id');
    }
}