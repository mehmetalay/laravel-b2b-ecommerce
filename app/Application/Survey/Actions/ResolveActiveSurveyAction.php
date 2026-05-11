<?php

namespace App\Application\Survey\Actions;

use App\Application\Survey\Services\SurveyWorkflowService;
use App\Models\Survey;

class ResolveActiveSurveyAction
{
    public function __construct(
        private SurveyWorkflowService $surveyWorkflowService
    ) {}

    public function __invoke(): ?Survey
    {
        return $this->surveyWorkflowService->getActiveSurvey();
    }
}

