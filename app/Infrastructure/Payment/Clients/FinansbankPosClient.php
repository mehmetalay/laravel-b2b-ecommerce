<?php

namespace App\Infrastructure\Payment\Clients;

class FinansbankPosClient
{
    protected $data;
    protected $merchantId;
    protected $userCode;
    protected $userPass;
    protected $storeKey;
    protected $transactionType;
    protected $rnd;
    protected $installment;

    public function __construct($data)
    {
        logSession("FinansbankPosClient ödeme işlemi için form başlatıldı.", null, 'info', 'payment_logs');

        $this->data = $data;

        $installment = $this->data['installment'] ?? 1;

        $this->rnd = uniqid();
        $this->installment = $installment == 1 ? 0 : $installment;
        $this->initializeIntegrationInfo();
    }

    protected function initializeIntegrationInfo()
    {
        $integrationInfo = $this->data['bank_integration_information'];
        $this->merchantId = $integrationInfo->MerchantID;
        $this->userCode = $integrationInfo->UserCode;
        $this->userPass = $integrationInfo->UserPass;
        $this->storeKey = $integrationInfo->StoreKey;
        $this->transactionType = 'Auth';
    }

    public function generateHash()
    {
        $hashString = '5' . $this->data['oid'] . $this->data['amount'] . $this->data['ok_url'] . $this->data['fail_url'] . $this->transactionType . $this->installment . $this->rnd . $this->storeKey;
        return base64_encode(pack('H*', sha1($hashString)));
    }

    /**
     * İptal (Void) işlemi
     */
    public static function cancel(array $data): array
    {
        return self::processRefundRequest($data, 'Void');
    }

