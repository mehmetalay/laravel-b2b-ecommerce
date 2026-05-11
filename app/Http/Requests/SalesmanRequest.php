<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class SalesmanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('salesman') ? $this->route('salesman')->id : null;

        $rules = [
            'current_account_id' => 'nullable',
            'name' => 'required|max:255',
            'email' => 'required|max:255|email|unique:users,email,' . $userId,
            'phone' => 'required',
            'code' => 'required|max:100|unique:users,code,' . $userId,
            'access_type' => 'required',
            'password' => 'max:100',
            'hide_category_ids' => 'nullable',
            'status' => 'nullable|boolean',
            'block_entry' => 'nullable|boolean',
            'report_access' => 'nullable|boolean',
            'show_all_installments' => 'nullable|boolean',
            'can_edit_price' => 'nullable|boolean',
            'can_edit_discount' => 'nullable|boolean',
            'hide_all_stock_quantities' => 'nullable|boolean',
        ];

        if (!$userId) {
            $rules['password'] = 'required|max:100';
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'current_account_id' => $this->route('id') ? null : User::where('role', 'salesman')->max('current_account_id') + 1,
            'name' => str_upper($this->name),
            'phone' => sanitize_phone_number($this->phone),
            'hide_category_ids' => implode_json_column($this->hide_category_ids, 'id'),
            'status' => $this->has('status') ? 1 : 0,
            'block_entry' => $this->has('block_entry') ? 1 : 0,
            'report_access' => $this->has('report_access') ? 1 : 0,
            'show_all_installments' => $this->has('show_all_installments') ? 1 : 0,
            'can_edit_price' => $this->has('can_edit_price') ? 1 : 0,
            'can_edit_discount' => $this->has('can_edit_discount') ? 1 : 0,
            'hide_all_stock_quantities' => $this->has('hide_all_stock_quantities') ? 1 : 0,
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Lütfen plasiyer adını giriniz.',
            'name.max' => 'Lütfen adını 255 karakteri aşmayacak şekilde giriniz.',

            'email.required' => 'Lütfen e-posta adresini giriniz.',
            'email.max' => 'Lütfen e-posta adresini 255 karakteri aşmayacak şekilde giriniz.',
            'email.email' => 'Lütfen geçerli bir e-posta adresini giriniz.',
            'email.unique' => 'Girdiğiniz e-posta adresi başka bir kullanıcı tarafından kullanılmaktadır.',

            'phone.required' => 'Lütfen telefon numarasını giriniz.',

            'code.required' => 'Lütfen kodu giriniz.',
            'code.max' => 'Lütfen kodu 100 karakteri aşmayacak şekilde giriniz.',
            'code.unique' => 'Girdiğiniz kod başka bir kullanıcı tarafından kullanılmaktadır.',

            'access_type.required' => 'Lütfen erişim tipi seçiniz.',

            'password.required' => 'Lütfen şifre giriniz.',
            'password.max' => 'Lütfen şifreyi 100 karakteri aşmayacak şekilde giriniz.',
        ];
    }
}
