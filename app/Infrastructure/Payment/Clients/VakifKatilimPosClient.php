<?php

namespace App\Infrastructure\Payment\Clients;

use SimpleXMLElement;
use Spatie\ArrayToXml\ArrayToXml;
use Illuminate\Support\Facades\Http;

class VakifKatilimPosClient
{
    protected $data;
    protected $merchantId;
    protected $customerId;
    protected $userName;
    protected $hashPassword;
    protected $amount;

    public function __construct($data)
    {
        logSession("VakifKatilimPosClient ödeme işlemi için başlatıldı.", null, 'info', 'payment_logs');

        $this->data = $data;
        $this->initializeIntegrationInfo();
        $this->amount = (int) (floatval($this->data['amount']) * 100);
    }

    protected function initializeIntegrationInfo()
    {
        $integrationInfo = $this->data['bank_integration_information'];

        $this->merchantId = $integrationInfo->MerchantId;
        $this->customerId = $integrationInfo->CustomerId;
        $this->userName = $integrationInfo->UserName;
        $this->hashPassword = $this->computeHash($integrationInfo->Password);
    }

    protected function computeHash($string)
    {
        return base64_encode(sha1(mb_convert_encoding($string, 'ISO-8859-9'), true));
    }

    protected function generateHash()
    {
        $hashString = implode('', [
            $this->merchantId,
            $this->data['oid'],
            $this->amount,
            $this->data['ok_url'],
            $this->data['fail_url'],
            $this->userName,
            $this->hashPassword
        ]);

        return $this->computeHash($hashString);
    }

    public function process3DTransaction() //request
    {
        logSession("VakifKatilimPosClient 3D islem isleniyor.", null, 'info', 'payment_logs');

        $data = [
            'OkUrl' => $this->data['ok_url'],
            'FailUrl' => $this->data['fail_url'],
            'HashData' => $this->generateHash(),
            'MerchantOrderId' => $this->data['oid'],
            'MerchantId' => $this->merchantId,
            'CustomerId' => $this->customerId,
            'UserName' => $this->userName,
            'HashPassword' => $this->hashPassword,
            'CardNumber' => $this->data['credit_card_number'],
            'CardExpireDateYear' => $this->data['credit_card_exp_date_year'],
            'CardExpireDateMonth' => $this->data['credit_card_exp_date_month'],
            'CardCVV2' => $this->data['cvc'],
            'CardHolderName' => $this->data['credit_card_name'],
            'InstallmentCount' => $this->data['installment'],
            'Amount' => $this->amount,
            'DisplayAmount' => $this->amount,
            'CurrencyCode' => '0949',
            'FECCurrencyCode' => '0949',
            'TransactionSecurity' => 3,
        ];

        $response = $this->sendRequest(
            'https://boa.vakifkatilim.com.tr/VirtualPOS.Gateway/Home/ThreeDModelPayGate',
            $this->convertToXml($data)
        );

        if (!$response['success'] || empty($response['body']) || stripos($response['body'], '<html') === false) {
            logSession("VakifKatilimPosClient ödeme formu oluşturulamadı.", ['message' => $response['message'] ?? 'Yanıt alınamadı.', 'body' => $response['body'] ?? null], 'error', 'payment_logs');

            return [
                'success' => false
            ];
        }

        logSession("VakifKatilimPosClient ödeme formu başarıyla oluşturuldu.", null, 'info', 'payment_logs');

        return [
            'success' => true,
            'html' => $response['body']
        ];
    }

    public function processSaleTransaction($data) //response
    {
        logSession("VakifKatilimPosClient islem talebini isleme alindi.", null, 'info', 'payment_logs');

        $data = [
            'HashData' => $this->generateHash(),
            'MerchantId' => $this->merchantId,
            'CustomerId' => $this->customerId,
            'UserName' => $this->userName,
            'MerchantOrderId' => $this->data['oid'],
            'Amount' => $this->amount,
            'OkUrl' => $this->data['ok_url'],
            'FailUrl' => $this->data['fail_url'],
            'AdditionalData' => [
                'AdditionalDataList' => [
                    'VPosAdditionalData' => [
                        'Key' => 'MD',
                        'Data' => $data['md'],
                    ]
                ]
            ],
            'TransactionSecurity' => 3,
        ];

        $response = $this->sendRequest(
            'https://boa.vakifkatilim.com.tr/VirtualPOS.Gateway/Home/ThreeDModelProvisionGate',
            $this->convertToXml($data)
        );

        if (!$response['success'] || empty($response['body'])) {
            logSession("VakifKatilimPosClient bankadan yanıt alınamadı.", null, 'error', 'payment_logs');

            return [
                'success' => false,
                'message' => 'Bankadan yanıt alınamadı.',
            ];
        }

        $responseXml = str_replace('encoding="utf-16"', 'encoding="utf-8"', $response['body']);

        try {
            $xmlObject = new SimpleXMLElement($responseXml);

            logSession("VakifKatilimPosClient bankadan yanıt alındı.", $xmlObject, 'error', 'payment_logs');

            return [
                'success' => true,
                'xml' => $xmlObject
            ];
        } catch (\Exception $ex) {
            logSession("VakifKatilimPosClient XML parse hatası.", ['message' => $ex->getMessage()], 'error', 'payment_logs');

            return [
                'success' => false,
                'message' => 'Bankadan alınan yanıt işlenemedi.',
            ];
        }
    }

