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
use App\Infrastructure\Payment\Clients\DenizbankPosClient;

class DenizbankPaymentProvider implements BankPaymentProviderInterface, BankPaymentStatusQueryableInterface, BankCodeAwarePaymentProviderInterface
{
    use MapsProviderFields;

    private const BANK_ID = 8;
    private const BANK_CODES = ['denizbank'];

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
        $response = (new DenizbankPosClient($request->toProviderPayload()))->generatePaymentForm();

        if (!($response['result'] ?? false)) {
            return PaymentGatewayResult::failure('Islem baslatilamadi. Lutfen tekrar deneyiniz.');
        }

        return PaymentGatewayResult::successHtml((string) ($response['response'] ?? ''));
    }

    public function startNon3D(PaymentGatewayRequest $request): PaymentGatewayResult
    {
        return PaymentGatewayResult::failure('Secilen bankada 3Dsiz odeme desteklenmiyor.');
    }

    public function resolveCallback(PaymentCallbackRequest $request): PaymentCallbackResult
    {
        $payload = $request->payload;
        $status = (string) ($payload['3DStatus'] ?? '');
        $is3dOk = in_array($status, ['1', '2', '3', '4'], true);
        $isSuccess = $is3dOk && (($payload['ProcReturnCode'] ?? null) === '00');

        if ($isSuccess) {
            $providerReference = (string) ($payload['TransId'] ?? $payload['HostRefNum'] ?? '');
            $resolvedPayload = array_merge($payload, [
                'provider_reference' => $providerReference,
            ], $this->mapProviderFields($payload));

            return PaymentCallbackResult::success($resolvedPayload);
        }

        $message = '3D Dogrulama Islemi Basarisiz.'
            . (!empty($payload['ErrorMessage']) ? ' Hata: ' . sanitize_error_message($payload['ErrorMessage']) : '');

        return PaymentCallbackResult::failure($message, $payload);
    }

    public function cancel(PaymentRefundRequest $request): PaymentGatewayResult
    {
        $result = DenizbankPosClient::cancel($request->toProviderPayload());

        return ($result['success'] ?? false)
            ? PaymentGatewayResult::successMessage((string) ($result['message'] ?? 'Iptal islemi basarili.'))
            : PaymentGatewayResult::failure((string) ($result['message'] ?? 'Iptal islemi basarisiz.'));
    }

    public function refund(PaymentRefundRequest $request): PaymentGatewayResult
    {
        $result = DenizbankPosClient::refund($request->toProviderPayload());

        return ($result['success'] ?? false)
            ? PaymentGatewayResult::successMessage((string) ($result['message'] ?? 'Iade islemi basarili.'))
            : PaymentGatewayResult::failure((string) ($result['message'] ?? 'Iade islemi basarisiz.'));
    }

    public function checkStatus(PaymentStatusQueryRequest $request): PaymentStatusQueryResult
    {
        $result = DenizbankPosClient::queryStatus($request->toProviderPayload());

        if (!($result['success'] ?? false)) {
            return new PaymentStatusQueryResult(
                supported: true,
                status: 'unknown',
                message: (string) ($result['message'] ?? 'denizbank_status_query_failed'),
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




