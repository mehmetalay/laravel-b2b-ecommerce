<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HomepageBlockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'slug' => 'nullable|string|max:255',
            'title_tr' => 'required|string|max:255',
            'subtitle_tr' => 'nullable',
            'title_en' => 'required|string|max:255',
            'subtitle_en' => 'nullable',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_active' => $this->has('is_active') ? 1 : 0,
        ]);
    }

    public function messages(): array
    {
        return [
            'title_tr.required' => 'Lütfen başlık (TR) giriniz.',
            'title_en.required' => 'Lütfen başlık (EN) giriniz.',
        ];
    }
}
