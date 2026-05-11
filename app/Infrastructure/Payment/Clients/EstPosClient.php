<?php

namespace App\Infrastructure\Payment\Clients;

class EstPosClient
{
    private $data;
    private $url;
    private $transactionType;
    private $workplaceCode;
    private $storeKey;
    private $amount;
    private $installment;
    private $okUrl;
    private $failUrl;
    private $oid;
    private $rnd;
    private $credit_card_name;
    private $credit_card_number;
    private $cvc;
    private $credit_card_exp_date_month;
    private $credit_card_exp_date_year;

    public function __construct($data)
    {
        logSession('EstPosClient odeme islemi icin form baslatildi.', null, 'info', 'payment_logs');

        $this->data = $data;
        $this->url = $this->data['bank_integration_information']->url;
        $this->transactionType = 'Auth';
        $this->workplaceCode = $this->data['bank_integration_information']->workplace_code;
        $this->storeKey = $this->data['bank_integration_information']->store_key;
        $this->amount = round($this->data['amount'], 2);
        $this->installment = $this->data['installment'] == 1 ? '' : $this->data['installment'];
        $this->okUrl = $this->data['ok_url'];
        $this->failUrl = $this->data['fail_url'];
        $this->oid = $this->data['oid'];
        $this->rnd = uniqid();

        $this->credit_card_name = $this->data['credit_card_name'];
        $this->credit_card_number = $this->data['credit_card_number'];
        $this->cvc = $this->data['cvc'];
        $this->credit_card_exp_date_month = $this->data['credit_card_exp_date_month'];
        $this->credit_card_exp_date_year = '20' . $this->data['credit_card_exp_date_year'];
    }

    public function generateHash()
    {
        $params = [
            'amount' => $this->amount,
            'clientid' => $this->workplaceCode,
            'creditcard_name' => $this->credit_card_name,
            'currency' => '949',
            'cv2' => $this->cvc,
            'Ecom_Payment_Card_ExpDate_Month' => $this->credit_card_exp_date_month,
            'Ecom_Payment_Card_ExpDate_Year' => $this->credit_card_exp_date_year,
            'failUrl' => $this->failUrl,
            'hashAlgorithm' => 'ver3',
            'islemtipi' => $this->transactionType,
            'lang' => 'tr',
            'oid' => $this->oid,
            'okurl' => $this->okUrl,
            'pan' => $this->credit_card_number,
            'rnd' => $this->rnd,
            'storetype' => '3d_pay',
            'taksit' => $this->installment,
            'storeKey' => $this->storeKey,
        ];

        $hashString = implode('|', array_map(function ($value) {
            return str_replace(['|', '\\'], ['\|', '\\\\'], $value);
        }, $params));

        return base64_encode(hash('sha512', $hashString, true));
    }

    public static function cancel(array $data): array
    {
        return self::processRefundRequest($data, 'Void');
    }

    public static function refund(array $data): array
    {
        return self::processRefundRequest($data, 'Credit');
    }

