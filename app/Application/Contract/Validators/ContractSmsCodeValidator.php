<?php

namespace App\Application\Contract\Validators;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Validator as ValidatorFactory;

class ContractSmsCodeValidator
{
    public function validate(array $input): Validator
    {
        return ValidatorFactory::make(
            $input,
            [
                'sms_code' => 'required',
            ],
            [
                'sms_code.required' => trans('translations.contract_controller.onay_kodu_giriniz'),
            ]
        );
    }
}

