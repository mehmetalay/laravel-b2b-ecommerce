<?php

namespace App\Application\Survey\DTO;

use App\Http\Requests\SurveyAnswerRequest;

class SurveyAnswerSubmissionData
{
    public function __construct(
        public int $dealerId,
        public array $answers
    ) {}

    public static function fromRequest(SurveyAnswerRequest $request, int $dealerId): self
    {
        return new self(
            dealerId: $dealerId,
            answers: array_values((array) $request->input('answers', []))
        );
    }
}

