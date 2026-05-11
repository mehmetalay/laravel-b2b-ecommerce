<?php

namespace App\Application\Payment\Services;

use App\Application\Payment\DTO\PaymentCallbackRequest;
use App\Application\Payment\DTO\PaymentCallbackResult;
use App\Application\Payment\DTO\PaymentGatewayRequest;
use App\Application\Payment\DTO\PaymentGatewayResult;
use App\Application\Payment\DTO\PaymentRefundRequest;
use App\Application\Payment\DTO\PaymentStatusQueryRequest;
use App\Application\Payment\DTO\PaymentStatusQueryResult;
use App\Application\Payment\Interfaces\BankPaymentStatusQueryableInterface;
use App\Models\Payment;

class PaymentOrchestrationService
{
    public function __construct(
        private PaymentProviderRegistry $providerRegistry
    ) {}

    public function start3D(PaymentGatewayRequest $request): PaymentGatewayResult
    {
        try {
            $provider = $this->providerRegistry->resolve($request->bankIntegrationId);
            return $provider->start3D($request);
        } catch (\Throwable $e) {
            logException($e, 'PaymentOrchestrationService::start3D', true);
            return PaymentGatewayResult::failure('Islem baslatilamadi. Lutfen tekrar deneyiniz.');
        }
    }

    public function startNon3D(PaymentGatewayRequest $request): PaymentGatewayResult
    {
        try {
            $provider = $this->providerRegistry->resolve($request->bankIntegrationId);
            return $provider->startNon3D($request);
        } catch (\Throwable $e) {
            logException($e, 'PaymentOrchestrationService::startNon3D', true);
            return PaymentGatewayResult::failure('3Dsiz odeme islemi baslatilamadi.');
        }
    }

    public function resolveCallback(PaymentCallbackRequest $request): PaymentCallbackResult
    {
        try {
            $provider = $this->providerRegistry->resolve($request->bankIntegrationId);
            return $provider->resolveCallback($request);
        } catch (\Throwable $e) {
            logException($e, 'PaymentOrchestrationService::resolveCallback', true);
            return PaymentCallbackResult::failure('Odeme callback islemi sirasinda hata olustu.', $request->payload);
        }
    }

    public function cancel(PaymentRefundRequest $request): PaymentGatewayResult
    {
        try {
            $provider = $this->providerRegistry->resolve($request->bankIntegrationId);
            return $provider->cancel($request);
        } catch (\Throwable $e) {
            logException($e, 'PaymentOrchestrationService::cancel', true);
            return PaymentGatewayResult::failure('Iptal islemi sirasinda hata olustu.');
        }
    }

    public function refund(PaymentRefundRequest $request): PaymentGatewayResult
    {
        try {
            $provider = $this->providerRegistry->resolve($request->bankIntegrationId);
            return $provider->refund($request);
        } catch (\Throwable $e) {
            logException($e, 'PaymentOrchestrationService::refund', true);
            return PaymentGatewayResult::failure('Iade islemi sirasinda hata olustu.');
        }
    }

    public function queryPaymentStatus(Payment $payment): PaymentStatusQueryResult
    {
        try {
            $bankIntegration = $payment->bankIntegration;
            if ($bankIntegration === null) {
                return new PaymentStatusQueryResult(
                    supported: false,
                    status: 'unknown',
                    message: 'bank_integration_not_found',
                    providerReference: null,
                    authCode: null,
                    rrn: null,
                    rawPayload: []
                );
            }

            $provider = $this->providerRegistry->resolve((int) $payment->bank_integration_id);

            if (!$provider instanceof BankPaymentStatusQueryableInterface) {
                return PaymentStatusQueryResult::unsupported();
            }

            $bankIntegrationInformation = json_decode((string) ($bankIntegration?->json ?? '{}'));
            if (!is_object($bankIntegrationInformation)) {
                $bankIntegrationInformation = (object) [];
            }

            $request = new PaymentStatusQueryRequest(
                paymentId: (int) $payment->id,
                bankIntegrationId: (int) $payment->bank_integration_id,
                bankCode: strtolower(trim((string) ($bankIntegration->bank_code ?? ''))) ?: null,
                bankIntegrationInformation: $bankIntegrationInformation,
                oid: (string) $payment->oid,
                amount: (float) $payment->amount_paid,
                providerReference: $payment->provider_reference ?: null,
                createdAt: $payment->created_at?->toDateTimeString()
            );

            return $provider->checkStatus($request);
        } catch (\Throwable $e) {
            logException($e, 'PaymentOrchestrationService::queryPaymentStatus', true);

            return new PaymentStatusQueryResult(
                supported: true,
                status: 'unknown',
                message: 'provider_status_check_failed',
                providerReference: null,
                authCode: null,
                rrn: null,
                rawPayload: ['error' => $e->getMessage()]
            );
        }
    }
}
