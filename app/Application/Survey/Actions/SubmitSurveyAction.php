<?php

namespace App\Application\Survey\Actions;

use App\Application\Survey\DTO\SurveyAnswerSubmissionData;
use App\Application\Survey\Services\SurveyWorkflowService;
use App\Http\Requests\SurveyAnswerRequest;
use App\Models\Survey;
use Illuminate\Contracts\Auth\Authenticatable;

class SubmitSurveyAction
{
    public function __construct(
        private SurveyWorkflowService $surveyWorkflowService
    ) {}

    public function __invoke(SurveyAnswerRequest $request, Survey $survey, int $dealerId, ?Authenticatable $user): void
    {
        $submission = SurveyAnswerSubmissionData::fromRequest($request, $dealerId);
        $this->surveyWorkflowService->submitSurvey($survey, $submission, $user);
    }
}

