<?php

namespace App\Application\Survey\Actions;

use App\Application\Survey\Services\SurveyWorkflowService;
use App\Models\Survey;

class DeleteAdminSurveyAction
{
    public function __construct(
        private SurveyWorkflowService $surveyWorkflowService
    ) {}

    public function __invoke(Survey $survey): bool
    {
        return $this->surveyWorkflowService->delete($survey);
    }
}

