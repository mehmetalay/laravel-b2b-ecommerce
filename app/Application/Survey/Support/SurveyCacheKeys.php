<?php

namespace App\Application\Survey\Support;

class SurveyCacheKeys
{
    public const ALL = 'survey:all';
    public const ACTIVE = 'survey:active';

    public static function detail(int $surveyId): string
    {
        return 'survey:detail:' . $surveyId;
    }
}

