<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrandRequest extends FormRequest
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
        $brand = $this->route('brand');
        $id = $brand?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands', 'name')
                    ->ignore($id)
                    ->whereNull('deleted_at')
            ],
            'status' => 'nullable|boolean',
            'image' => 'nullable|file|image|mimes:png',
            'slug' => 'nullable|string',
            'allowed_payment_methods' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ];
    }

    protected function prepareForValidation()
    {
        $methods = $this->input('allowed_payment_methods', []);
        if (is_array($methods)) {
            $methods = implode(',', array_filter($methods));
        }
        if (empty($methods)) {
            $methods = '';
        }

        $this->merge([
            'name' => str_upper($this->name),
            'slug' => str_slug($this->name),
            'status' => $this->has('status') ? 1 : 0,
            'allowed_payment_methods' => $methods,
            'sort_order' => $this->input('sort_order'),
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Marka adı zorunludur.',
            'name.max' => 'Marka adı 255 karakterden uzun olamaz.',
            'name.unique' => 'Bu marka adı zaten kayıtlıdır.',
            'image.file' => 'Yüklenen dosya geçerli bir dosya olmalıdır.',
            'image.image' => 'Yüklenen dosya bir resim olmalıdır.',
            'image.mimes' => 'Resim yalnızca png formatında olmalıdır.'
        ];
    }
}
