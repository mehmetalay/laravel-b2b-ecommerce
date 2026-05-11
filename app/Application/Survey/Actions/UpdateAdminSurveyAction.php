<?php

namespace App\Application\Survey\Actions;

use App\Application\Survey\DTO\SurveyUpsertData;
use App\Application\Survey\Services\SurveyWorkflowService;
use App\Http\Requests\SurveyRequest;
use App\Models\Survey;

class UpdateAdminSurveyAction
{
    public function __construct(
        private SurveyWorkflowService $surveyWorkflowService
    ) {}

    public function __invoke(SurveyRequest $request, Survey $survey): Survey
    {
        return $this->surveyWorkflowService->update(
            $survey,
            SurveyUpsertData::fromRequest($request)
        );
    }
}

