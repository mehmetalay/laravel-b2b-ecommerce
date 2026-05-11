<?php

namespace App\Application\Survey\Validators;

use App\Application\Survey\Enums\SurveyQuestionType;
use App\Models\Survey;
use Illuminate\Validation\ValidationException;

class SurveyAnswerValidator
{
    public function validateAndNormalize(Survey $survey, array $answers): array
    {
        $survey->loadMissing([
            'questions' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
            'questions.options' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
        ]);

        $questions = $survey->questions->values();
        $errors = [];
        $normalized = [];

        if (count($answers) !== $questions->count()) {
            throw ValidationException::withMessages([
                'answers' => ['Lütfen anketteki tüm soruları yanıtlayınız.'],
            ]);
        }

        foreach ($questions as $index => $question) {
            if (!array_key_exists($index, $answers)) {
                $errors["answers.{$index}"][] = 'Bu soruyu boş bırakamazsınız.';
                continue;
            }

            $type = SurveyQuestionType::tryFrom((string) $question->type);
            $rawAnswer = $answers[$index];
            $optionIds = $question->options->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

            if ($type === SurveyQuestionType::TEXT) {
                $value = trim((string) $rawAnswer);
                if ($value === '') {
                    $errors["answers.{$index}"][] = 'Bu soruyu boş bırakamazsınız.';
                    continue;
                }

                $normalized[$index] = $value;
                continue;
            }

            if ($type === SurveyQuestionType::SINGLE) {
                if (is_array($rawAnswer) || (string) $rawAnswer === '') {
                    $errors["answers.{$index}"][] = 'Lütfen bir seçenek seçiniz.';
                    continue;
                }

                $selected = (int) $rawAnswer;
                if (!in_array($selected, $optionIds, true)) {
                    $errors["answers.{$index}"][] = 'Geçersiz seçenek seçimi.';
                    continue;
                }

                $normalized[$index] = $selected;
                continue;
            }

            if (!is_array($rawAnswer) || empty($rawAnswer)) {
                $errors["answers.{$index}"][] = 'Lütfen en az bir seçenek seçiniz.';
                continue;
            }

            $selected = collect($rawAnswer)
                ->map(fn ($value) => (int) $value)
                ->filter(fn ($value) => $value > 0)
                ->unique()
                ->values()
                ->all();

            if (empty($selected)) {
                $errors["answers.{$index}"][] = 'Lütfen en az bir seçenek seçiniz.';
                continue;
            }

            $invalidSelections = array_diff($selected, $optionIds);
            if (!empty($invalidSelections)) {
                $errors["answers.{$index}"][] = 'Geçersiz seçenek seçimi.';
                continue;
            }

            $normalized[$index] = $selected;
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        ksort($normalized);

        return $normalized;
    }
}
