<?php

namespace App\Infrastructure\Payment\BankProviders;

use App\Application\Payment\DTO\PaymentCallbackRequest;
use App\Application\Payment\DTO\PaymentCallbackResult;
use App\Application\Payment\DTO\PaymentGatewayRequest;
use App\Application\Payment\DTO\PaymentGatewayResult;
use App\Application\Payment\DTO\PaymentRefundRequest;
use App\Application\Payment\DTO\PaymentStatusQueryRequest;
use App\Application\Payment\DTO\PaymentStatusQueryResult;
use App\Application\Payment\Interfaces\BankCodeAwarePaymentProviderInterface;
use App\Application\Payment\Interfaces\BankPaymentProviderInterface;
use App\Application\Payment\Interfaces\BankPaymentStatusQueryableInterface;
use App\Infrastructure\Payment\BankProviders\Concerns\MapsProviderFields;
use App\Infrastructure\Payment\Clients\VakifbankPosClient;

class VakifbankPaymentProvider implements BankPaymentProviderInterface, BankPaymentStatusQueryableInterface, BankCodeAwarePaymentProviderInterface
{
    use MapsProviderFields;

    private const BANK_ID = 3;
    private const BANK_CODES = ['vakifbank'];

    public function __construct(
        private VakifbankPosClient $vakifbankPosClient
    ) {}

    public function supportsBank(int $bankIntegrationId): bool
    {
        return $bankIntegrationId === self::BANK_ID;
    }

    public function supportsBankCode(?string $bankCode): bool
    {
        return in_array(strtolower(trim((string) $bankCode)), self::BANK_CODES, true);
    }

    public function start3D(PaymentGatewayRequest $request): PaymentGatewayResult
    {
        $result = $this->vakifbankPosClient->start3D($request->toProviderPayload());

        if (!($result['success'] ?? false)) {
            return PaymentGatewayResult::failure((string) ($result['message'] ?? 'Islem baslatilamadi.'));
        }

        return PaymentGatewayResult::successHtml((string) ($result['html'] ?? ''));
    }

    public function startNon3D(PaymentGatewayRequest $request): PaymentGatewayResult
    {
        return PaymentGatewayResult::failure('Secilen bankada 3Dsiz odeme desteklenmiyor.');
    }

    public function resolveCallback(PaymentCallbackRequest $request): PaymentCallbackResult
    {
        $result = $this->vakifbankPosClient->pay($request->payload, $request->bankIntegrationInformation);

        if (!$result) {
            return PaymentCallbackResult::failure('Odeme Islemi Basarisiz.', $request->payload);
        }

        if ((string) ($result->ResultCode ?? '') !== '0000') {
            return PaymentCallbackResult::failure(
                'Odeme Islemi Basarisiz. Hata: ' . (string) ($result->ResultDetail ?? ''),
                $request->payload
            );
        }

        $extra = [
            'TransactionId' => (string) ($result->TransactionId ?? ''),
            'AuthCode' => (string) ($result->AuthCode ?? ''),
            'Rrn' => (string) ($result->Rrn ?? ''),
            'provider_reference' => (string) ($result->TransactionId ?? ''),
        ];

        $payload = array_merge($request->payload, $extra, $this->mapProviderFields($extra));
        return PaymentCallbackResult::success($payload);
    }

    public function cancel(PaymentRefundRequest $request): PaymentGatewayResult
    {
        $result = $this->vakifbankPosClient->cancel($request->toProviderPayload(), $request->bankIntegrationInformation);

        return ($result['success'] ?? false)
            ? PaymentGatewayResult::successMessage((string) ($result['message'] ?? 'Iptal islemi basarili.'))
            : PaymentGatewayResult::failure((string) ($result['message'] ?? 'Iptal islemi basarisiz.'));
    }

    public function refund(PaymentRefundRequest $request): PaymentGatewayResult
    {
        $result = $this->vakifbankPosClient->refund($request->toProviderPayload(), $request->bankIntegrationInformation);

        return ($result['success'] ?? false)
            ? PaymentGatewayResult::successMessage((string) ($result['message'] ?? 'Iade islemi basarili.'))
            : PaymentGatewayResult::failure((string) ($result['message'] ?? 'Iade islemi basarisiz.'));
    }

    public function checkStatus(PaymentStatusQueryRequest $request): PaymentStatusQueryResult
    {
        $result = $this->vakifbankPosClient->queryStatus($request->toProviderPayload());

        if (!($result['success'] ?? false)) {
            return new PaymentStatusQueryResult(
                supported: true,
                status: 'unknown',
                message: (string) ($result['message'] ?? 'vakifbank_status_query_failed'),
                providerReference: null,
                authCode: null,
                rrn: null,
                rawPayload: (array) ($result['raw_payload'] ?? [])
            );
        }

        $status = strtolower((string) ($result['status'] ?? 'unknown'));
        if (!in_array($status, ['success', 'failed', 'pending', 'unknown'], true)) {
            $status = 'unknown';
        }

        return new PaymentStatusQueryResult(
            supported: true,
            status: $status,
            message: (string) ($result['message'] ?? null),
            providerReference: $result['provider_reference'] ?? null,
            authCode: $result['auth_code'] ?? null,
            rrn: $result['rrn'] ?? null,
            rawPayload: (array) ($result['raw_payload'] ?? [])
        );
    }
}


