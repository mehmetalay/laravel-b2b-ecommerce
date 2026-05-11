<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'id' => 'nullable|exists:customer_addresses,id',

            'title' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',

            'tax_office' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:50',
            'phone' => 'required|not_regex:/_/',

            'city_id' => 'required|exists:cities,id',
            'district_id' => 'required|exists:districts,id',
            'neighborhood_id' => 'required|exists:neighborhoods,id',

            'address' => 'required|string',
            'is_default' => 'nullable',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'title' => str_upper($this->title),
            'company_name' => str_upper($this->company_name),
            'tax_office' => str_upper($this->tax_office),
            'phone' => sanitize_phone_number($this->phone),
            'address' => str_upper($this->address),
            'slug' => str_slug($this->name),
            'is_default' => $this->has('is_default') ? 1 : 0,
        ]);
    }

    public function messages(): array
    {
        return [
            'id.exists' => 'Seçilen adres geçerli değil.',

            'title.required' => 'Adres başlığı zorunludur.',
            'title.string' => 'Adres başlığı metin olmalıdır.',
            'title.max' => 'Adres başlığı en fazla :max karakter olabilir.',

            'company_name.required' => 'Firma adı zorunludur.',
            'company_name.string' => 'Firma adı metin olmalıdır.',
            'company_name.max' => 'Firma adı en fazla :max karakter olabilir.',

            'tax_office.string' => 'Vergi dairesi metin olmalıdır.',
            'tax_office.max' => 'Vergi dairesi en fazla :max karakter olabilir.',

            'tax_number.string' => 'Vergi numarası metin olmalıdır.',
            'tax_number.max' => 'Vergi numarası en fazla :max karakter olabilir.',

            'phone.required' => 'Telefon numarası zorunludur.',
            'phone.not_regex' => 'Telefon numarası zorunludur.',

            'city_id.required' => 'Şehir seçimi zorunludur.',
            'city_id.exists' => 'Şehir seçimi zorunludur.',

            'district_id.required' => 'İlçe seçimi zorunludur.',
            'district_id.exists' => 'İlçe seçimi zorunludur.',

            'neighborhood_id.required' => 'Mahalle seçimi zorunludur.',
            'neighborhood_id.exists' => 'Mahalle seçimi zorunludur.',

            'address.required' => 'Adres alanı zorunludur.',
            'address.string' => 'Adres metin olmalıdır.',

            'is_default.boolean' => 'Varsayılan adres bilgisi geçersiz.',
        ];
    }
}
