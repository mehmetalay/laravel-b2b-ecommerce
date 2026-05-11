<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CurrencyRequest extends FormRequest
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
            'symbol' => 'required',
            'manual_override' => 'nullable|boolean',
            'manual_buy' => 'required_if:manual_override,1',
            'manual_sell' => 'required_if:manual_override,1',
            'status' => 'nullable|boolean',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'manual_override' => $this->has('manual_override') ? 1 : 0,
            'status' => $this->has('status') ? 1 : 0,
        ]);
    }

    public function messages(): array
    {
        return [
            'symbol.required' => 'Lütfen bir sembol giriniz.',
            'manual_buy.required_if' => 'Manuel giriş seçiliyse, lütfen alış fiyatını giriniz.',
            'manual_sell.required_if' => 'Manuel giriş seçiliyse, lütfen satış fiyatını giriniz.',
        ];
    }
}
