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
use App\Infrastructure\Payment\Clients\VakifKatilimPosClient;

class VakifKatilimPaymentProvider implements BankPaymentProviderInterface, BankPaymentStatusQueryableInterface, BankCodeAwarePaymentProviderInterface
{
    private const BANK_ID = 5;
    private const BANK_CODES = ['vakif_katilim'];

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
        $service = new VakifKatilimPosClient($request->toProviderPayload());
        $result = $service->process3DTransaction();

        if (!($result['success'] ?? false)) {
            return PaymentGatewayResult::failure('Islem baslatilamadi. Lutfen tekrar deneyiniz.');
        }

        return PaymentGatewayResult::successHtml((string) ($result['html'] ?? ''));
    }

    public function startNon3D(PaymentGatewayRequest $request): PaymentGatewayResult
    {
        return PaymentGatewayResult::failure('Secilen bankada 3Dsiz odeme desteklenmiyor.');
    }

    public function resolveCallback(PaymentCallbackRequest $request): PaymentCallbackResult
    {
        $payload = $request->payload;
        $responseCode = (string) ($payload['ResponseCode'] ?? '');
        $md = (string) ($payload['MD'] ?? '');

        if ($responseCode !== '00' || $md === '') {
            $message = (string) ($payload['ResponseMessage'] ?? 'Odeme Islemi Basarisiz.');
            return PaymentCallbackResult::failure($message, $payload);
        }

        $serviceData = [
            'bank_integration_information' => $request->bankIntegrationInformation,
            'amount' => number_format($request->amount, 2, '.', ''),
            'ok_url' => $request->okUrl,
            'fail_url' => $request->failUrl,
            'oid' => $request->oid,
        ];

        $service = new VakifKatilimPosClient($serviceData);
        $result = $service->processSaleTransaction(['md' => $md]);

        if (!($result['success'] ?? false)) {
            return PaymentCallbackResult::failure(
                (string) ($result['message'] ?? 'Odeme islemi basarisiz.'),
                $payload
            );
        }

        $xml = $result['xml'] ?? null;
        if ($xml && (string) $xml->ResponseCode === '00') {
            $rrn = (string) ($xml->RRN ?? $xml->OrderContract->RRN ?? '');
            $authCode = (string) ($xml->ProvNumber ?? $xml->OrderContract->ProvNumber ?? '');

            return PaymentCallbackResult::success(array_merge($payload, [
                'provider_reference' => $rrn,
                'Rrn' => $rrn,
                'AuthCode' => $authCode,
            ]));
        }

        $message = $xml && isset($xml->ResponseMessage)
            ? (string) $xml->ResponseMessage
            : 'Odeme islemi basarisiz.';

        return PaymentCallbackResult::failure($message, $payload);
    }

    public function cancel(PaymentRefundRequest $request): PaymentGatewayResult
    {
        $service = new VakifKatilimPosClient($request->toProviderPayload());
        $result = $service->cancel($request->toProviderPayload());

        return ($result['success'] ?? false)
            ? PaymentGatewayResult::successMessage((string) ($result['message'] ?? 'Iptal islemi basarili.'))
            : PaymentGatewayResult::failure((string) ($result['message'] ?? 'Iptal islemi basarisiz.'));
    }

    public function refund(PaymentRefundRequest $request): PaymentGatewayResult
    {
        $service = new VakifKatilimPosClient($request->toProviderPayload());
        $result = $service->refund($request->toProviderPayload());

        return ($result['success'] ?? false)
            ? PaymentGatewayResult::successMessage((string) ($result['message'] ?? 'Iade islemi basarili.'))
            : PaymentGatewayResult::failure((string) ($result['message'] ?? 'Iade islemi basarisiz.'));
    }

    public function checkStatus(PaymentStatusQueryRequest $request): PaymentStatusQueryResult
    {
        $service = new VakifKatilimPosClient($request->toProviderPayload());
        $result = $service->queryStatus($request->toProviderPayload());

        if (!($result['success'] ?? false)) {
            return new PaymentStatusQueryResult(
                supported: true,
                status: 'unknown',
                message: (string) ($result['message'] ?? 'vakif_katilim_status_query_failed'),
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




