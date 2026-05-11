<?php

namespace App\Application\Survey\DTO;

class SurveyOptionData
{
    public function __construct(
        public string $optionText,
        public int $sortOrder
    ) {}

    public static function fromArray(array $payload, int $sortOrder): self
    {
        return new self(
            optionText: trim((string) ($payload['option_text'] ?? '')),
            sortOrder: $sortOrder
        );
    }
}

