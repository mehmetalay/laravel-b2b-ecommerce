<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Slider;

class SliderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        /** @var \App\Models\Slider|null $slider */
        $slider = $this->route('slider');
        $id = $slider?->id;

        return [
            'type' => [
                'required',
                'string',
                Rule::in([
                    'slider',
                    'payment_slider',
                    'category_slider',
                    'campaign_slider',
                ]),
            ],

            'status' => 'nullable|boolean',
            'target_blank' => 'nullable|boolean',

            'link' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',

            // ======================
            // DESKTOP (TR ZORUNLU)
            // ======================
            'image_desktop_tr' => [
                $id ? 'nullable' : 'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,gif',
            ],

            'image_desktop_en' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,gif',
            ],

            // ======================
            // TABLET
            // ======================
            'image_tablet_tr' => 'nullable|file|image|mimes:jpg,jpeg,png,gif',
            'image_tablet_en' => 'nullable|file|image|mimes:jpg,jpeg,png,gif',

            // ======================
            // MOBILE
            // ======================
            'image_mobile_tr' => 'nullable|file|image|mimes:jpg,jpeg,png,gif',
            'image_mobile_en' => 'nullable|file|image|mimes:jpg,jpeg,png,gif',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->has('status') ? 1 : 0,
            'target_blank' => $this->has('target_blank') ? 1 : 0,
            'sort_order' => $this->route('slider') ? $this->route('slider')->sort_order : Slider::count() + 1,
        ]);
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Slider tipi zorunludur.',
            'type.in' => 'Geçersiz slider tipi seçildi.',

            'image_desktop_tr.required' => 'Masaüstü Türkçe slider görseli zorunludur.',
            'image_desktop_tr.image' => 'Yüklenen dosya geçerli bir resim olmalıdır.',
            'image_desktop_tr.mimes' => 'Resim formatı jpg, jpeg, png veya gif olmalıdır.',

            'image_desktop_en.image' => 'İngilizce masaüstü görseli resim formatında olmalıdır.',

            'link.max' => 'Link 500 karakterden uzun olamaz.',
            'sort_order.integer' => 'Sıralama değeri sayı olmalıdır.',
        ];
    }
}
