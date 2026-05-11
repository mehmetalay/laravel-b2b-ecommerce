<?php

namespace App\Application\Survey\Repositories;

use App\Models\Survey;
use App\Models\SurveyAnswer;

class SurveyAnswerRepository
{
    public function hasDealerParticipated(int $surveyId, int $dealerId): bool
    {
        return SurveyAnswer::query()
            ->where('survey_id', $surveyId)
            ->where('dealer_id', $dealerId)
            ->exists();
    }

    public function countDistinctParticipants(int $surveyId): int
    {
        return SurveyAnswer::query()
            ->where('survey_id', $surveyId)
            ->distinct('dealer_id')
            ->count('dealer_id');
    }

    public function createMany(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        SurveyAnswer::query()->insert($rows);
    }

    public function hasAnyParticipant(Survey $survey): bool
    {
        return SurveyAnswer::query()
            ->where('survey_id', $survey->id)
            ->exists();
    }
}

