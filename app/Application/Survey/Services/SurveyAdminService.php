<?php

namespace App\Application\Survey\Services;

use App\Application\Survey\DTO\SurveyUpsertData;
use App\Application\Survey\Repositories\SurveyAnswerRepository;
use App\Application\Survey\Repositories\SurveyRepository;
use App\Models\Survey;
use App\Models\SurveyOption;
use Illuminate\Support\Facades\DB;

class SurveyAdminService
{
    public function __construct(
        private SurveyRepository $surveyRepository,
        private SurveyAnswerRepository $surveyAnswerRepository,
        private SurveyAuditService $surveyAuditService
    ) {}

    public function create(SurveyUpsertData $data): Survey
    {
        return DB::transaction(function () use ($data) {
            $survey = $this->surveyRepository->create([
                'title' => $data->title,
                'description' => $data->description,
                'use_dates' => $data->useDates,
                'start_at' => $data->startAt,
                'end_at' => $data->endAt,
                'is_active' => $data->isActive,
                'created_by' => $data->createdBy,
            ]);

            $this->persistQuestions($survey, $data);
            $this->surveyRepository->clearCache($survey);

            $this->surveyAuditService->info('Survey created', [
                'survey_id' => (int) $survey->id,
                'created_by' => $data->createdBy,
            ]);

            return $survey;
        });
    }

    public function update(Survey $survey, SurveyUpsertData $data): Survey
    {
        return DB::transaction(function () use ($survey, $data) {
            $updatedSurvey = $this->surveyRepository->update($survey, [
                'title' => $data->title,
                'description' => $data->description,
                'use_dates' => $data->useDates,
                'start_at' => $data->startAt,
                'end_at' => $data->endAt,
                'is_active' => $data->isActive,
            ]);

            $canEditQuestions = !$this->surveyAnswerRepository->hasAnyParticipant($survey);
            if ($canEditQuestions) {
                $this->replaceQuestions($updatedSurvey, $data);
            }

            $this->surveyRepository->clearCache($updatedSurvey);

            $this->surveyAuditService->info('Survey updated', [
                'survey_id' => (int) $updatedSurvey->id,
                'questions_replaced' => $canEditQuestions,
            ]);

            return $updatedSurvey;
        });
    }

    public function delete(Survey $survey): bool
    {
        $isDeleted = $this->surveyRepository->delete($survey);

        $this->surveyAuditService->warning('Survey deleted', [
            'survey_id' => (int) $survey->id,
            'deleted' => $isDeleted,
        ]);

        return $isDeleted;
    }

    private function replaceQuestions(Survey $survey, SurveyUpsertData $data): void
    {
        $questionIds = $survey->questions()->pluck('id')->all();

        if (!empty($questionIds)) {
            SurveyOption::query()
                ->whereIn('survey_question_id', $questionIds)
                ->delete();
        }

        $survey->questions()->delete();
        $this->persistQuestions($survey, $data);
    }

    private function persistQuestions(Survey $survey, SurveyUpsertData $data): void
    {
        foreach ($data->questions as $questionData) {
            $question = $survey->questions()->create([
                'question' => $questionData->question,
                'type' => $questionData->type->value,
                'sort_order' => $questionData->sortOrder,
            ]);

            foreach ($questionData->options as $optionData) {
                $question->options()->create([
                    'option_text' => $optionData->optionText,
                    'sort_order' => $optionData->sortOrder,
                ]);
            }
        }
    }
}

