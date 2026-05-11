<?php

namespace App\Application\Contract\Validators;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Validator as ValidatorFactory;

class ContractFormValidator
{
    public function validate(array $input): Validator
    {
        return ValidatorFactory::make(
            $input,
            [
                'customer_invoice_title' => 'required',
                'customer_invoice_address' => 'required',
                'phone' => 'required',
                'tax_number' => 'required',
                'company_official' => 'required',
                'mobile_phone' => 'required|not_regex:/_/',
                'email_address' => 'required|email',
                'bank_accounts' => 'array|max:5',
                'bank_accounts.*.bank_name' => 'nullable|string|max:255',
                'bank_accounts.*.branch' => 'nullable|string|max:255',
                'bank_accounts.*.account_no' => 'nullable|string|max:255',
                'bank_accounts.*.account_holder' => 'nullable|string|max:255',
                'emails' => 'array|max:5',
                'emails.*.email' => 'nullable|email|max:255',
                'gsms' => 'array|max:5',
                'gsms.*.gsm' => 'nullable|not_regex:/_/',
                'ship_locations' => 'array|max:5',
                'ship_locations.*.name' => 'nullable|string|max:255',
                'ship_locations.*.address' => 'nullable|string|max:255',
                'ship_locations.*.city' => 'nullable|string|max:255',
                'ship_locations.*.district' => 'nullable|string|max:255',
                'ship_locations.*.phone' => 'nullable|not_regex:/_/',
                'ship_locations.*.fax' => 'nullable|not_regex:/_/',
                'ship_locations.*.authorized_name' => 'nullable|string|max:255',
            ],
            [
                'customer_invoice_title.required' => trans('translations.contract_controller.lutfen_musteri̇_fatura_unvani_giriniz'),
                'customer_invoice_address.required' => trans('translations.contract_controller.lutfen_musteri̇_fatura_adresi_giriniz'),
                'phone.required' => trans('translations.contract_controller.lutfen_telefon_no_giriniz'),
                'tax_number.required' => trans('translations.contract_controller.lutfen_vergi̇_no_giriniz'),
                'company_official.required' => trans('translations.contract_controller.lutfen_firma_yetkilisi_giriniz'),
                'mobile_phone.required' => trans('translations.contract_controller.lutfen_gsm_no_giriniz'),
                'mobile_phone.not_regex' => trans('translations.contract_controller.lutfen_gsm_no_giriniz'),
                'email_address.required' => trans('translations.contract_controller.lutfen_mai̇l_adresi̇ni_giriniz'),
                'email_address.email' => trans('translations.contract_controller.lutfen_gecerli_mai̇l_adresi̇ni_giriniz'),
            ]
        );
    }
}

