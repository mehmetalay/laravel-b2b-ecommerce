<?php

namespace App\Application\Payment\Interfaces;

use App\Application\Payment\DTO\PaymentCallbackRequest;
use App\Application\Payment\DTO\PaymentCallbackResult;
use App\Application\Payment\DTO\PaymentGatewayRequest;
use App\Application\Payment\DTO\PaymentGatewayResult;
use App\Application\Payment\DTO\PaymentRefundRequest;

interface BankPaymentProviderInterface
{
    public function supportsBank(int $bankIntegrationId): bool;

    public function start3D(PaymentGatewayRequest $request): PaymentGatewayResult;

    public function startNon3D(PaymentGatewayRequest $request): PaymentGatewayResult;

    public function resolveCallback(PaymentCallbackRequest $request): PaymentCallbackResult;

    public function cancel(PaymentRefundRequest $request): PaymentGatewayResult;

    public function refund(PaymentRefundRequest $request): PaymentGatewayResult;

    // TODO(payment-hardening): Do not add checkStatus in this phase to avoid provider contract break.
    // Next phase can introduce an optional status query capability for safe pending reconciliation.
}
