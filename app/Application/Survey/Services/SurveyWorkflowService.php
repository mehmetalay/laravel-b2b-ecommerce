<?php

namespace App\Application\Survey\Services;

use App\Application\Survey\DTO\SurveyAnswerSubmissionData;
use App\Application\Survey\DTO\SurveyUpsertData;
use App\Application\Survey\Repositories\SurveyAnswerRepository;
use App\Application\Survey\Repositories\SurveyRepository;
use App\Models\Survey;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Throwable;

class SurveyWorkflowService
{
    public function __construct(
        private SurveyRepository $surveyRepository,
        private SurveyAnswerRepository $surveyAnswerRepository,
        private SurveyAccessService $surveyAccessService,
        private SurveyAdminService $surveyAdminService,
        private SurveyResponseService $surveyResponseService,
        private SurveyResultService $surveyResultService,
        private SurveyAuditService $surveyAuditService
    ) {}

    public function getAll(): Collection
    {
        return $this->surveyRepository->all();
    }

    public function getActiveSurvey(): ?Survey
    {
        return $this->surveyRepository->getActiveSurvey();
    }

    public function create(SurveyUpsertData $data): Survey
    {
        return $this->surveyAdminService->create($data);
    }

    public function update(Survey $survey, SurveyUpsertData $data): Survey
    {
        return $this->surveyAdminService->update($survey, $data);
    }

    public function delete(Survey $survey): bool
    {
        return $this->surveyAdminService->delete($survey);
    }

    public function clearCache(Survey $survey): void
    {
        $this->surveyRepository->clearCache($survey);
    }

    public function buildAdminEditPayload(Survey $survey): array
    {
        $survey = $this->surveyRepository->findByIdWithQuestions((int) $survey->id) ?? $survey;

        return [
            'id' => $survey->id,
            'title' => $survey->title,
            'description' => $survey->description,
            'use_dates' => (bool) $survey->use_dates,
            'start_at' => $survey->start_at ? from_format($survey->start_at, 'Y-m-d') : null,
            'end_at' => $survey->end_at ? from_format($survey->end_at, 'Y-m-d') : null,
            'is_active' => (bool) $survey->is_active,
            'questions' => $survey->questions->map(function ($question) {
                return [
                    'question' => $question->question,
                    'type' => $question->type,
                    'sort_order' => $question->sort_order,
                    'options' => $question->options->map(fn ($option) => [
                        'option_text' => $option->option_text,
                        'sort_order' => $option->sort_order,
                    ]),
                ];
            }),
        ];
    }

    public function buildResultsPayload(Survey $survey): array
    {
        return $this->surveyResultService->build($survey);
    }

    public function prepareSurveyForDisplay(Survey $survey, ?Authenticatable $user): Survey
    {
        $this->surveyAccessService->ensureDealerUser($user);
        $survey = $this->surveyRepository->findByIdWithQuestions((int) $survey->id) ?? $survey;
        $this->surveyAccessService->ensureSurveyIsActive($survey);
        $this->surveyAccessService->ensureNotParticipated((int) $survey->id, (int) $user->current_account_id);

        return $survey;
    }

    public function submitSurvey(Survey $survey, SurveyAnswerSubmissionData $submission, ?Authenticatable $user): void
    {
        $this->surveyAccessService->ensureDealerUser($user);
        $survey = $this->surveyRepository->findByIdWithQuestions((int) $survey->id) ?? $survey;
        $this->surveyAccessService->ensureSurveyIsActive($survey);
        $this->surveyAccessService->ensureNotParticipated((int) $survey->id, $submission->dealerId);
        $this->surveyResponseService->submit($survey, $submission);
    }

    public function hasDealerParticipated(int $surveyId, int $dealerId): bool
    {
        return $this->surveyAnswerRepository->hasDealerParticipated($surveyId, $dealerId);
    }

    public function reportException(string $stage, Throwable $e): void
    {
        $this->surveyAuditService->error("Survey {$stage} exception", [
            'message' => $e->getMessage(),
        ]);
    }
}

