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
use App\Infrastructure\Payment\Clients\EstPosClient;

class EstBankPaymentProvider implements BankPaymentProviderInterface, BankPaymentStatusQueryableInterface, BankCodeAwarePaymentProviderInterface
{
    use MapsProviderFields;

    private const BANK_IDS = [1, 4, 10, 11];
    private const BANK_CODES = ['isbank', 'ziraat', 'halkbank', 'teb'];

    public function supportsBank(int $bankIntegrationId): bool
    {
        return in_array($bankIntegrationId, self::BANK_IDS, true);
    }

    public function supportsBankCode(?string $bankCode): bool
    {
        return in_array(strtolower(trim((string) $bankCode)), self::BANK_CODES, true);
    }

    public function start3D(PaymentGatewayRequest $request): PaymentGatewayResult
    {
        $response = (new EstPosClient($request->toProviderPayload()))->generatePaymentForm();

        if (!($response['result'] ?? false)) {
            return PaymentGatewayResult::failure('Islem baslatilamadi. Lutfen tekrar deneyiniz.');
        }

        return PaymentGatewayResult::successHtml((string) ($response['response'] ?? ''));
    }

    public function startNon3D(PaymentGatewayRequest $request): PaymentGatewayResult
    {
        $bankCode = strtolower(trim((string) ($request->bankCode ?? '')));
        if ($bankCode !== 'isbank' && $request->bankIntegrationId !== 1) {
            return PaymentGatewayResult::failure('Secilen bankada 3Dsiz odeme desteklenmiyor.');
        }

        $info = $request->bankIntegrationInformation;

        $apiName = $info->api_name ?? 'api';
        $apiPassword = $info->api_password ?? ($info->store_key ?? '');
        $clientId = $info->workplace_code ?? '';

        $xml = '<?xml version="1.0" encoding="ISO-8859-9"?>'
            . '<CC5Request>'
            . "<Name>{$apiName}</Name>"
            . "<Password>{$apiPassword}</Password>"
            . "<ClientId>{$clientId}</ClientId>"
            . '<Type>Auth</Type>'
            . "<OrderId>{$request->oid}</OrderId>"
            . '<Total>' . round($request->amount, 2) . '</Total>'
            . '<Currency>949</Currency>'
            . "<Number>{$request->creditCardNumber}</Number>"
            . "<Expires>{$request->creditCardExpMonth}/{$request->creditCardExpYear}</Expires>"
            . "<Cvv2Val>{$request->cvc}</Cvv2Val>"
            . "<Instalment>{$request->installment}</Instalment>"
            . '</CC5Request>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, 'https://sanalpos.isbank.com.tr/servlet/api');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'data=' . $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        if (!is_string($result) || $result === '') {
            return PaymentGatewayResult::failure('Bankadan yanit alinamadi.');
        }

        if (!mb_check_encoding($result, 'UTF-8')) {
            $result = mb_convert_encoding($result, 'UTF-8', 'ISO-8859-1');
        }

        $responseValue = $this->extractXmlValue($result, 'Response');
        $errorValue = $this->extractXmlValue($result, 'ErrMsg');

        if ($responseValue === 'Approved') {
            return PaymentGatewayResult::successMessage(
                'Odeme Islemi Basariyla Gerceklesti!',
                ['Response' => $responseValue]
            );
        }

        $message = 'Odeme Islemi Basarisiz.' . ($errorValue !== '' ? ' Hata: ' . sanitize_error_message($errorValue) : '');

        return PaymentGatewayResult::failure($message, [
            'Response' => $responseValue,
            'ErrMsg' => $errorValue,
        ]);
    }

    public function resolveCallback(PaymentCallbackRequest $request): PaymentCallbackResult
    {
        $payload = $request->payload;
        $isSuccess = (($payload['Response'] ?? null) === 'Approved')
            || (($payload['ProcReturnCode'] ?? null) === '00');

        if ($isSuccess) {
            return PaymentCallbackResult::success(array_merge($payload, $this->mapProviderFields($payload)));
        }

        $mdStatus = (string) ($payload['mdStatus'] ?? $payload['mdstatus'] ?? '');
        $message = in_array($mdStatus, ['0', '6'], true)
            ? '3D Dogrulama Islemi Basarisiz.'
            : ('Odeme Islemi Basarisiz.' . (!empty($payload['ErrMsg']) ? ' Hata: ' . sanitize_error_message($payload['ErrMsg']) : ''));

        return PaymentCallbackResult::failure($message, $payload);
    }

    public function cancel(PaymentRefundRequest $request): PaymentGatewayResult
    {
        $result = EstPosClient::cancel($request->toProviderPayload());

        return ($result['success'] ?? false)
            ? PaymentGatewayResult::successMessage((string) ($result['message'] ?? 'Iptal islemi basarili.'))
            : PaymentGatewayResult::failure((string) ($result['message'] ?? 'Iptal islemi basarisiz.'));
    }

    public function refund(PaymentRefundRequest $request): PaymentGatewayResult
    {
        $result = EstPosClient::refund($request->toProviderPayload());

        return ($result['success'] ?? false)
            ? PaymentGatewayResult::successMessage((string) ($result['message'] ?? 'Iade islemi basarili.'))
            : PaymentGatewayResult::failure((string) ($result['message'] ?? 'Iade islemi basarisiz.'));
    }

    public function checkStatus(PaymentStatusQueryRequest $request): PaymentStatusQueryResult
    {
        $result = EstPosClient::queryStatus($request->toProviderPayload());

        if (!($result['success'] ?? false)) {
            return new PaymentStatusQueryResult(
                supported: true,
                status: 'unknown',
                message: (string) ($result['message'] ?? 'est_status_query_failed'),
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

    private function extractXmlValue(string $xml, string $tag): string
    {
        $start = strpos($xml, "<{$tag}>");
        $end = strpos($xml, "</{$tag}>");

        if ($start === false || $end === false || $end < $start) {
            return '';
        }

        $offset = $start + strlen($tag) + 2;
        return (string) substr($xml, $offset, $end - $offset);
    }
}




