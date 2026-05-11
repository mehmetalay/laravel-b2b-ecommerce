<?php

namespace App\Infrastructure\Payment\Clients;

use Spatie\ArrayToXml\ArrayToXml;

class VakifbankPosClient
{
    public function start3D(array $data): array
    {
        $posXml = $this->enrollmentCheck($data);
        $status = (string) ($posXml->Message->VERes->Status ?? '');

        if (in_array($status, ['N', 'U', 'E'], true)) {
            $error = (string) ($posXml->ErrorMessage ?? 'Islem baslatilamadi.');
            return ['success' => false, 'message' => $error];
        }

        return [
            'success' => true,
            'html' => $this->build3DHtml($posXml->Message->VERes),
        ];
    }

    public function pay(array $payload, object $bankIntegrationJson): mixed
    {
        $data = [
            'TransactionType' => 'Sale',
            'MerchantId' => $bankIntegrationJson->IsyeriNo,
            'TerminalNo' => $bankIntegrationJson->TerminalNo,
            'Password' => $bankIntegrationJson->IsyeriSifre,
            'ECI' => $payload['Eci'] ?? null,
            'CAVV' => $payload['Cavv'] ?? null,
            'MpiTransactionId' => $payload['VerifyEnrollmentRequestId'] ?? null,
            'ClientIp' => '144.76.62.72',
            'TransactionDeviceSource' => 0,
        ];

        if (!empty($payload['InstallmentCount'])) {
            $data['NumberOfInstallments'] = $payload['InstallmentCount'];
        }

        $xml = ArrayToXml::convert($data, 'VposRequest', true, 'utf-8');
        logSession("VakifbankPosClient pay request", $xml, 'info', 'payment_logs');

        $result = simplexml_load_string($this->curlVpos($xml), "SimpleXMLElement", LIBXML_NOCDATA);
        logSession("VakifbankPosClient pay response", (string) $result, 'info', 'payment_logs');

        return $result;
    }

    public function cancel(array $data, object $bankIntegrationJson): array
    {
        return $this->processRefundRequest($data, $bankIntegrationJson, 'Cancel');
    }

    public function refund(array $data, object $bankIntegrationJson): array
    {
        return $this->processRefundRequest($data, $bankIntegrationJson, 'Refund');
    }

    public function queryStatus(array $data): array
    {
        $info = $data['bank_integration_information'] ?? null;
        if (!$info) {
            return [
                'success' => false,
                'status' => 'unknown',
                'message' => 'bank_integration_not_found',
                'raw_payload' => [],
            ];
        }

        $oid = trim((string) ($data['oid'] ?? ''));
        if ($oid === '') {
            return [
                'success' => false,
                'status' => 'unknown',
                'message' => 'vakifbank_status_query_missing_order_id',
                'raw_payload' => [],
            ];
        }

        $createdAt = $data['created_at'] ?? null;
        $startDate = $createdAt ? date('Y-m-d', strtotime((string) $createdAt . ' -3 days')) : date('Y-m-d', strtotime('-7 days'));
        $endDate = date('Y-m-d');

        $merchantId = (string) ($info->IsyeriNo ?? '');
        $password = (string) ($info->IsyeriSifre ?? '');
        if ($merchantId === '' || $password === '') {
            return [
                'success' => false,
                'status' => 'unknown',
                'message' => 'vakifbank_status_query_invalid_configuration',
                'raw_payload' => [],
            ];
        }

        $transactionId = trim((string) ($data['provider_reference'] ?? ''));
        $xml = '<?xml version="1.0" encoding="utf-8"?>'
            . '<SearchRequest>'
            . '<MerchantCriteria>'
            . '<HostMerchantId>' . $merchantId . '</HostMerchantId>'
            . '<MerchantPassword>' . $password . '</MerchantPassword>'
            . '</MerchantCriteria>'
            . '<DateCriteria>'
            . '<StartDate>' . $startDate . '</StartDate>'
            . '<EndDate>' . $endDate . '</EndDate>'
            . '</DateCriteria>'
            . '<TransactionCriteria>'
            . '<TransactionId>' . $transactionId . '</TransactionId>'
            . '<OrderId>' . $oid . '</OrderId>'
            . '<AuthCode></AuthCode>'
            . '</TransactionCriteria>'
            . '</SearchRequest>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://apigw.vakifbank.com.tr:8443/virtualPos/Search');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/xml']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);

