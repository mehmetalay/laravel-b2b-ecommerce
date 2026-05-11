<?php

namespace App\Application\Survey\Services;

use App\Application\Survey\DTO\SurveyAnswerSubmissionData;
use App\Application\Survey\Enums\SurveyQuestionType;
use App\Application\Survey\Repositories\SurveyAnswerRepository;
use App\Application\Survey\Validators\SurveyAnswerValidator;
use App\Models\Survey;

class SurveyResponseService
{
    public function __construct(
        private SurveyAnswerValidator $surveyAnswerValidator,
        private SurveyAnswerRepository $surveyAnswerRepository,
        private SurveyAuditService $surveyAuditService
    ) {}

    public function submit(Survey $survey, SurveyAnswerSubmissionData $submission): void
    {
        $survey->loadMissing([
            'questions' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
            'questions.options' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
        ]);

        $normalizedAnswers = $this->surveyAnswerValidator->validateAndNormalize($survey, $submission->answers);
        $rows = [];
        $now = now();

        foreach ($survey->questions->values() as $index => $question) {
            $answer = $normalizedAnswers[$index];
            $questionType = SurveyQuestionType::tryFrom((string) $question->type);

            if ($questionType === SurveyQuestionType::TEXT) {
                $rows[] = [
                    'survey_id' => (int) $survey->id,
                    'survey_question_id' => (int) $question->id,
                    'survey_option_id' => null,
                    'dealer_id' => $submission->dealerId,
                    'answer_text' => (string) $answer,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                continue;
            }

            if ($questionType === SurveyQuestionType::SINGLE) {
                $rows[] = [
                    'survey_id' => (int) $survey->id,
                    'survey_question_id' => (int) $question->id,
                    'survey_option_id' => (int) $answer,
                    'dealer_id' => $submission->dealerId,
                    'answer_text' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                continue;
            }

            foreach ((array) $answer as $optionId) {
                $rows[] = [
                    'survey_id' => (int) $survey->id,
                    'survey_question_id' => (int) $question->id,
                    'survey_option_id' => (int) $optionId,
                    'dealer_id' => $submission->dealerId,
                    'answer_text' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->surveyAnswerRepository->createMany($rows);

        $this->surveyAuditService->info('Survey response submitted', [
            'survey_id' => (int) $survey->id,
            'dealer_id' => $submission->dealerId,
            'answers_count' => count($rows),
        ]);
    }
}

