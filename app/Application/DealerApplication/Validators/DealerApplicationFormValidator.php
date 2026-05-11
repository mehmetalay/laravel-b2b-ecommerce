<?php

namespace App\Application\DealerApplication\Validators;

use App\Application\DealerApplication\Services\DealerApplicationDocumentService;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;

class DealerApplicationFormValidator
{
    public function make(array $payload): ValidatorContract
    {
        return Validator::make($payload, $this->rules(), $this->messages());
    }

    private function rules(): array
    {
        return [
            'company_name' => 'required|max:255',
            'tax_office' => 'nullable|max:255',
            'tax_number' => 'nullable|max:255',
            'city' => 'required|max:255',
            'district' => 'required|max:255',
            'address' => 'required',
            'authorized_name_surname' => 'required|max:255',
            'identity_number' => 'nullable|max:255',
            'phone_number' => 'nullable|not_regex:/_/',
            'mobile_phone_number' => 'required|not_regex:/_/',
            'fax_number' => 'nullable|not_regex:/_/',
            'email_address' => 'required|max:255|email',
            'web_address' => 'nullable|max:255',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:' . implode(',', DealerApplicationDocumentService::ALLOWED_EXTENSIONS),
        ];
    }

    private function messages(): array
    {
        return [
            'company_name.required' => 'Lütfen şirket adı/firma ünvanını giriniz.',
            'company_name.max' => 'Lütfen şirket adı/firma ünvanını 255 karakteri aşmayacak şekilde giriniz.',
            'tax_office.max' => 'Lütfen vergi dairesini 255 karakteri aşmayacak şekilde giriniz.',
            'tax_number.max' => 'Lütfen vergi numarasını 255 karakteri aşmayacak şekilde giriniz.',
            'city.required' => 'Lütfen şehir adını giriniz.',
            'city.max' => 'Lütfen şehir adını 255 karakteri aşmayacak şekilde giriniz.',
            'district.required' => 'Lütfen ilçe adını giriniz.',
            'district.max' => 'Lütfen ilçe adını 255 karakteri aşmayacak şekilde giriniz.',
            'address.required' => 'Lütfen adres giriniz.',
            'authorized_name_surname.required' => 'Lütfen yetkili kişinin adını soyadını giriniz.',
            'authorized_name_surname.max' => 'Lütfen yetkili kişinin adını soyadını 255 karakteri aşmayacak şekilde giriniz.',
            'identity_number.max' => 'Lütfen kimlik numarasını 255 karakteri aşmayacak şekilde giriniz.',
            'phone_number.not_regex' => 'Lütfen telefon numarasını giriniz.',
            'mobile_phone_number.required' => 'Lütfen cep telefon numarasını giriniz.',
            'mobile_phone_number.not_regex' => 'Lütfen cep telefon numarasını giriniz.',
            'fax_number.not_regex' => 'Lütfen faks numarasını giriniz.',
            'email_address.required' => 'Lütfen e-posta adresini giriniz.',
            'email_address.max' => 'Lütfen e-posta adresini 255 karakteri aşmayacak şekilde giriniz.',
            'email_address.email' => 'Lütfen geçerli bir e-posta adresini giriniz.',
            'web_address.max' => 'Lütfen web adresini 255 karakteri aşmayacak şekilde giriniz.',
            'documents.array' => 'Lütfen belgeleri uygun formatta yükleyiniz.',
            'documents.*.file' => 'Lütfen geçerli bir belge yükleyiniz.',
            'documents.*.mimes' => 'Yalnızca pdf, jpg, jpeg, png, doc, docx, xls, xlsx uzantılı belgeler yükleyebilirsiniz.',
        ];
    }
}
