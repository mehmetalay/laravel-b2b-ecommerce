<?php

namespace App\Application\Payment\Validators;

use App\Application\Payment\DTO\PaymentRequestData;
use App\Models\BankIntegration;
use App\Models\Installment;

class PaymentRequestValidator
{
    public function validate(PaymentRequestData $dto): ?string
    {
        if ($dto->amount < 1) {
            return 'Gecersiz odeme tutari.';
        }

        $maxAmount = (float) (config('payment.max_amount') ?? 0);
        if ($maxAmount > 0 && $dto->amount > $maxAmount) {
            return 'Odeme tutari izin verilen ust limiti asiyor.';
        }

        if ($dto->bankIntegrationId <= 0) {
            return 'Banka secimi gecersiz.';
        }

        if ($dto->installmentId <= 0) {
            return 'Taksit secimi gecersiz.';
        }

        if (trim((string) $dto->creditCardNumber) === '' || trim((string) $dto->cvc) === '') {
            return 'Kart bilgileri eksik.';
        }

        if (strlen((string) $dto->creditCardExpMonth) < 1 || strlen((string) $dto->creditCardExpYear) < 1) {
            return 'Kart son kullanma tarihi gecersiz.';
        }

        if (!BankIntegration::query()->where('id', $dto->bankIntegrationId)->where('status', 1)->exists()) {
            return 'Banka secimi gecersiz.';
        }

        return null;
    }

    public function validateInstallmentBinding(Installment $installment, int $bankIntegrationId): ?string
    {
        if ((int) $installment->status !== 1) {
            return 'Secilen taksit aktif degil.';
        }

        if ((int) $installment->bank_integration_id !== $bankIntegrationId) {
            return 'Secilen taksit bilgisi banka ile uyusmuyor.';
        }

        if (!$installment->relationLoaded('bankIntegration')) {
            $installment->load('bankIntegration');
        }

        if ((int) ($installment->bankIntegration->status ?? 0) !== 1) {
            return 'Secilen banka aktif degil.';
        }

        return null;
    }
}
