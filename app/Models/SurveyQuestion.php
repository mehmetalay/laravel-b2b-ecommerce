<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SurveyQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'question',
        'type',
        'sort_order'
    ];

    public $timestamps = true;

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function answers()
    {
        return $this->hasMany(SurveyAnswer::class, 'survey_question_id');
    }

    public function options()
    {
        return $this->hasMany(SurveyOption::class, 'survey_question_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
