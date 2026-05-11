<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CurrentAccountUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'discount_rate' => [
                'nullable',
                'numeric',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    $rate = (float) $value;
                    if (($rate > 0 && $rate < 1) || $rate < 0 || $rate > 100) {
                        $fail('Indirim orani 1 ile 100 arasinda olmalidir.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'discount_rate.numeric' => 'Indirim orani sayisal bir deger olmalidir.',
        ];
    }
}
