<?php

namespace App\Application\Survey\Enums;

enum SurveyQuestionType: string
{
    case SINGLE = 'single';
    case MULTIPLE = 'multiple';
    case TEXT = 'text';
}

