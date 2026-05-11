<?php

namespace App\Infrastructure\Payment\Clients;

use Carbon\Carbon;

class AkbankPosClient
{
    private const API_VPOS_URL = 'https://virtualpospaymentgateway.akbank.com/api/v1/payment/virtualpos';
    private const API_STATUS_URL = 'https://api.akbank.com/api/v1/payment/virtualpos/transaction/process';

    protected $data, $requestDateTime, $randomNumber, $amount, $merchantSafeId, $terminalSafeId, $subMerchantId, $secretKey;

    public function __construct($data)
    {
        logSession("AkbankPosClient ödeme işlemi için form başlatıldı.", null, 'info', 'payment_logs');

        $this->data = $data;
        $this->requestDateTime = Carbon::now()->format('Y-m-d\TH:i:s.v');
        $this->randomNumber = $this->getRandomNumberBase16();
        $this->amount = number_format($this->data['amount'], 2, '.', '');
        $this->initializeIntegrationInfo();
    }

    protected function initializeIntegrationInfo()
    {
        $integrationInfo = $this->data['bank_integration_information'];
        $this->merchantSafeId = $integrationInfo->merchantSafeId;
        $this->terminalSafeId = $integrationInfo->terminalSafeId;
        $this->subMerchantId = $integrationInfo->subMerchantId;
        $this->secretKey = $integrationInfo->secretKey;
    }

    public function generateHash()
    {
        $params = [
            'paymentModel' => '3D_PAY',
            'txnCode' => '3000',
            'merchantSafeId' => $this->merchantSafeId,
            'terminalSafeId' => $this->terminalSafeId,
            'orderId' => $this->data['oid'],
            'lang' => 'TR',
            'amount' => $this->amount,
            'currencyCode' => '949',
            'installCount' => $this->data['installment'],
            'okUrl' => $this->data['ok_url'],
            'failUrl' => $this->data['fail_url'],
            'creditCard' => $this->data['credit_card_number'],
            'expiredDate' => $this->data['credit_card_exp_date_month'] . $this->data['credit_card_exp_date_year'],
            'cvv' => $this->data['cvc'],
            'randomNumber' => $this->randomNumber,
            'requestDateTime' => $this->requestDateTime
        ];

        $hashData = implode('', array_values($params));

        return base64_encode(hash_hmac('sha512', $hashData, $this->secretKey, true));
    }

    public function getRandomNumberBase16($length = 128)
    {
        return strtoupper(bin2hex(random_bytes($length / 2)));
    }

    /**
     * İptal (Void) işlemi
     */
    public static function cancel(array $data): array
    {
        return self::processRefundRequest($data, '1003');
    }

    /**
     * İade (Refund) işlemi
     */
    public static function refund(array $data): array
    {
        return self::processRefundRequest($data, '1004');
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

        $orderId = trim((string) ($data['oid'] ?? ''));
        if ($orderId === '') {
            return [
                'success' => false,
                'status' => 'unknown',
                'message' => 'akbank_status_query_missing_order_id',
                'raw_payload' => [],
            ];
        }

        $requestDateTime = \Carbon\Carbon::now()->format('Y-m-d\TH:i:s.v');
        $randomNumber = strtoupper(bin2hex(random_bytes(64)));
        $merchantSafeId = (string) ($info->merchantSafeId ?? '');
        $terminalSafeId = (string) ($info->terminalSafeId ?? '');
        $secretKey = (string) ($info->secretKey ?? '');

        if ($merchantSafeId === '' || $terminalSafeId === '' || $secretKey === '') {
            return [
                'success' => false,
                'status' => 'unknown',
                'message' => 'akbank_status_query_invalid_configuration',
                'raw_payload' => [],
            ];
        }

        $postData = [
            'version' => '1.00',
            'txnCode' => '1010',
            'requestDateTime' => $requestDateTime,
            'randomNumber' => $randomNumber,
            'terminal' => [
                'merchantSafeId' => $merchantSafeId,
                'terminalSafeId' => $terminalSafeId,
            ],
            'order' => [
                'orderId' => $orderId,
            ],
        ];
        $jsonPayload = json_encode($postData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($jsonPayload)) {
            return [
                'success' => false,
                'status' => 'unknown',
                'message' => 'akbank_status_query_payload_encode_error',
                'raw_payload' => [],
            ];
        }
        $authHash = base64_encode(hash_hmac('sha512', $jsonPayload, $secretKey, true));

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => self::API_STATUS_URL,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonPayload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'auth-hash: ' . $authHash,
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);

