<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Survey extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'use_dates',
        'start_at',
        'end_at',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'use_dates' => 'boolean',
        'is_active' => 'boolean'
    ];

    public $timestamps = true;

    public function questions()
    {
        return $this->hasMany(SurveyQuestion::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    // public function answers()
    // {
    //     return $this->hasMany(SurveyAnswer::class, 'survey_id');
    // }

    public function answers()
    {
        return $this->hasManyThrough(
            SurveyAnswer::class,
            SurveyQuestion::class,
            'survey_id',          // SurveyQuestion tablosundaki survey_id
            'survey_question_id', // SurveyAnswer tablosundaki survey_question_id
            'id',                 // Survey tablosundaki id
            'id'                  // SurveyQuestion tablosundaki id
        );
    }
}