    /**
     * İade (Refund) işlemi
     */
    public static function refund(array $data): array
    {
        return self::processRefundRequest($data, 'Refund');
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
                'message' => 'finansbank_status_query_missing_order_id',
                'raw_payload' => [],
            ];
        }

        $postData = [
            'MbrId' => '5',
            'MerchantID' => $info->MerchantID ?? null,
            'UserCode' => $info->UserCode ?? null,
            'UserPass' => $info->UserPass ?? null,
            'SecureType' => 'Inquiry',
            'TxnType' => 'OrderInquiry',
            'OrgOrderId' => $oid,
            'Currency' => '949',
            'Lang' => 'TR',
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://vpos.qnbfinansbank.com/Gateway/Default.aspx',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);

            return [
                'success' => false,
                'status' => 'unknown',
                'message' => 'finansbank_status_query_curl_error: ' . $error,
                'raw_payload' => [],
            ];
        }

        curl_close($ch);
        $raw = trim((string) $response);
        if ($raw === '') {
            return [
                'success' => true,
                'status' => 'unknown',
                'message' => 'finansbank_status_query_empty_response',
                'provider_reference' => null,
                'auth_code' => null,
                'rrn' => null,
                'raw_payload' => ['response' => $response],
            ];
        }

        $parsed = self::parseKeyValueResponse($raw);
        if (empty($parsed)) {
            return [
                'success' => true,
                'status' => 'unknown',
                'message' => 'finansbank_status_query_unparseable_response',
                'provider_reference' => null,
                'auth_code' => null,
                'rrn' => null,
                'raw_payload' => ['response' => $response],
            ];
        }

        $procReturnCode = (string) ($parsed['ProcReturnCode'] ?? '');
        $responseValue = strtolower((string) ($parsed['Response'] ?? ''));
        $isVoided = in_array(strtolower((string) ($parsed['IsVoided'] ?? '')), ['true', '1', 'yes'], true);
        $isRefunded = in_array(strtolower((string) ($parsed['IsRefunded'] ?? '')), ['true', '1', 'yes'], true);
        $message = (string) ($parsed['ErrMsg'] ?? $parsed['ErrorMessage'] ?? $parsed['ResponseMessage'] ?? '');
        $combined = strtolower(trim($message . ' ' . $responseValue));

        $status = 'unknown';
        $isApproved = $procReturnCode === '00' || $responseValue === 'approved';

        if ($isApproved && !$isVoided && !$isRefunded) {
            $status = 'success';
        } elseif ($isVoided) {
            $status = 'failed';
        } elseif ($isRefunded) {
            $status = 'unknown';
        } elseif (self::containsFailureText($combined)) {
            $status = 'failed';
        } elseif (self::containsNotFoundText($combined)) {
            $status = 'unknown';
        }

        return [
            'success' => true,
            'status' => $status,
            'message' => $message !== '' ? $message : ($parsed['Response'] ?? null),
            'provider_reference' => self::firstNonEmptyString([
                (string) ($parsed['TransId'] ?? ''),
                (string) ($parsed['TransactionId'] ?? ''),
                (string) ($parsed['HostRefNum'] ?? ''),
            ]),
            'auth_code' => self::firstNonEmptyString([
                (string) ($parsed['AuthCode'] ?? ''),
            ]),
            'rrn' => self::firstNonEmptyString([
                (string) ($parsed['HostRefNum'] ?? ''),
                (string) ($parsed['Rrn'] ?? ''),
            ]),
            'raw_payload' => $parsed,
        ];
    }

    /**
     * Finansbank NestPay iptal/iade XML isteği
     */
    private static function processRefundRequest(array $data, string $type): array
    {
        $info = $data['bank_integration_information'];

        $postData = [
            'MbrId' => '5',
            'MerchantID' => $info->MerchantID,
            'UserCode' => $info->UserCode,
            'UserPass' => $info->UserPass,
            'OrgOrderId' => $data['oid'],
            'SecureType' => 'NonSecure',
            'TxnType' => $type,
            'Currency' => '949',
            'Lang' => 'TR',
        ];

        if ($type === 'Refund') {
            $postData['PurchAmount'] = number_format((float)$data['amount'], 2, '.', '');
        }

        $typeName = $type === 'Void' ? 'İptal' : 'İade';

        logSession("FinansbankPosClient {$typeName} isteği gönderiliyor.", [
            'oid' => $data['oid'],
            'amount' => $data['amount'],
            'post_data' => $postData,
        ], 'info', 'payment_logs');

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://vpos.qnbfinansbank.com/Gateway/Default.aspx',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);

            return [
                'success' => false,
                'message' => "{$typeName} işlemi sırasında cURL hatası: {$error}",
            ];
        }
        
        curl_close($ch);

        logSession("FinansbankPosClient {$typeName} yanıtı.", ['response' => $response], 'info', 'payment_logs');

        $responseData = [];
        $resultValues = explode(';;', $response);

        foreach ($resultValues as $item) {
            if (strpos($item, '=') !== false) {
                [$key, $value] = explode('=', $item, 2);
                $responseData[trim($key)] = trim($value);
            }
        }

        if (($responseData['ProcReturnCode'] ?? '') === '00') {
            return [
                'success' => true,
                'message' => "{$typeName} işlemi başarılı.",
            ];
        }

        return [
            'success' => false,
            'message' => "{$typeName} işlemi başarısız." . (!empty($responseData['ErrMsg']) ? ' Hata: ' . $responseData['ErrMsg'] : ''),
        ];
    }

    public function generatePaymentForm()
    {
        $postData = [
            "CardHolderName" => $this->data['credit_card_name'],
            "Pan" => $this->data['credit_card_number'],
            "Expiry" => $this->data['credit_card_exp_date_month'] . $this->data['credit_card_exp_date_year'],
            "Cvv2" => $this->data['cvc'],
            "MbrId" => "5",
            "MerchantID" => $this->merchantId,
            "UserCode" => $this->userCode,
            "UserPass" => $this->userPass,
            "SecureType" => "3DPay",
            "TxnType" => $this->transactionType,
            "InstallmentCount" => $this->installment,
            "Currency" => "949",
            "OkUrl" => $this->data['ok_url'],
            "FailUrl" => $this->data['fail_url'],
            "OrderId" => $this->data['oid'],
            "PurchAmount" => $this->data['amount'],
            "Lang" => "TR",
            "Rnd" => $this->rnd,
            "Hash" => $this->generateHash()
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => "https://vpos.qnbfinansbank.com/Gateway/Default.aspx",
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
            logSession("FinansbankPosClient ödeme formu oluşturma başarısız. cURL Error", ['message' => curl_error($ch)], 'info', 'payment_logs');
        } else if ($httpCode !== 200) {
            logSession("FinansbankPosClient HTTP hatası.", ['http_code' => $httpCode], 'error', 'payment_logs');
        } else {
            $result = true;
            logSession("FinansbankPosClient ödeme formu başarıyla oluşturuldu.", ['http_code' => $httpCode], 'info', 'payment_logs');
        }

        curl_close($ch);

        return [
            'result' => $result,
            'response' => $response
        ];
    }

    private static function parseKeyValueResponse(string $raw): array
    {
        $result = [];
        $parts = explode(';;', $raw);
        foreach ($parts as $part) {
            if (!str_contains($part, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $part, 2);
            $key = trim((string) $key);
            if ($key === '') {
                continue;
            }

            $result[$key] = trim((string) $value);
        }

        return $result;
    }

    private static function containsFailureText(string $value): bool
    {
        return str_contains($value, 'declin')
            || str_contains($value, 'error')
            || str_contains($value, 'fail')
            || str_contains($value, 'reject')
            || str_contains($value, 'hata');
    }

    private static function containsNotFoundText(string $value): bool
    {
        return str_contains($value, 'not found')
            || str_contains($value, 'kayit bulunamadi')
            || str_contains($value, 'kayıt bulunamadı')
            || str_contains($value, 'islem bulunamadi')
            || str_contains($value, 'işlem bulunamadı');
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
}

