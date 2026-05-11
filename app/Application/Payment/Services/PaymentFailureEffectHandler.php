<?php

namespace App\Application\Payment\Services;

use App\Models\Payment;

class PaymentFailureEffectHandler
{
    public function handle(Payment $payment, string $message, array $payload = []): void
    {
        // Failure side-effectleri bu handler'a toplanir.
        // Davranis degistirmemek icin su an no-op.
    }
}
