<?php

namespace App\Application\Payment\Interfaces;

interface BankCodeAwarePaymentProviderInterface
{
    public function supportsBankCode(?string $bankCode): bool;
}

