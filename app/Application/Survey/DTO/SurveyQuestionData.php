<?php

namespace App\Application\Survey\DTO;

use App\Application\Survey\Enums\SurveyQuestionType;

class SurveyQuestionData
{
    /**
     * @param SurveyOptionData[] $options
     */
    public function __construct(
        public string $question,
        public SurveyQuestionType $type,
        public int $sortOrder,
        public array $options = []
    ) {}

    public static function fromArray(array $payload, int $sortOrder): self
    {
        $type = SurveyQuestionType::tryFrom((string) ($payload['type'] ?? ''));
        $options = [];

        foreach ((array) ($payload['options'] ?? []) as $optionIndex => $option) {
            $options[] = SurveyOptionData::fromArray((array) $option, $optionIndex + 1);
        }

        return new self(
            question: trim((string) ($payload['question'] ?? '')),
            type: $type ?? SurveyQuestionType::TEXT,
            sortOrder: $sortOrder,
            options: $options
        );
    }
}

