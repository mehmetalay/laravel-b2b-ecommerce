<?php

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentLinkListInstallmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxAmount = (float) (config('payment.max_amount') ?? 0);
        $amountRules = ['required', 'numeric', 'min:1'];
        if ($maxAmount > 0) {
            $amountRules[] = 'max:' . $maxAmount;
        }

        return [
            'token' => [
                'required',
                'string',
                Rule::exists('payment_links', 'token')
                    ->where(function ($q) {
                        $q->where('status', 1)
                            ->where('is_paid', 0)
                            ->whereNull('deleted_at');
                    }),
            ],
            'amount' => ['required', 'string', 'max:32'],
            'amount_numeric' => $amountRules,
            'bank_integration_id' => [
                'required',
                'integer',
                Rule::exists('bank_integrations', 'id')->where(fn ($q) => $q->where('status', 1)),
            ],
            'credit_card_number' => ['nullable', 'string', 'min:8', 'max:24'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'amount_numeric' => $this->normalizeAmount((string) $this->input('amount', '')),
        ]);
    }

    private function normalizeAmount(string $raw): float
    {
        $value = trim(str_replace(' ', '', $raw));
        if ($value === '') {
            return 0.0;
        }

        $commaPos = strrpos($value, ',');
        $dotPos = strrpos($value, '.');

        if ($commaPos !== false && $dotPos !== false) {
            if ($commaPos > $dotPos) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif ($commaPos !== false) {
            $value = str_replace(',', '.', $value);
        }

        return (float) $value;
    }
}
