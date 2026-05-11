<?php

namespace App\Application\Survey\DTO;

use App\Http\Requests\SurveyRequest;

class SurveyUpsertData
{
    /**
     * @param SurveyQuestionData[] $questions
     */
    public function __construct(
        public string $title,
        public ?string $description,
        public bool $useDates,
        public ?string $startAt,
        public ?string $endAt,
        public bool $isActive,
        public ?int $createdBy,
        public array $questions
    ) {}

    public static function fromRequest(SurveyRequest $request, ?int $createdBy = null): self
    {
        $questions = [];
        foreach ((array) $request->input('questions', []) as $index => $question) {
            $questions[] = SurveyQuestionData::fromArray((array) $question, $index + 1);
        }

        $useDates = $request->boolean('use_dates');

        return new self(
            title: (string) $request->input('title'),
            description: $request->input('description'),
            useDates: $useDates,
            startAt: $useDates ? $request->input('start_at') : null,
            endAt: $useDates ? $request->input('end_at') : null,
            isActive: $request->boolean('is_active'),
            createdBy: $createdBy,
            questions: $questions
        );
    }
}

