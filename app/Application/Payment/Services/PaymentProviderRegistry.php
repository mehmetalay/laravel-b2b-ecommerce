<?php

namespace App\Application\Payment\Services;

use App\Application\Payment\Interfaces\BankCodeAwarePaymentProviderInterface;
use App\Application\Payment\Interfaces\BankPaymentProviderInterface;
use App\Models\BankIntegration;
use InvalidArgumentException;

class PaymentProviderRegistry
{
    /**
     * @var BankPaymentProviderInterface[]|null
     */
    private ?array $providers = null;

    /**
     * @return BankPaymentProviderInterface[]
     */
    public function all(): array
    {
        if ($this->providers !== null) {
            return $this->providers;
        }

        $providers = [];

        foreach ((array) config('payment_providers.providers', []) as $providerClass) {
            $provider = app($providerClass);

            if ($provider instanceof BankPaymentProviderInterface) {
                $providers[] = $provider;
            }
        }

        $this->providers = $providers;

        return $this->providers;
    }

    public function resolve(int $bankIntegrationId): BankPaymentProviderInterface
    {
        $bankCode = $this->resolveBankCode($bankIntegrationId);
        if ($bankCode !== null) {
            foreach ($this->all() as $provider) {
                if (
                    $provider instanceof BankCodeAwarePaymentProviderInterface
                    && $provider->supportsBankCode($bankCode)
                ) {
                    return $provider;
                }
            }
        }

        foreach ($this->all() as $provider) {
            if ($provider->supportsBank($bankIntegrationId)) {
                return $provider;
            }
        }

        throw new InvalidArgumentException(
            "Bank provider not configured for integration id {$bankIntegrationId}"
            . ($bankCode !== null ? " (bank_code: {$bankCode})" : '')
        );
    }

    private function resolveBankCode(int $bankIntegrationId): ?string
    {
        $bankCode = BankIntegration::query()
            ->whereKey($bankIntegrationId)
            ->value('bank_code');

        $bankCode = strtolower(trim((string) $bankCode));

        return $bankCode !== '' ? $bankCode : null;
    }
}
