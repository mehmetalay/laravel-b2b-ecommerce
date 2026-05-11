<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'general_description' => 'nullable|string|max:500',
            'type' => 'required|in:product,brand,category,cart',
            'sub_type' => 'nullable|string|required_if:type,product|in:tiered_price,free_product,free_shipping,bonus_product',
            'rules' => 'required|array',
            'rules.*.rule_type' => 'required|string',
            'rules.*.extra' => 'nullable|array',
            'rules.*.extra.min_quantity' => 'nullable|numeric|min:1',
            'rules.*.extra.min_amount' => 'nullable|numeric|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|boolean',
            'auto_apply' => 'nullable|boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->has('status') ? 1 : 0,
            'auto_apply' => $this->has('auto_apply') ? 1 : 0,
        ]);
    }
}
