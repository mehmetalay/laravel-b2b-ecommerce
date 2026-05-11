<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'user_id' => 'required',
            'email' => 'nullable|max:255|email',
            'phone' => 'nullable|max:100|not_regex:/_/',
            'amount' => 'required',
            'transaction_type' => 'nullable|in:1,2,3',
            'status' => 'nullable|boolean',
            'amount_locked' => 'nullable|boolean',
            'manual_lock_bank_installment' => 'nullable|boolean',
        ];

        if ((int) $this->input('transaction_type') === 3) {
            $rules['manual_bank_integration_id'] = 'required';
            $rules['manual_installment'] = 'required';
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => $this->filled('phone')
                ? str_replace(['(', ')', ' ', '-'], '', (string) $this->input('phone'))
                : null,
            'amount' => str_replace(',', '', (string) $this->input('amount')),
            'status' => $this->has('status') ? 1 : 0,
            'amount_locked' => $this->has('amount_locked') ? 1 : 0,
            'manual_lock_bank_installment' => $this->has('manual_lock_bank_installment') ? 1 : 0,
        ]);
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Lutfen musteri seciniz.',
            'email.max' => 'Lutfen e-posta adresini 255 karakteri asmayacak sekilde giriniz.',
            'email.email' => 'Lutfen gecerli bir e-posta adresini giriniz.',
            'phone.max' => 'Lutfen telefon numarasini 100 karakteri asmayacak sekilde giriniz.',
            'phone.not_regex' => 'Lutfen telefon numarasini giriniz.',
            'amount.required' => 'Lutfen odeme tutari giriniz.',
            'manual_bank_integration_id.required' => 'Lutfen banka seciniz.',
            'manual_installment.required' => 'Lutfen taksit seciniz.',
        ];
    }
}
