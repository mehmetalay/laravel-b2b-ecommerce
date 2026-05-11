<?php

namespace App\Application\Survey\Services;

use App\Application\Survey\Repositories\SurveyAnswerRepository;
use App\Models\Survey;

class SurveyResultService
{
    public function __construct(
        private SurveyAnswerRepository $surveyAnswerRepository
    ) {}

    public function build(Survey $survey): array
    {
        $survey->load([
            'questions' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
            'questions.options' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
            'questions.answers',
        ]);

        $totalParticipants = $this->surveyAnswerRepository->countDistinctParticipants((int) $survey->id);

        $questionsStats = $survey->questions->map(function ($question) use ($totalParticipants) {
            $stats = [];

            if ((string) $question->type === 'text') {
                $stats['text_answers'] = $question->answers->pluck('answer_text')->filter()->values()->all();
            } else {
                $stats['options'] = $question->options->map(function ($option) use ($question, $totalParticipants) {
                    $count = $question->answers->where('survey_option_id', $option->id)->count();
                    $percent = $totalParticipants > 0
                        ? round(($count / $totalParticipants) * 100, 2)
                        : 0;

                    return [
                        'option' => $option->option_text,
                        'count' => $count,
                        'percent' => $percent,
                    ];
                });
            }

            return [
                'question' => $question->question,
                'type' => $question->type,
                'stats' => $stats,
            ];
        });

        return [
            'survey' => $survey,
            'questionsStats' => $questionsStats,
            'totalParticipants' => $totalParticipants,
        ];
    }
}

