<?php

namespace App\Application\Survey\Actions;

use App\Application\Survey\Services\SurveyWorkflowService;
use App\Models\Survey;

class BuildSurveyEditPayloadAction
{
    public function __construct(
        private SurveyWorkflowService $surveyWorkflowService
    ) {}

    public function __invoke(Survey $survey): array
    {
        return $this->surveyWorkflowService->buildAdminEditPayload($survey);
    }
}

