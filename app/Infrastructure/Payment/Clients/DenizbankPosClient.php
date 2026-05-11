<?php

namespace App\Infrastructure\Payment\Clients;

class DenizbankPosClient
{
    protected $data;
    protected $shopCode;
    protected $storeKey;
    protected $transactionType;
    protected $rnd;
    protected $installment;

    public function __construct($data)
    {
        logSession("DenizbankPosClient ödeme işlemi için form başlatıldı.", null, 'info', 'payment_logs');

        $this->data = $data;
        $this->rnd = uniqid();
        $this->installment = $this->data['installment'] == 1 ? '' : $this->data['installment'];
        $this->initializeIntegrationInfo();
    }

    protected function initializeIntegrationInfo()
    {
        $integrationInfo = $this->data['bank_integration_information'];
        $this->shopCode = $integrationInfo->ShopCode;
        $this->storeKey = $integrationInfo->StoreKey;
        $this->transactionType = 'Auth';
    }

    public function generateHash()
    {
        $hashString = $this->shopCode . $this->data['oid'] . $this->data['amount'] . $this->data['ok_url'] . $this->data['fail_url'] . $this->transactionType . $this->installment . $this->rnd . $this->storeKey;
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
                'message' => 'denizbank_status_query_missing_order_id',
                'raw_payload' => [],
            ];
        }

        $shopCode = self::resolveInfoValue($info, ['ShopCode', 'shop_code', 'client_id', 'merchant_id']);
        $userCode = self::resolveInfoValue($info, ['UserCode', 'user_code', 'username', 'user']);
        $userPass = self::resolveInfoValue($info, ['UserPass', 'user_pass', 'password']);
        if ($shopCode === '' || $userCode === '' || $userPass === '') {
            return [
                'success' => true,
                'status' => 'unknown',
                'message' => 'denizbank_status_query_invalid_configuration',
                'provider_reference' => null,
                'auth_code' => null,
                'rrn' => null,
                'raw_payload' => [],
            ];
        }

        $postData = [
            'SecureType' => 'NonSecure',
            'TxnType' => 'StatusHistory',
            'OrderId' => $oid,
            'OrgOrderId' => $oid,
            'ShopCode' => $shopCode,
            'UserCode' => $userCode,
            'UserPass' => $userPass,
            'Lang' => 'TR',
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://inter-vpos.com.tr/mpi/Default.aspx',
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
                'message' => 'denizbank_status_query_curl_error: ' . $error,
                'raw_payload' => [],
            ];
        }

        curl_close($ch);
        $raw = trim((string) $response);
        if ($raw === '') {
            return [
                'success' => true,
                'status' => 'unknown',
                'message' => 'denizbank_status_query_empty_response',
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
                'message' => 'denizbank_status_query_unparseable_response',
                'provider_reference' => null,
                'auth_code' => null,
                'rrn' => null,
                'raw_payload' => ['response' => $response],
            ];
        }

        $message = (string) ($parsed['ErrMsg'] ?? $parsed['ErrorMessage'] ?? $parsed['ResponseMessage'] ?? '');
        $procReturnCode = (string) ($parsed['ProcReturnCode'] ?? '');
        $errorCode = (string) ($parsed['ErrorCode'] ?? '');
        $responseValue = strtolower((string) ($parsed['Response'] ?? ''));

        $combined = strtolower(trim($message . ' ' . $responseValue));
        $status = 'unknown';
        if ($procReturnCode === '00' || $responseValue === 'approved') {
            $status = 'success';
        } elseif (self::isConfigOrAuthError($procReturnCode, $errorCode, $message)) {
            $status = 'unknown';
            $message = 'denizbank_status_query_invalid_configuration';
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
     * Denizbank NestPay iptal/iade XML isteği
     */
    private static function processRefundRequest(array $data, string $type): array
    {
        $info = $data['bank_integration_information'];

        $postData = [
            'ShopCode' => $info->ShopCode,
            'SecureType' => 'NonSecure',
            'TxnType' => $type,
            'OrderId' => $data['oid'],
            'Currency' => '949',
            'Lang' => 'tr',
        ];

        if ($type === 'Refund') {
            $postData['PurchAmount'] = $data['amount'];
        }

        $typeName = $type === 'Void' ? 'İptal' : 'İade';

        logSession("DenizbankPosClient {$typeName} isteği gönderiliyor.", [
            'oid' => $data['oid'],
            'amount' => $data['amount'],
        ], 'info', 'payment_logs');

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://inter-vpos.com.tr/mpi/Default.aspx',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        logSession("DenizbankPosClient {$typeName} yanıtı.", ['response' => $response], 'info', 'payment_logs');

        $xmlResponse = @simplexml_load_string($response);

        if ($xmlResponse) {
            $procReturnCode = (string)($xmlResponse->ProcReturnCode ?? '');

            if ($procReturnCode === '00') {
                return [
                    'success' => true,
                    'message' => "{$typeName} işlemi başarılı.",
                ];
            }

            $errorMsg = (string)($xmlResponse->ErrMsg ?? $xmlResponse->ErrorMessage ?? '');

            return [
                'success' => false,
                'message' => "{$typeName} işlemi başarısız." . ($errorMsg ? " Hata: {$errorMsg}" : ''),
            ];
        }

        return [
            'success' => false,
            'message' => "{$typeName} işlemi sırasında bankadan yanıt alınamadı.",
        ];
    }

    public function generatePaymentForm()
    {
        $postData = [
            'CardHolderName' => $this->data['credit_card_name'],
            'Pan' => $this->data['credit_card_number'],
            'Expiry' => $this->data['credit_card_exp_date_month'] . $this->data['credit_card_exp_date_year'],
            'Cvv2' => $this->data['cvc'],
            'Version3D' => "2.0",
            'ShopCode' => $this->shopCode,
            'SecureType' => "3DPay",
            'TxnType' => $this->transactionType,
            'InstallmentCount' => $this->installment,
            'Currency' => "949",
            'OkUrl' => $this->data['ok_url'],
            'FailUrl' => $this->data['fail_url'],
            'OrderId' => $this->data['oid'],
            'PurchAmount' => $this->data['amount'],
            'Lang' => "tr",
            'Rnd' => $this->rnd,
            'Hash' => $this->generateHash()
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://inter-vpos.com.tr/mpi/Default.aspx',
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
            logSession("DenizbankPosClient ödeme formu oluşturma başarısız. cURL Error", ['message' => curl_error($ch)], 'info', 'payment_logs');
        } else if ($httpCode !== 200) {
            logSession("DenizbankPosClient HTTP hatası.", ['http_code' => $httpCode], 'error', 'payment_logs');
        } else {
            $result = true;
            logSession("DenizbankPosClient ödeme formu başarıyla oluşturuldu.", ['http_code' => $httpCode], 'info', 'payment_logs');
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

    private static function resolveInfoValue(object $info, array $keys): string
    {
        foreach ($keys as $key) {
            if (!isset($info->{$key})) {
                continue;
            }

            $value = trim((string) $info->{$key});
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private static function isConfigOrAuthError(string $procReturnCode, string $errorCode, string $message): bool
    {
        $value = strtolower(trim($message));

        if ($procReturnCode === '96' && $errorCode === '040') {
            return true;
        }

        return str_contains($value, 'kullanici kodu bos olamaz')
            || str_contains($value, 'kullanıcı kodu boş olamaz')
            || str_contains($value, 'kullanici')
            || str_contains($value, 'sifre')
            || str_contains($value, 'şifre')
            || str_contains($value, 'password');
    }
}