    /**
     * İptal (Cancel) işlemi
     */
    public function cancel(array $data): array
    {
        return $this->processRefundRequest($data, 'SaleReversal');
    }

    /**
     * İade (Refund) işlemi
     */
    public function refund(array $data): array
    {
        return $this->processRefundRequest($data, 'PartialDrawback');
    }

    public function queryStatus(array $data): array
    {
        $oid = trim((string) ($data['oid'] ?? ''));
        if ($oid === '') {
            return [
                'success' => false,
                'status' => 'unknown',
                'message' => 'vakif_katilim_status_query_missing_order_id',
                'raw_payload' => [],
            ];
        }

        $xmlData = [
            'HashData' => $this->generateOrderQueryHash($oid),
            'MerchantId' => $this->merchantId,
            'SubMerchantId' => 0,
            'CustomerId' => $this->customerId,
            'UserName' => $this->userName,
            'MerchantOrderId' => $oid,
        ];

        $xml = $this->convertToXml($xmlData);
        $response = $this->sendRequest(
            'https://boa.vakifkatilim.com.tr/VirtualPOS.Gateway/Home/SelectOrderByMerchantOrderId',
            $xml
        );

        if (!($response['success'] ?? false) || empty($response['body'])) {
            return [
                'success' => false,
                'status' => 'unknown',
                'message' => 'vakif_katilim_status_query_transport_failed',
                'raw_payload' => [],
            ];
        }

        $responseXml = str_replace('encoding="utf-16"', 'encoding="utf-8"', (string) $response['body']);

        try {
            $xmlObject = new SimpleXMLElement($responseXml);
        } catch (\Throwable $e) {
            return [
                'success' => true,
                'status' => 'unknown',
                'message' => 'vakif_katilim_status_query_parse_failed',
                'provider_reference' => null,
                'auth_code' => null,
                'rrn' => null,
                'raw_payload' => ['response' => $responseXml],
            ];
        }

        $rawPayload = self::xmlToArray($xmlObject);
        $responseCode = (string) ($xmlObject->ResponseCode ?? '');
        $responseMessage = (string) ($xmlObject->ResponseMessage ?? '');

        $orderContract = $xmlObject->OrderContract ?? null;
        $transactionStatus = (string) ($orderContract->TransactionStatus ?? '');
        $provNumber = (string) ($orderContract->ProvNumber ?? '');
        $rrn = (string) ($orderContract->RRN ?? '');

        $status = 'unknown';
        $message = $responseMessage !== '' ? $responseMessage : null;
        if ($responseCode === '00' && $transactionStatus === '1') {
            $status = 'success';
        } elseif ($transactionStatus === '2') {
            $status = 'failed';
        } elseif ($responseCode !== '' && $responseCode !== '00') {
            if (self::isIpAuthOrConfigError($responseCode, $responseMessage)) {
                $status = 'unknown';
                $message = 'vakif_katilim_status_query_invalid_ip';
            } elseif (self::containsNotFoundText($responseMessage)) {
                $status = 'unknown';
            } else {
                $status = 'failed';
            }
        }

        return [
            'success' => true,
            'status' => $status,
            'message' => $message,
            'provider_reference' => self::firstNonEmptyString([$rrn]),
            'auth_code' => self::firstNonEmptyString([$provNumber]),
            'rrn' => self::firstNonEmptyString([$rrn]),
            'raw_payload' => $rawPayload,
        ];
    }