            return [
                'success' => false,
                'status' => 'unknown',
                'message' => 'akbank_status_query_curl_error: ' . $error,
                'raw_payload' => [],
            ];
        }

        curl_close($ch);
        $decoded = json_decode((string) $response, true);
        if ($httpCode === 401) {
            return [
                'success' => true,
                'status' => 'unknown',
                'message' => 'akbank_status_query_auth_hash_unauthorized',
                'provider_reference' => null,
                'auth_code' => null,
                'rrn' => null,
                'raw_payload' => is_array($decoded) ? $decoded : ['response' => $response],
            ];
        }

        if ($httpCode === 400) {
            return [
                'success' => true,
                'status' => 'unknown',
                'message' => 'akbank_status_query_bad_request_payload_format',
                'provider_reference' => null,
                'auth_code' => null,
                'rrn' => null,
                'raw_payload' => is_array($decoded) ? $decoded : ['response' => $response],
            ];
        }

        if ($httpCode === 404) {
            return [
                'success' => true,
                'status' => 'unknown',
                'message' => 'akbank_status_query_endpoint_not_found',
                'provider_reference' => null,
                'auth_code' => null,
                'rrn' => null,
                'raw_payload' => is_array($decoded) ? $decoded : ['response' => $response],
            ];
        }

        if (!is_array($decoded)) {
            return [
                'success' => false,
                'status' => 'unknown',
                'message' => 'akbank_status_query_invalid_response',
                'raw_payload' => ['response' => $response],
            ];
        }

        $txnList = $decoded['txnDetailList'] ?? $decoded['txnDetails'] ?? [];
        if (!is_array($txnList)) {
            $txnList = [];
        }

        $matched = null;
        foreach ($txnList as $item) {
            if (!is_array($item)) {
                continue;
            }

            $itemOrderId = (string) ($item['orderId'] ?? $item['orgOrderId'] ?? '');
            if ($itemOrderId !== '' && $itemOrderId === $orderId) {
                $matched = $item;
                break;
            }
        }

        if ($matched === null && count($txnList) === 1 && is_array($txnList[0])) {
            $matched = $txnList[0];
        }

        $responseCode = (string) ($decoded['responseCode'] ?? '');
        $responseMessage = (string) ($decoded['responseMessage'] ?? $decoded['hostMessage'] ?? '');

        if ($matched === null) {
            return [
                'success' => true,
                'status' => 'unknown',
                'message' => $responseMessage !== '' ? $responseMessage : 'akbank_status_query_not_found',
                'provider_reference' => null,
                'auth_code' => null,
                'rrn' => null,
                'raw_payload' => $decoded,
            ];
        }

        $txnStatus = strtoupper((string) ($matched['txnStatus'] ?? ''));
        $txnCode = (string) ($matched['txnCode'] ?? '');
        $detailResponseCode = (string) ($matched['responseCode'] ?? '');
        $detailResponseMessage = (string) ($matched['responseMessage'] ?? $matched['statusDescription'] ?? '');
        $effectiveResponseCode = $detailResponseCode !== '' ? $detailResponseCode : $responseCode;

        $isSecureTxn = in_array($txnCode, ['3000', '3002', '3003', '3004'], true);
        $isSuccess = $txnStatus === 'N' && $effectiveResponseCode === 'VPS-0000';
        $isPending = $isSecureTxn && $detailResponseCode === 'VPS-0000';
        $isFailed = ($isSecureTxn && $detailResponseCode !== '' && $detailResponseCode !== 'VPS-0000')
            || $txnStatus === 'V'
            || self::containsFailureText($detailResponseMessage)
            || self::containsFailureText($responseMessage);

        $status = 'unknown';
        if ($isSuccess) {
            $status = 'success';
        } elseif ($isFailed) {
            $status = 'failed';
        } elseif ($isPending || $txnStatus === 'P') {
            $status = 'pending';
        }

        return [
            'success' => true,
            'status' => $status,
            'message' => $detailResponseMessage !== '' ? $detailResponseMessage : ($responseMessage !== '' ? $responseMessage : ((string) ($matched['statusDescription'] ?? null))),
            'provider_reference' => self::firstNonEmptyString([
                (string) ($matched['hostRefNum'] ?? ''),
                (string) ($matched['transactionId'] ?? ''),
                (string) ($matched['transId'] ?? ''),
            ]),
            'auth_code' => self::firstNonEmptyString([
                (string) ($matched['authCode'] ?? ''),
            ]),
            'rrn' => self::firstNonEmptyString([
                (string) ($matched['rrn'] ?? ''),
                (string) ($matched['hostRefNum'] ?? ''),
            ]),
            'raw_payload' => $decoded,
        ];
    }

    /**
     * Akbank POS iptal/iade REST isteği
     */
    private static function processRefundRequest(array $data, string $txnCode): array
    {
        $info = $data['bank_integration_information'];
        $requestDateTime = \Carbon\Carbon::now()->format('Y-m-d\TH:i:s.v');
        $randomNumber = strtoupper(bin2hex(random_bytes(64)));
        $amount = number_format($data['amount'], 2, '.', '');

        $hashParams = [
            'paymentModel' => 'NON_SECURE',
            'txnCode' => $txnCode,
            'merchantSafeId' => $info->merchantSafeId,
            'terminalSafeId' => $info->terminalSafeId,
            'orderId' => $data['oid'],
            'lang' => 'TR',
            'amount' => $amount,
            'currencyCode' => '949',
            'randomNumber' => $randomNumber,
            'requestDateTime' => $requestDateTime,
        ];

        $hashData = implode('', array_values($hashParams));
        $hash = base64_encode(hash_hmac('sha512', $hashData, $info->secretKey, true));

        $postData = array_merge($hashParams, ['hash' => $hash]);

        $typeName = $txnCode === '1003' ? 'İptal' : 'İade';

        logSession("AkbankPosClient {$typeName} isteği gönderiliyor.", [
            'oid' => $data['oid'],
            'amount' => $amount,
            'txnCode' => $txnCode,
        ], 'info', 'payment_logs');

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => self::API_VPOS_URL,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        logSession("AkbankPosClient {$typeName} yanıtı.", ['response' => $response], 'info', 'payment_logs');

        $result = json_decode($response, true);

        if ($result && isset($result['responseCode']) && $result['responseCode'] === 'VPS-0000') {
            return [
                'success' => true,
                'message' => "{$typeName} işlemi başarılı.",
            ];
        }

        $errorMsg = $result['hostMessage'] ?? ($result['responseMessage'] ?? '');

        return [
            'success' => false,
            'message' => "{$typeName} işlemi başarısız." . ($errorMsg ? " Hata: {$errorMsg}" : ''),
        ];
    }

    public function generatePaymentForm()
    {
        $postData = [
            'paymentModel' => "3D_PAY",
            'txnCode' => "3000",
            'merchantSafeId' => $this->merchantSafeId,
            'terminalSafeId' => $this->terminalSafeId,
            'orderId' => $this->data['oid'],
            'lang' => "TR",
            'amount' => $this->amount,
            'currencyCode' => "949",
            'installCount' => $this->data['installment'],
            'okUrl' => $this->data['ok_url'],
            'failUrl' => $this->data['fail_url'],
            'creditCard' => $this->data['credit_card_number'],
            'expiredDate' => $this->data['credit_card_exp_date_month'] . $this->data['credit_card_exp_date_year'],
            'cvv' => $this->data['cvc'],
            'randomNumber' => $this->randomNumber,
            'requestDateTime' => $this->requestDateTime,
            'hash' => $this->generateHash()
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://virtualpospaymentgateway.akbank.com/securepay',
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
            logSession("AkbankPosClient ödeme formu oluşturma başarısız. cURL Error", ['message' => curl_error($ch)], 'info', 'payment_logs');
        } else if ($httpCode !== 200) {
            logSession("AkbankPosClient HTTP hatası.", ['http_code' => $httpCode], 'error', 'payment_logs');
        } else {
            $result = true;
            logSession("AkbankPosClient ödeme formu başarıyla oluşturuldu.", ['http_code' => $httpCode], 'info', 'payment_logs');
        }

        curl_close($ch);

        return [
            'result' => $result,
            'response' => $response
        ];
    }

    private static function containsFailureText(string $value): bool
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        if ($value === '') {
            return false;
        }

        return str_contains($value, 'declin')
            || str_contains($value, 'fail')
            || str_contains($value, 'error')
            || str_contains($value, 'başarısız')
            || str_contains($value, 'hata')
            || str_contains($value, 'red')
            || str_contains($value, 'reject')
            || str_contains($value, 'cancel');
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