    public static function queryStatus(array $data): array
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
                'message' => 'est_status_query_missing_order_id',
                'raw_payload' => [],
            ];
        }

        $apiName = (string) ($info->api_name ?? 'api');
        $apiPassword = (string) ($info->api_password ?? $info->store_key ?? '');
        $clientId = (string) ($info->workplace_code ?? '');
        $bankCode = strtolower(trim((string) ($data['bank_code'] ?? '')));
        $apiUrl = self::resolveStatusApiUrl(
            $bankCode,
            (string) ($info->url ?? '')
        );

        if ($apiPassword === '' || $clientId === '' || $apiUrl === '') {
            return [
                'success' => false,
                'status' => 'unknown',
                'message' => 'est_status_query_invalid_configuration',
                'raw_payload' => [],
            ];
        }

        $xml = '<?xml version="1.0" encoding="ISO-8859-9"?>'
            . '<CC5Request>'
            . '<Name>' . $apiName . '</Name>'
            . '<Password>' . $apiPassword . '</Password>'
            . '<ClientId>' . $clientId . '</ClientId>'
            . '<OrderId>' . $oid . '</OrderId>'
            . '<Extra><ORDERSTATUS>QUERY</ORDERSTATUS></Extra>'
            . '</CC5Request>';

        logSession('EstPosClient status query request.', [
            'oid' => $oid,
            'bank_code' => $bankCode !== '' ? $bankCode : null,
            'bank_integration_id' => (int) ($data['bank_integration_id'] ?? 0),
            'url' => $apiUrl,
        ], 'info', 'payment_logs');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'data=' . $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $result = curl_exec($ch);

        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);

            logSession('EstPosClient status query cURL error.', ['message' => $error], 'error', 'payment_logs');

            return [
                'success' => false,
                'status' => 'unknown',
                'message' => 'est_status_query_curl_error: ' . $error,
                'raw_payload' => [],
            ];
        }

        curl_close($ch);

        $response = self::convertToUtf8((string) $result);
        logSession('EstPosClient status query response.', ['response' => $response], 'info', 'payment_logs');

        $xmlResponse = @simplexml_load_string($response);
        if (!$xmlResponse) {
            $responseLower = strtolower($response);
            $message = self::containsNotFoundHint($responseLower)
                ? 'est_status_query_not_found'
                : (self::containsAuthConfigHint($responseLower) ? 'est_status_query_auth_or_configuration_failed' : 'est_status_query_invalid_response');

            return [
                'success' => true,
                'status' => 'unknown',
                'message' => $message,
                'provider_reference' => null,
                'auth_code' => null,
                'rrn' => null,
                'raw_payload' => ['response' => $response],
            ];
        }

        $extraData = self::extractExtraData($xmlResponse);
        $rawPayload = self::xmlToArray($xmlResponse);
        $rawPayload['_extra'] = $extraData;

        $status = self::mapEstQueryStatus($xmlResponse, $extraData);
        $message = self::resolveEstQueryMessage($xmlResponse, $extraData);
        $providerReference = self::firstNonEmptyString([
            self::getExtraValue($extraData, ['TRANS_ID']),
            self::getExtraValue($extraData, ['HOST_REF_NUM']),
            (string) ($xmlResponse->TransId ?? ''),
            (string) ($xmlResponse->TransactionId ?? ''),
            (string) ($xmlResponse->HostRefNum ?? ''),
        ]);
        $authCode = self::firstNonEmptyString([
            self::getExtraValue($extraData, ['AUTH_CODE']),
            (string) ($xmlResponse->AuthCode ?? ''),
        ]);
        $rrn = self::firstNonEmptyString([
            self::getExtraValue($extraData, ['HOST_REF_NUM']),
            (string) ($xmlResponse->HostRefNum ?? ''),
            (string) ($xmlResponse->Rrn ?? ''),
        ]);

        return [
            'success' => true,
            'status' => $status,
            'message' => $message,
            'provider_reference' => $providerReference,
            'auth_code' => $authCode,
            'rrn' => $rrn,
            'raw_payload' => $rawPayload,
        ];
    }

    private static function processRefundRequest(array $data, string $type): array
    {
        $info = $data['bank_integration_information'];
        $apiUrl = self::resolveApiUrl((string) $info->url);

        $xml = '<?xml version="1.0" encoding="ISO-8859-9"?>'
            . '<CC5Request>'
            . '<Name>' . ($info->api_name ?? 'api') . '</Name>'
            . '<Password>' . ($info->api_password ?? $info->store_key) . '</Password>'
            . '<ClientId>' . $info->workplace_code . '</ClientId>'
            . '<Type>' . $type . '</Type>'
            . '<OrderId>' . $data['oid'] . '</OrderId>';

        if ($type === 'Credit') {
            $xml .= '<Total>' . round($data['amount'], 2) . '</Total>'
                . '<Currency>949</Currency>';
        }

        $xml .= '</CC5Request>';

        logSession("EstPosClient {$type} istegi gonderiliyor.", [
            'oid' => $data['oid'],
            'amount' => $data['amount'],
            'url' => $apiUrl,
        ], 'info', 'payment_logs');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'data=' . $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $result = curl_exec($ch);
        curl_close($ch);

        $response = self::convertToUtf8((string) $result);
        logSession("EstPosClient {$type} yaniti.", ['response' => $response], 'info', 'payment_logs');

        $xmlResponse = @simplexml_load_string($response);

        if ($xmlResponse && isset($xmlResponse->Response) && (string) $xmlResponse->Response === 'Approved') {
            return [
                'success' => true,
                'message' => $type === 'Void' ? 'Iptal islemi basarili.' : 'Iade islemi basarili.',
            ];
        }

        $errorMsg = '';
        if ($xmlResponse && isset($xmlResponse->ErrMsg)) {
            $errorMsg = (string) $xmlResponse->ErrMsg;
        }

        return [
            'success' => false,
            'message' => ($type === 'Void' ? 'Iptal' : 'Iade') . ' islemi basarisiz.' . ($errorMsg ? ' Hata: ' . $errorMsg : ''),
        ];
    }

    private static function resolveApiUrl(string $url): string
    {
        if ($url === '') {
            return '';
        }

        $apiUrl = str_replace('/servlet/gt3dengine', '/servlet/cc5ApiServer', $url);

        if (strpos($apiUrl, 'cc5ApiServer') === false) {
            $apiUrl = (string) preg_replace('#/[^/]*$#', '/cc5ApiServer', $apiUrl);
        }

        return $apiUrl;
    }

    private static function resolveStatusApiUrl(?string $bankCode, string $url): string
    {
        $normalizedBankCode = strtolower(trim((string) $bankCode));
        $byBankCode = [
            'isbank' => 'https://sanalpos.isbank.com.tr/fim/api',
            'teb' => 'http://sanalpos.teb.com.tr/fim/api',
            'halkbank' => 'https://sanalpos.halkbank.com.tr/fim/api',
            'ziraat' => 'https://sanalpos2.ziraatbank.com.tr/fim/api',
        ];

        if ($normalizedBankCode !== '' && isset($byBankCode[$normalizedBankCode])) {
            return $byBankCode[$normalizedBankCode];
        }

        if ($normalizedBankCode !== '') {
            return '';
        }

        if ($url === '') {
            return '';
        }

        $trimmed = rtrim($url, '/');
        if (str_contains($trimmed, '/fim/api')) {
            return $trimmed;
        }

        if (str_contains($trimmed, '/servlet/')) {
            return preg_replace('#/servlet/.*$#', '/fim/api', $trimmed) ?: $trimmed;
        }

        return $trimmed . '/fim/api';
    }

    private static function convertToUtf8(string $value): string
    {
        if (!mb_check_encoding($value, 'UTF-8')) {
            return mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
        }

        return $value;
    }

    private static function mapEstQueryStatus(\SimpleXMLElement $xmlResponse, array $extraData): string
    {
        $response = strtolower(trim((string) ($xmlResponse->Response ?? '')));
        $procReturnCode = strtoupper(trim(self::firstNonEmptyString([
            self::getExtraValue($extraData, ['PROC_RET_CD', 'PROC_RET_CODE']),
            (string) ($xmlResponse->ProcReturnCode ?? ''),
        ]) ?? ''));
        $transStat = strtoupper(trim(self::firstNonEmptyString([
            self::getExtraValue($extraData, ['TRANS_STAT', 'TRANS_STATUS']),
            (string) ($xmlResponse->TransStat ?? ''),
        ]) ?? ''));
        $errorMessage = strtolower(trim((string) self::resolveEstQueryMessage($xmlResponse, $extraData)));
        $combined = trim($response . ' ' . $transStat . ' ' . $procReturnCode . ' ' . $errorMessage);

        if (self::containsNotFoundHint($combined)) {
            return 'unknown';
        }

        if (self::containsAuthConfigHint($combined)) {
            return 'unknown';
        }

        if (in_array($transStat, ['S', 'C', 'A'], true) && $procReturnCode === '00') {
            return 'success';
        }

        if ($transStat === 'PN') {
            return 'pending';
        }

        if (in_array($transStat, ['D', 'ERR', 'CNCL', 'V'], true)) {
            return 'failed';
        }

        if ($response === 'declined' && !self::containsAuthConfigHint($combined)) {
            return 'failed';
        }

        if (self::containsDefinitiveFailureHint($combined) && !self::containsAuthConfigHint($combined)) {
            return 'failed';
        }

        if (in_array($response, ['pending', 'inprogress', 'processing'], true)) {
            return 'pending';
        }

        if (str_contains($combined, 'pending') || str_contains($combined, 'bekle')) {
            return 'pending';
        }

        return 'unknown';
    }

    private static function resolveEstQueryMessage(\SimpleXMLElement $xmlResponse, array $extraData = []): ?string
    {
        $values = [
            self::getExtraValue($extraData, ['ERR_MSG', 'ERROR_MESSAGE', 'PROC_RET_MSG']),
            (string) ($xmlResponse->ErrMsg ?? ''),
            (string) ($xmlResponse->ErrorMessage ?? ''),
            (string) ($xmlResponse->Response ?? ''),
            self::getExtraValue($extraData, ['TRANS_STAT']),
            self::getExtraValue($extraData, ['PROC_RET_CD']),
        ];

        foreach ($values as $value) {
            $trimmed = trim($value);
            if ($trimmed !== '') {
                return $trimmed;
            }
        }

        return null;
    }

    private static function containsNotFoundHint(string $value): bool
    {
        $value = strtolower($value);

        return str_contains($value, 'kayit bulunamadi')
            || str_contains($value, 'kayıt bulunamadı')
            || str_contains($value, 'islem bulunamadi')
            || str_contains($value, 'işlem bulunamadı')
            || str_contains($value, 'order not found')
            || str_contains($value, 'not found')
            || str_contains($value, 'no transaction')
            || str_contains($value, 'record not found');
    }

    private static function containsDefinitiveFailureHint(string $value): bool
    {
        $value = strtolower($value);

        return str_contains($value, 'declin')
            || str_contains($value, 'reject')
            || str_contains($value, 'fail')
            || str_contains($value, 'hata')
            || str_contains($value, 'error')
            || str_contains($value, 'iptal')
            || str_contains($value, 'cancel');
    }

    private static function containsAuthConfigHint(string $value): bool
    {
        $value = strtolower($value);

        return str_contains($value, 'sifre')
            || str_contains($value, 'şifre')
            || str_contains($value, 'password')
            || str_contains($value, 'kullanici')
            || str_contains($value, 'kullanıcı')
            || str_contains($value, 'auth')
            || str_contains($value, 'yetki')
            || str_contains($value, 'ip')
            || str_contains($value, 'endpoint')
            || str_contains($value, 'forbidden')
            || str_contains($value, 'unauthorized');
    }

    private static function xmlToArray(\SimpleXMLElement $xml): array
    {
        $json = json_encode($xml, JSON_UNESCAPED_UNICODE);
        $decoded = json_decode((string) $json, true);

        return is_array($decoded) ? $decoded : [];
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

    private static function extractExtraData(\SimpleXMLElement $xmlResponse): array
    {
        $extraData = [];
        if (!isset($xmlResponse->Extra)) {
            return $extraData;
        }

        $extraNode = $xmlResponse->Extra;
        foreach ($extraNode->children() as $child) {
            $key = strtoupper(trim((string) $child->getName()));
            if ($key === '') {
                continue;
            }

            $extraData[$key] = trim((string) $child);
        }

        $segments = [];
        $segments[] = trim((string) $extraNode);
        foreach ($extraNode->children() as $child) {
            $segments[] = trim((string) $child);
        }

        foreach ($segments as $segment) {
            if ($segment === '') {
                continue;
            }

            $parts = preg_split('/[;\r\n|,]+/', $segment) ?: [];
            foreach ($parts as $part) {
                $part = trim((string) $part);
                if ($part === '') {
                    continue;
                }

                if (!preg_match('/^([A-Za-z0-9_]+)\s*[:=]\s*(.+)$/', $part, $matches)) {
                    continue;
                }

                $key = strtoupper(trim((string) ($matches[1] ?? '')));
                $value = trim((string) ($matches[2] ?? ''));
                if ($key === '' || $value === '') {
                    continue;
                }

                $extraData[$key] = $value;
            }
        }

        return $extraData;
    }

    private static function getExtraValue(array $extraData, array $keys): ?string
    {
        foreach ($keys as $key) {
            $normalized = strtoupper(trim($key));
            if (!array_key_exists($normalized, $extraData)) {
                continue;
            }

            $value = trim((string) $extraData[$normalized]);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    public function generatePaymentForm()
    {
        $postData = [
            'clientid' => $this->workplaceCode,
            'amount' => $this->amount,
            'oid' => $this->oid,
            'okUrl' => $this->okUrl,
            'failUrl' => $this->failUrl,
            'rnd' => $this->rnd,
            'hash' => $this->generateHash(),
            'hashAlgorithm' => 'ver3',
            'islemtipi' => $this->transactionType,
            'taksit' => $this->installment,
            'storetype' => '3d_pay',
            'lang' => 'tr',
            'currency' => '949',
            'creditcard_name' => $this->credit_card_name,
            'pan' => $this->credit_card_number,
            'Ecom_Payment_Card_ExpDate_Month' => $this->credit_card_exp_date_month,
            'Ecom_Payment_Card_ExpDate_Year' => $this->credit_card_exp_date_year,
            'cv2' => $this->cvc,
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $result = false;

        if (curl_errno($ch)) {
            logSession('EstPosClient odeme formu olusturma basarisiz. cURL Error', ['message' => curl_error($ch)], 'info', 'payment_logs');
        } elseif ($httpCode !== 200) {
            logSession('EstPosClient HTTP hatasi.', ['http_code' => $httpCode], 'error', 'payment_logs');
        } else {
            $result = true;
            logSession('EstPosClient odeme formu basariyla olusturuldu.', ['http_code' => $httpCode], 'info', 'payment_logs');
        }

        curl_close($ch);

        return [
            'result' => $result,
            'response' => $response,
        ];
    }
}