    /**
     * Vakıf Katılım iptal/iade XML isteği
     */
    private function processRefundRequest(array $data, string $transactionType): array
    {
        $xmlData = [
            'MerchantId' => $this->merchantId,
            'CustomerId' => $this->customerId,
            'UserName' => $this->userName,
            'HashPassword' => $this->hashPassword,
            'MerchantOrderId' => $data['oid'],
            'Amount' => $this->amount,
            'CurrencyCode' => '0949',
            'TransactionSecurity' => 3,
        ];

        $typeName = $transactionType === 'SaleReversal' ? 'İptal' : 'İade';

        logSession("VakifKatilimPosClient {$typeName} isteği gönderiliyor.", [
            'oid' => $data['oid'],
            'amount' => $data['amount'],
            'transactionType' => $transactionType,
        ], 'info', 'payment_logs');

        $xml = $this->convertToXml($xmlData);

        // Root element adını transaction type'a göre değiştirelim
        $xml = str_replace('VPosMessageContract', 'VPosMessageContract', $xml);

        $url = 'https://boa.vakifkatilim.com.tr/VirtualPOS.Gateway/Home/' .
               ($transactionType === 'SaleReversal' ? 'SaleReversalGate' : 'DrawBackGate');

        $response = $this->sendRequest($url, $xml);

        if (!$response['success'] || empty($response['body'])) {
            logSession("VakifKatilimPosClient {$typeName} bankadan yanıt alınamadı.", null, 'error', 'payment_logs');

            return [
                'success' => false,
                'message' => "{$typeName} işlemi sırasında bankadan yanıt alınamadı.",
            ];
        }

        $responseXml = str_replace('encoding="utf-16"', 'encoding="utf-8"', $response['body']);

        try {
            $xmlObject = new SimpleXMLElement($responseXml);

            logSession("VakifKatilimPosClient {$typeName} yanıtı.", $xmlObject, 'info', 'payment_logs');

            if ((string) $xmlObject->ResponseCode === '00') {
                return [
                    'success' => true,
                    'message' => "{$typeName} işlemi başarılı.",
                ];
            }

            $errorMsg = isset($xmlObject->ResponseMessage) ? (string) $xmlObject->ResponseMessage : '';

            return [
                'success' => false,
                'message' => "{$typeName} işlemi başarısız." . ($errorMsg ? " Hata: {$errorMsg}" : ''),
            ];
        } catch (\Exception $ex) {
            logSession("VakifKatilimPosClient {$typeName} XML parse hatası.", [
                'message' => $ex->getMessage(),
            ], 'error', 'payment_logs');

            return [
                'success' => false,
                'message' => "{$typeName} işlemi sırasında yanıt işlenemedi.",
            ];
        }
    }

    protected function sendRequest($url, $xmlData)
    {
        try {
            $response = Http::withHeaders([
                    'Content-Type' => 'application/xml',
                ])
                ->timeout(30)
                ->send('POST', $url, ['body' => $xmlData]);

            if ($response->failed()) {
                logSession("VakifKatilimPosClient HTTP hatası.", ['status' => $response->status()], 'error', 'payment_logs');

                return [
                    'success' => false,
                    'message' => 'Bankadan geçerli bir yanıt alınamadı.'
                ];
            }

            return [
                'success' => true,
                'body' => $response
            ];

        } catch (\Exception $ex) {
            logSession("VakifKatilimPosClient Exception oluştu.", ['message' => $ex->getMessage(), 'trace' => $ex->getTraceAsString()], 'error', 'payment_logs');

            return [
                'success' => false,
                'message' => 'Ödeme servisiyle bağlantı kurulamadı.'
            ];
        }
    }

    protected function convertToXml($data)
    {
        return ArrayToXml::convert($data, 'VPosMessageContract', true, 'ISO-8859-1', '1.0');
    }

    protected function generateOrderQueryHash(string $oid): string
    {
        return $this->computeHash($this->merchantId . $oid . $this->userName . $this->hashPassword);
    }

    private static function xmlToArray(SimpleXMLElement $xml): array
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

    private static function isIpAuthOrConfigError(string $responseCode, string $responseMessage): bool
    {
        $normalizedCode = strtolower(trim($responseCode));
        if (in_array($normalizedCode, ['posmerchantiperror', 'posmerchantautherror', 'posmerchantconfigerror'], true)) {
            return true;
        }

        $message = mb_strtolower(trim($responseMessage), 'UTF-8');

        return str_contains($message, 'farkli ip')
            || str_contains($message, 'farklı ip')
            || str_contains($message, 'yetki')
            || str_contains($message, 'auth')
            || str_contains($message, 'config');
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
