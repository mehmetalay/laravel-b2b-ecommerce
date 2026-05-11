<?php

namespace App\Application\Survey\Actions;

use App\Application\Survey\Services\SurveyWorkflowService;
use Illuminate\Support\Collection;

class ListAdminSurveysAction
{
    public function __construct(
        private SurveyWorkflowService $surveyWorkflowService
    ) {}

    public function __invoke(): Collection
    {
        return $this->surveyWorkflowService->getAll();
    }
}