            return [
                'success' => false,
                'status' => 'unknown',
                'message' => 'vakifbank_status_query_curl_error: ' . $error,
                'raw_payload' => [],
            ];
        }

        curl_close($ch);
        $jsonResponse = json_decode((string) $response, true);
        if (is_array($jsonResponse)) {
            $resultCode = trim((string) ($jsonResponse['ResultCode'] ?? ''));
            $resultDetail = trim((string) ($jsonResponse['ResultDetail'] ?? $jsonResponse['message'] ?? ''));

            if ($resultCode === '6011' || self::containsInvalidIpText($resultDetail)) {
                return [
                    'success' => true,
                    'status' => 'unknown',
                    'message' => 'vakifbank_status_query_invalid_ip',
                    'provider_reference' => null,
                    'auth_code' => null,
                    'rrn' => null,
                    'raw_payload' => $jsonResponse,
                ];
            }

            return [
                'success' => true,
                'status' => 'unknown',
                'message' => $resultDetail !== '' ? $resultDetail : 'vakifbank_status_query_unknown_json_response',
                'provider_reference' => null,
                'auth_code' => null,
                'rrn' => null,
                'raw_payload' => $jsonResponse,
            ];
        }

        $xmlResponse = @simplexml_load_string((string) $response);
        if (!$xmlResponse) {
            return [
                'success' => true,
                'status' => 'unknown',
                'message' => 'vakifbank_status_query_invalid_response',
                'provider_reference' => null,
                'auth_code' => null,
                'rrn' => null,
                'raw_payload' => ['response' => $response],
            ];
        }

        $rawPayload = self::xmlToArray($xmlResponse);
        $statusText = strtolower(trim((string) ($xmlResponse->ResponseInfo->Status ?? '')));
        $responseCode = trim((string) ($xmlResponse->ResponseInfo->ResponseCode ?? ''));
        $responseMessage = trim((string) ($xmlResponse->ResponseInfo->ResponseMessage ?? ''));
        $totalItemCount = (int) ($xmlResponse->TotalItemCount ?? 0);

        if ($totalItemCount <= 0 || self::containsNotFoundText($responseMessage)) {
            return [
                'success' => true,
                'status' => 'unknown',
                'message' => $responseMessage !== '' ? $responseMessage : 'vakifbank_status_query_not_found',
                'provider_reference' => null,
                'auth_code' => null,
                'rrn' => null,
                'raw_payload' => $rawPayload,
            ];
        }

        $records = $xmlResponse->TransactionSearchResultInfo ?? [];
        $recordList = [];
        foreach ($records as $record) {
            $recordList[] = $record;
        }

        $matched = null;
        foreach ($recordList as $record) {
            $recordOrderId = trim((string) ($record->OrderId ?? ''));
            if ($recordOrderId !== '' && $recordOrderId === $oid) {
                $matched = $record;
                break;
            }
        }

        if ($matched === null && count($recordList) === 1) {
            $matched = $recordList[0];
        }

        if ($matched === null) {
            return [
                'success' => true,
                'status' => 'unknown',
                'message' => 'vakifbank_status_query_not_found',
                'provider_reference' => null,
                'auth_code' => null,
                'rrn' => null,
                'raw_payload' => $rawPayload,
            ];
        }

        $resultCode = trim((string) ($matched->ResultCode ?? ''));
        $hostResultCode = trim((string) ($matched->HostResultCode ?? ''));
        $status = 'unknown';

        if ($statusText === 'success' && $responseCode === '0000' && ($resultCode === '0000' || $hostResultCode === '000')) {
            $status = 'success';
        } elseif (self::isDefinitiveFailureCode($resultCode, $hostResultCode)) {
            $status = 'failed';
        }

        return [
            'success' => true,
            'status' => $status,
            'message' => $responseMessage !== '' ? $responseMessage : null,
            'provider_reference' => self::firstNonEmptyString([
                (string) ($matched->TransactionId ?? ''),
            ]),
            'auth_code' => self::firstNonEmptyString([
                (string) ($matched->AuthCode ?? ''),
            ]),
            'rrn' => self::firstNonEmptyString([
                (string) ($matched->Rrn ?? ''),
            ]),
            'raw_payload' => $rawPayload,
        ];
    }

    private function enrollmentCheck(array $data): mixed
    {
        $params = [
            'VerifyEnrollmentRequestId' => $data['oid'],
            'Pan' => $data['credit_card_number'],
            'ExpiryDate' => $data['credit_card_exp_date_year'] . $data['credit_card_exp_date_month'],
            'PurchaseAmount' => str_replace(',', '', number_format(round($data['amount'], 2), 2)),
            'Currency' => '949',
            'SuccessUrl' => $data['ok_url'],
            'FailureUrl' => $data['fail_url'],
            'MerchantId' => $data['bank_integration_information']->IsyeriNo,
            'MerchantPassword' => $data['bank_integration_information']->IsyeriSifre,
        ];

        if ((int) ($data['installment'] ?? 1) !== 1) {
            $params['InstallmentCount'] = $data['installment'];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://3dsecure.vakifbank.com.tr:4443/MPIAPI/MPI_Enrollment.aspx');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        $result = curl_exec($ch);

        if ($result === false) {
            logSession("VakifbankPosClient enrollment check cURL Error", ['message' => curl_error($ch)], 'info', 'payment_logs');
        } else {
            logSession("VakifbankPosClient enrollment check cURL result", $result, 'info', 'payment_logs');
        }

        curl_close($ch);

        return simplexml_load_string($result, "SimpleXMLElement", LIBXML_NOCDATA);
    }

    private function build3DHtml(mixed $veRes): string
    {
        return '<form id="form" method="POST" action="' . $veRes->ACSUrl . '">
                    <input type="hidden" name="PaReq" value="' . $veRes->PaReq . '">
                    <input type="hidden" name="TermUrl" value="' . $veRes->TermUrl . '">
                    <input type="hidden" name="MD" value="' . $veRes->MD . '">
                </form>';
    }

    private function processRefundRequest(array $data, object $bankIntegrationJson, string $type): array
    {
        $xmlData = [
            'TransactionType' => $type,
            'MerchantId' => $bankIntegrationJson->IsyeriNo,
            'TerminalNo' => $bankIntegrationJson->TerminalNo,
            'Password' => $bankIntegrationJson->IsyeriSifre,
            'ReferenceTransactionId' => $data['provider_reference'],
            'ClientIp' => '144.76.62.72',
            'TransactionDeviceSource' => 0,
        ];

        if ($type === 'Refund') {
            $xmlData['CurrencyAmount'] = str_replace(',', '', number_format($data['amount'], 2));
            $xmlData['CurrencyCode'] = '949';
        }

        $xml = ArrayToXml::convert($xmlData, 'VposRequest', true, 'utf-8');
        $typeName = $type === 'Cancel' ? 'Iptal' : 'Iade';

        logSession("VakifbankPosClient {$typeName} istegi gonderiliyor.", [
            'oid' => $data['oid'],
            'provider_reference' => $data['provider_reference'],
            'amount' => $data['amount'],
            'xml' => $xml,
        ], 'info', 'payment_logs');

        $result = simplexml_load_string($this->curlVpos($xml), "SimpleXMLElement", LIBXML_NOCDATA);

        logSession("VakifbankPosClient {$typeName} yaniti.", [
            'response' => $result ? json_encode($result) : 'null',
        ], 'info', 'payment_logs');

        if ($result && isset($result->ResultCode) && (string) $result->ResultCode === '0000') {
            return [
                'success' => true,
                'message' => "{$typeName} islemi basarili.",
            ];
        }

        $errorMsg = $result && isset($result->ResultDetail) ? (string) $result->ResultDetail : '';

        return [
            'success' => false,
            'message' => "{$typeName} islemi basarisiz." . ($errorMsg ? " Hata: {$errorMsg}" : ''),
        ];
    }

    private function curlVpos(string $xml): mixed
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://onlineodeme.vakifbank.com.tr:4443/VposService/v3/Vposreq.aspx');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'prmstr=' . $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 59);
        curl_setopt($ch, CURLOPT_SSL_OPTIONS, ["CURLOPT_SSLVERSION" => "CURL_SSLVERSION_TLSv1_1"]);
        $result = curl_exec($ch);

        if ($result === false) {
            $error = curl_error($ch);
            logSession("VakifbankPosClient cURL Error", ['message' => $error], 'info', 'payment_logs');
        }

        curl_close($ch);
        logSession("VakifbankPosClient cURL result", $result, 'info', 'payment_logs');

        return $result;
    }

    private static function xmlToArray(\SimpleXMLElement $xml): array
    {
        $json = json_encode($xml, JSON_UNESCAPED_UNICODE);
        $decoded = json_decode((string) $json, true);

        return is_array($decoded) ? $decoded : [];
    }

    private static function containsNotFoundText(string $value): bool
    {
        $value = strtolower(trim($value));

        return str_contains($value, 'not found')
            || str_contains($value, 'kayit bulunamadi')
            || str_contains($value, 'kayıt bulunamadı')
            || str_contains($value, 'islem bulunamadi')
            || str_contains($value, 'işlem bulunamadı');
    }

    private static function isDefinitiveFailureCode(string $resultCode, string $hostResultCode): bool
    {
        if ($resultCode !== '' && $resultCode !== '0000') {
            return true;
        }

        return $hostResultCode !== '' && $hostResultCode !== '000';
    }

    private static function firstNonEmptyString(array $values): ?string
    {
        foreach ($values as $value) {
            $trimmed = trim((string) $value);
            if ($trimmed !== '') {
                return $trimmed;
            }
        }

        return null;
    }

    private static function containsInvalidIpText(string $value): bool
    {
        $value = strtolower(trim($value));

        return str_contains($value, 'gecersiz is yeri ip adresi')
            || str_contains($value, 'geçersiz iş yeri ip adresi')
            || str_contains($value, 'invalid merchant ip');
    }
}
