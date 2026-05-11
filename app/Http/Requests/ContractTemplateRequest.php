<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContractTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|max:255',
            'dealer_type' => 'required|in:all,dealer,subdealer',
            'content' => 'required',
            'is_active' => 'nullable|boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->has('is_active') ? 1 : 0,
        ]);
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Baslik alani zorunludur.',
            'title.max' => 'Baslik alani en fazla 255 karakter olabilir.',
            'dealer_type.required' => 'Bayi turu alani zorunludur.',
            'dealer_type.in' => 'Gecersiz bayi turu secimi.',
            'content.required' => 'Icerik alani zorunludur.',
        ];
    }
}
