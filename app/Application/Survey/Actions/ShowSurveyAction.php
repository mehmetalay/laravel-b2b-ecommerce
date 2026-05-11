<?php

namespace App\Application\Survey\Actions;

use App\Application\Survey\Services\SurveyWorkflowService;
use App\Models\Survey;
use Illuminate\Contracts\Auth\Authenticatable;

class ShowSurveyAction
{
    public function __construct(
        private SurveyWorkflowService $surveyWorkflowService
    ) {}

    public function __invoke(Survey $survey, ?Authenticatable $user): Survey
    {
        return $this->surveyWorkflowService->prepareSurveyForDisplay($survey, $user);
    }
}

