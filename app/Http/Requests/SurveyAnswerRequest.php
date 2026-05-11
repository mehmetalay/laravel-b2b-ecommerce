<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SurveyAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('web')->check() && auth('web')->user()->role === 'dealer';
    }

    public function rules(): array
    {
        return [
            'answers' => 'required|array',
            'answers.*' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'answers.required' => 'Lütfen anketteki soruları yanıtlayınız.',
            'answers.array' => 'Geçersiz veri formatı.',
            'answers.*.required' => 'Bu soruyu boş bırakamazsınız.',
        ];
    }
}
