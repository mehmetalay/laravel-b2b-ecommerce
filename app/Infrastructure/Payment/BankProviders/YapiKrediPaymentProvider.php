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
use App\Infrastructure\Payment\Clients\YapiKrediPosClient;

class YapiKrediPaymentProvider implements BankPaymentProviderInterface, BankPaymentStatusQueryableInterface, BankCodeAwarePaymentProviderInterface
{
    private const BANK_ID = 9;
    private const BANK_CODES = ['yapikredi'];

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
        $payload = $request->toProviderPayload();
        $payload['amount'] = (int) round($request->amount * 100);

        $response = (new YapiKrediPosClient($payload))->oosRequestData();

        if (($response['response'] ?? 'declined') === 'declined') {
            return PaymentGatewayResult::failure((string) ($response['message'] ?? 'Islem baslatilamadi.'));
        }

        return PaymentGatewayResult::successHtml((string) ($response['html'] ?? ''));
    }

    public function startNon3D(PaymentGatewayRequest $request): PaymentGatewayResult
    {
        return PaymentGatewayResult::failure('Secilen bankada 3Dsiz odeme desteklenmiyor.');
    }

    public function resolveCallback(PaymentCallbackRequest $request): PaymentCallbackResult
    {
        $payload = $request->payload;
        $payload['bank_integration_information'] = $request->bankIntegrationInformation;
        $payload['amount'] = $payload['Amount'] ?? $request->amount;

        $response = (new YapiKrediPosClient($payload))->oosResolveMerchantData();

        if (($response['response'] ?? 'declined') === 'declined') {
            return PaymentCallbackResult::failure(
                (string) ($response['message'] ?? 'Odeme islemi basarisiz.'),
                $request->payload
            );
        }

        $hostLogKey = (string) ($response['hostLogKey'] ?? '');
        $authCode = (string) ($response['authCode'] ?? '');
        $rrn = (string) ($response['rrn'] ?? '');

        $resolvedPayload = array_merge($request->payload, [
            'hostLogKey' => $hostLogKey,
            'AuthCode' => $authCode,
            'Rrn' => $rrn,
            'provider_reference' => $hostLogKey,
        ]);

        return PaymentCallbackResult::success($resolvedPayload);
    }

    public function cancel(PaymentRefundRequest $request): PaymentGatewayResult
    {
        $result = YapiKrediPosClient::cancel($request->toProviderPayload());

        return ($result['success'] ?? false)
            ? PaymentGatewayResult::successMessage((string) ($result['message'] ?? 'Iptal islemi basarili.'))
            : PaymentGatewayResult::failure((string) ($result['message'] ?? 'Iptal islemi basarisiz.'));
    }

    public function refund(PaymentRefundRequest $request): PaymentGatewayResult
    {
        $result = YapiKrediPosClient::refund($request->toProviderPayload());

        return ($result['success'] ?? false)
            ? PaymentGatewayResult::successMessage((string) ($result['message'] ?? 'Iade islemi basarili.'))
            : PaymentGatewayResult::failure((string) ($result['message'] ?? 'Iade islemi basarisiz.'));
    }

    public function checkStatus(PaymentStatusQueryRequest $request): PaymentStatusQueryResult
    {
        $result = YapiKrediPosClient::queryStatus($request->toProviderPayload());

        if (!($result['success'] ?? false)) {
            return new PaymentStatusQueryResult(
                supported: true,
                status: 'unknown',
                message: (string) ($result['message'] ?? 'yapikredi_status_query_failed'),
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




