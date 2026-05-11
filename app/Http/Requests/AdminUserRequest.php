<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $userId = $user?->id;

        $rules = [
            'name' => 'required|max:255',
            'surname' => 'required|max:255',
            'email' => [
                'required',
                'max:255',
                'email',
                Rule::unique('admins', 'email')->ignore($userId),
            ],
            'username' => [
                'required',
                'max:255',
                Rule::unique('admins', 'username')->ignore($userId),
            ],
            'permissions' => 'nullable|array',
            'status' => 'nullable|boolean',
            'block_entry' => 'nullable|boolean',
        ];

        if (!$userId) {
            $rules['password'] = 'required|max:255';
        } else {
            $rules['password'] = 'nullable|max:255';
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->has('status') ? 1 : 0,
            'block_entry' => $this->has('block_entry') ? 1 : 0,
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Lutfen adini giriniz.',
            'surname.required' => 'Lutfen soyadini giriniz.',
            'name.max' => 'Lutfen adini 255 karakteri asmayacak sekilde giriniz.',
            'surname.max' => 'Lutfen soyadini 255 karakteri asmayacak sekilde giriniz.',
            'email.required' => 'Lutfen e-posta adresini giriniz.',
            'email.max' => 'Lutfen e-posta adresini 255 karakteri asmayacak sekilde giriniz.',
            'email.email' => 'Lutfen gecerli bir e-posta adresini giriniz.',
            'email.unique' => 'Girdiginiz e-posta adresi baska bir kullanici tarafindan kullanilmaktadir.',
            'username.required' => 'Lutfen kullanici adini giriniz.',
            'username.max' => 'Lutfen kullanici adini 255 karakteri asmayacak sekilde giriniz.',
            'username.unique' => 'Girdiginiz kullanici adi baska bir kullanici tarafindan kullanilmaktadir.',
            'password.required' => 'Lutfen sifre giriniz.',
            'password.max' => 'Lutfen sifreyi 255 karakteri asmayacak sekilde giriniz.',
        ];
    }
}
