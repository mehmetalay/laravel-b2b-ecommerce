<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SurveyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'use_dates' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question' => ['required', 'string', 'max:500'],
            'questions.*.type' => ['required', 'in:single,multiple,text'],
        ];

        if ($this->boolean('use_dates')) {
            $rules['start_at'] = ['required', 'date'];
            $rules['end_at'] = ['required', 'date', 'after_or_equal:start_at'];
        }

        foreach ($this->input('questions', []) as $index => $question) {
            if (isset($question['type']) && in_array($question['type'], ['single', 'multiple'], true)) {
                $rules["questions.{$index}.options"] = ['required', 'array', 'min:1'];
                $rules["questions.{$index}.options.*.option_text"] = ['required', 'string', 'max:255'];
            }
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'use_dates' => $this->has('use_dates') ? 1 : 0,
            'is_active' => $this->has('is_active') ? 1 : 0,
        ]);
    }

    public function messages()
    {
        return [
            'title.required' => 'Anket başlığı zorunludur.',
            'title.max' => 'Anket başlığı en fazla :max karakter olabilir.',
            'description.string' => 'Açıklama metin formatında olmalıdır.',
            'start_at.required' => 'Başlangıç tarihi zorunludur.',
            'end_at.required' => 'Bitiş tarihi zorunludur.',
            'end_at.after_or_equal' => 'Bitiş tarihi başlangıç tarihinden önce olamaz.',
            'questions.required' => 'En az bir soru eklemelisiniz.',
            'questions.array' => 'Sorular geçersiz formatta gönderildi.',
            'questions.*.question.required' => 'Her sorunun bir metni olmalıdır.',
            'questions.*.type.required' => 'Her sorunun bir tipi olmalıdır.',
            'questions.*.type.in' => 'Soru tipi geçersiz.',
            'questions.*.options.required' => 'Bu soru için en az bir seçenek eklenmelidir.',
            'questions.*.options.array' => 'Seçenekler geçersiz formatta gönderildi.',
            'questions.*.options.*.option_text.required' => 'Her seçeneğin bir metni olmalıdır.',
            'questions.*.options.*.option_text.max' => 'Seçenek metni en fazla :max karakter olabilir.',
        ];
    }

    public function attributes()
    {
        return [
            'title' => 'Anket Başlığı',
            'description' => 'Açıklama',
            'start_at' => 'Başlangıç Tarihi',
            'end_at' => 'Bitiş Tarihi',
            'questions.*.question' => 'Soru Metni',
            'questions.*.type' => 'Soru Tipi',
            'questions.*.options.*.option_text' => 'Seçenek Metni',
        ];
    }
}
