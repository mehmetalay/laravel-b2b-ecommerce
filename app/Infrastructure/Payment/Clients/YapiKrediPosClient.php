<?php

namespace App\Infrastructure\Payment\Clients;

use SimpleXMLElement;

class YapiKrediPosClient
{
    private $data;
    private $merchantId;
    private $terminalId;
    private $posnetId;
    private $encKey;
    private $serviceUrl;
    private $action;

    public function __construct($data)
    {
        logSession("YapiKrediPosClient ödeme işlemi için form başlatıldı.", null, 'info', 'payment_logs');

        $this->data = $data;

        $bank_integration_information = $this->data['bank_integration_information'];

        $this->merchantId = $bank_integration_information->merchantId;
        $this->terminalId = $bank_integration_information->terminalId;
        $this->posnetId = $bank_integration_information->posnetId;
        $this->encKey = $bank_integration_information->encKey;

        $this->serviceUrl = 'https://posnet.yapikredi.com.tr/PosnetWebService/XML';
        $this->action = 'https://posnet.yapikredi.com.tr/3DSWebService/YKBPaymentService';
    }

    public function oosRequestData()
    {
        try {
            logSession("YapiKrediPosClient oosRequestData başlatıldı.", null, 'info', 'payment_logs');

            $installment = $this->data['installment'] == 1 ? '00' : '0' . $this->data['installment'];

            $xml = new SimpleXMLElement('<posnetRequest/>');
            $xml->addChild('mid', $this->merchantId);
            $xml->addChild('tid', $this->terminalId);

            $sale = $xml->addChild('oosRequestData');
            $sale->addChild('posnetid', $this->posnetId);
            $sale->addChild('XID', $this->data['oid']);
            $sale->addChild('amount', $this->data['amount']);
            $sale->addChild('currencyCode', 'TL');
            $sale->addChild('installment', $installment);
            $sale->addChild('tranType', 'Sale');
            $sale->addChild('cardHolderName', $this->data['credit_card_name']);
            $sale->addChild('ccno', $this->data['credit_card_number']);
            $sale->addChild('expDate', $this->data['credit_card_exp_date_year'] . $this->data['credit_card_exp_date_month']);
            $sale->addChild('cvc', $this->data['cvc']);

            $xmlData = $xml->asXML();

            logSession("YapiKrediPosClient oosRequestData request:", $xmlData, 'info', 'payment_logs');

            $responseXml = $this->sendRequest($xmlData);
            $response = simplexml_load_string($responseXml);

            if ($response === false) {
                $message = 'Geçersiz XML Yanıtı Alındı.';

                logSession("YapiKrediPosClient oosRequestData response declined. Message: Geçersiz XML Yanıtı Alındı.", null, 'info', 'payment_logs');

                return [
                    'response' => 'declined',
                    'message' => $message
                ];
            }

            logSession("YapiKrediPosClient oosRequestData response:", $response, 'info', 'payment_logs');

            if (isset($response->approved) && (int) $response->approved === 1) {

                logSession("YapiKrediPosClient oosRequestData response approved.", null, 'info', 'payment_logs');

                return [
                    'response' => 'approved',
                    'html' => $this->generatePaymentForm($response->oosRequestDataResponse)
                ];
            } else {
                $message = isset($response->respText) && $response->respText != '' ? (string) $response->respText : 'Bilinmeyen hata';

                logSession("YapiKrediPosClient oosRequestData response declined. Message: {$message}", null, 'info', 'payment_logs');

                return [
                    'response' => 'declined',
                    'message' => $message
                ];
            }
        } catch (\Exception $e) {
            logSession("YapiKrediPosClient oosRequestData error. Message: {$e->getMessage()}", null, 'info', 'payment_logs');

            return [
                'response' => 'declined',
                'message' => 'İşlem sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.'
            ];
        }
    }

    public function oosResolveMerchantData()
    {
        try {
            logSession("YapiKrediPosClient oosResolveMerchantData başlatıldı.", null, 'info', 'payment_logs');

            $xml = new SimpleXMLElement('<posnetRequest/>');
            $xml->addChild('mid', $this->merchantId);
            $xml->addChild('tid', $this->terminalId);

            $sale = $xml->addChild('oosResolveMerchantData');
            $sale->addChild('bankData', $this->data['BankPacket']);
            $sale->addChild('merchantData', $this->data['MerchantPacket']);
            $sale->addChild('sign', $this->data['Sign']);
            $sale->addChild('mac', $this->generateMac());

            $xmlData = $xml->asXML();

            logSession("YapiKrediPosClient oosResolveMerchantData request:", $xmlData, 'info', 'payment_logs');

            $responseXml = $this->sendRequest($xmlData);
            $response = simplexml_load_string($responseXml);

            if ($response === false) {
                $message = 'Geçersiz XML Yanıtı Alındı.';

                logSession("YapiKrediPosClient oosResolveMerchantData response declined. Message: {$message}", null, 'info', 'payment_logs');

                return [
                    'response' => 'declined',
                    'message' => $message
                ];
            }

            logSession("YapiKrediPosClient oosResolveMerchantData response:", $response, 'info', 'payment_logs');

            if (isset($response->approved) && (int) $response->approved === 1) {
                logSession("YapiKrediPosClient oosResolveMerchantData response approved.", null, 'info', 'payment_logs');

                if ((int) $response->oosResolveMerchantDataResponse->mdStatus === 1) {
                    logSession("YapiKrediPosClient oosResolveMerchantData response mdStatus 1", null, 'info', 'payment_logs');

                    return $this->oosTranData($this->data['BankPacket']);
                } else {
                    return [
                        'response' => 'declined',
                        'message' => '3D Doğrulama Başarısız.'
                    ];
                }
            } else {
                $message = isset($response->respText) && $response->respText != '' ? (string) $response->respText : 'Bilinmeyen hata';

                logSession("YapiKrediPosClient oosResolveMerchantData response declined. Message: {$message}", null, 'info', 'payment_logs');

                return [
                    'response' => 'declined',
                    'message' => $message
                ];
            }
        } catch (\Exception $e) {
            logSession("YapiKrediPosClient oosResolveMerchantData error. Message: {$e->getMessage()}", null, 'info', 'payment_logs');

            return [
                'response' => 'declined',
                'message' => 'İşlem sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.'
            ];
        }
    }

    public function oosTranData($bankData)
    {
        try {
            logSession("YapiKrediPosClient oosTranData başlatıldı.", null, 'info', 'payment_logs');

            $xml = new SimpleXMLElement('<posnetRequest/>');
            $xml->addChild('mid', $this->merchantId);
            $xml->addChild('tid', $this->terminalId);

            $sale = $xml->addChild('oosTranData');
            $sale->addChild('bankData', $bankData);
            $sale->addChild('wpAmount', '0');
            $sale->addChild('mac', $this->generateMac());

            $xmlData = $xml->asXML();

            logSession("YapiKrediPosClient oosTranData request:", $xmlData, 'info', 'payment_logs');

            $responseXml = $this->sendRequest($xmlData);
            $response = simplexml_load_string($responseXml);

            if ($response === false) {
                $message = 'Geçersiz XML Yanıtı Alındı.';

                logSession("YapiKrediPosClient oosTranData response declined. Message: {$message}", null, 'info', 'payment_logs');

                return [
                    'response' => 'declined',
                    'message' => $message
                ];
            }

            logSession("YapiKrediPosClient oosTranData response:", $response, 'info', 'payment_logs');

            if (isset($response->approved) && (int) $response->approved === 1) {
                logSession("YapiKrediPosClient oosTranData response approved", null, 'info', 'payment_logs');

                $hostLogKey = self::firstNonEmptyString([
                    (string) ($response->hostlogkey ?? ''),
                    (string) ($response->hostLogKey ?? ''),
                ]);
                $authCode = self::firstNonEmptyString([
                    (string) ($response->authCode ?? ''),
                    (string) ($response->authcode ?? ''),
                ]);
                $rrn = self::firstNonEmptyString([
                    (string) ($response->rrn ?? ''),
                    (string) ($response->RRN ?? ''),
                ]);

                return [
                    'response' => 'approved',
                    'hostLogKey' => $hostLogKey,
                    'authCode' => $authCode,
                    'rrn' => $rrn,
                ];
            } else {
                $message = isset($response->respText) && $response->respText != '' ? (string) $response->respText : 'Bilinmeyen Hata';

                logSession("YapiKrediPosClient oosTranData response declined. Message: {$message}", null, 'info', 'payment_logs');

                return [
                    'response' => 'declined',
                    'message' => $message
                ];
            }
        } catch (\Exception $e) {
            logSession("YapiKrediPosClient oosTranData error. Message: {$e->getMessage()}", null, 'info', 'payment_logs');

            return [
                'response' => 'declined',
                'message' => 'İşlem sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.'
            ];
        }
        return $xmlData;
    }

    /**
     * İptal (Void) işlemi
     */
    public static function cancel(array $data): array
    {
        return self::processRefundRequest($data, 'void');
    }

    /**
     * İade (Return) işlemi
     */
    public static function refund(array $data): array
    {
        return self::processRefundRequest($data, 'return');
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
                'message' => 'yapikredi_status_query_missing_order_id',
                'raw_payload' => [],
            ];
        }
        $queryOrderId = str_starts_with($oid, 'TDS_') ? $oid : 'TDS_' . $oid;

        $xml = new SimpleXMLElement('<posnetRequest/>');
        $xml->addChild('mid', (string) ($info->merchantId ?? ''));
        $xml->addChild('tid', (string) ($info->terminalId ?? ''));
        $agreement = $xml->addChild('agreement');
        $agreement->addChild('orderID', $queryOrderId);
        $xmlData = $xml->asXML();
        $url = 'https://posnet.yapikredi.com.tr/PosnetWebService/XML?xmldata=' . urlencode((string) $xmlData);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $result = curl_exec($ch);

        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);

            return [
                'success' => false,
                'status' => 'unknown',
                'message' => 'yapikredi_status_query_curl_error: ' . $error,
                'raw_payload' => [],
            ];
        }

        curl_close($ch);
        $response = @simplexml_load_string((string) $result);
        if (!$response) {
            return [
                'success' => true,
                'status' => 'unknown',
                'message' => 'yapikredi_status_query_invalid_response',
                'provider_reference' => null,
                'auth_code' => null,
                'rrn' => null,
                'raw_payload' => ['response' => $result],
            ];
        }

        $rawPayload = self::xmlToArray($response);
        $approved = (int) ($response->approved ?? 0);
        $respCode = trim((string) ($response->respCode ?? ''));
        $respText = trim((string) ($response->respText ?? ''));

        $transactions = $response->transactions->transaction ?? [];
        $transactionList = [];
        foreach ($transactions as $txn) {
            $transactionList[] = $txn;
        }

        $matched = null;
        $acceptableOrderIds = [$oid, $queryOrderId];
        foreach ($transactionList as $txn) {
            $txnOrderId = trim((string) ($txn->orderID ?? $txn->orderId ?? ''));
            if ($txnOrderId !== '' && in_array($txnOrderId, $acceptableOrderIds, true)) {
                $matched = $txn;
                break;
            }
        }

        if ($matched === null && count($transactionList) === 1) {
            $matched = $transactionList[0];
        }

        if ($matched === null && self::containsNotFoundText($respText)) {
            return [
                'success' => true,
                'status' => 'unknown',
                'message' => $respText !== '' ? $respText : 'yapikredi_status_query_not_found',
                'provider_reference' => null,
                'auth_code' => null,
                'rrn' => null,
                'raw_payload' => $rawPayload,
            ];
        }

        $txnStatus = trim((string) ($matched->txnStatus ?? ''));
        $state = strtolower(trim((string) ($matched->state ?? '')));

        $status = 'unknown';
        $successStates = ['sale', 'vft_sale', 'authorization', 'capture'];
        $failedStates = ['reverse', 'return', 'cancel', 'cancelled', 'void'];

        if ($approved === 1 && $txnStatus === '1' && in_array($state, $successStates, true)) {
            $status = 'success';
        } elseif ($approved === 1 && in_array($state, $failedStates, true)) {
            $status = 'failed';
        } elseif ($approved === 0 && self::isDefinitiveFailureCode($respCode, $respText)) {
            $status = 'failed';
        }

        return [
            'success' => true,
            'status' => $status,
            'message' => $respText !== '' ? $respText : null,
            'provider_reference' => self::firstNonEmptyString([
                (string) ($matched->hostLogKey ?? ''),
                (string) ($matched->hostRefNum ?? ''),
            ]),
            'auth_code' => self::firstNonEmptyString([
                (string) ($matched->authCode ?? ''),
            ]),
            'rrn' => self::firstNonEmptyString([
                (string) ($matched->hostLogKey ?? ''),
                (string) ($matched->rrn ?? ''),
            ]),
            'raw_payload' => $rawPayload,
        ];
    }

    /**
     * Yapı Kredi POSNET iptal/iade XML isteği
     */
    private static function processRefundRequest(array $data, string $type): array
    {
        $info = $data['bank_integration_information'];
        $merchantId = $info->merchantId;
        $terminalId = $info->terminalId;
        $amount = (int) round($data['amount'] * 100);

        $xml = new SimpleXMLElement('<posnetRequest/>');
        $xml->addChild('mid', $merchantId);
        $xml->addChild('tid', $terminalId);

        if ($type === 'void') {
            $voidNode = $xml->addChild('void');
            $voidNode->addChild('transaction', 'sale');
            $voidNode->addChild('orderID', $data['oid']);
        } else {
            $returnNode = $xml->addChild('return');
            $returnNode->addChild('amount', $amount);
            $returnNode->addChild('currencyCode', 'TL');
            $returnNode->addChild('orderID', $data['oid']);
        }

        $xmlData = $xml->asXML();
        $typeName = $type === 'void' ? 'İptal' : 'İade';

        logSession("YapiKrediPosClient {$typeName} isteği gönderiliyor.", [
            'oid' => $data['oid'],
            'amount' => $data['amount'],
            'xml' => $xmlData,
        ], 'info', 'payment_logs');

        $serviceUrl = 'https://posnet.yapikredi.com.tr/PosnetWebService/XML';
        $url = $serviceUrl . '?xmldata=' . urlencode($xmlData);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $result = curl_exec($ch);
        curl_close($ch);

        logSession("YapiKrediPosClient {$typeName} yanıtı.", ['response' => $result], 'info', 'payment_logs');

        $response = @simplexml_load_string($result);

        if ($response && isset($response->approved) && (int) $response->approved === 1) {
            return [
                'success' => true,
                'message' => "{$typeName} işlemi başarılı.",
            ];
        }

        $errorMsg = '';
        if ($response && isset($response->respText)) {
            $errorMsg = (string) $response->respText;
        }

        return [
            'success' => false,
            'message' => "{$typeName} işlemi başarısız." . ($errorMsg ? " Hata: {$errorMsg}" : ''),
        ];
    }

    protected function sendRequest($xmlData)
    {
        $url = $this->serviceUrl . '?xmldata=' . urlencode($xmlData);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $result = curl_exec($ch);

        if ($result === false) {
            $error = curl_error($ch);
            logSession("YapiKrediPosClient sendRequest cURL error. Message: {$error}", null, 'info', 'payment_logs');
        }

        curl_close($ch);

        return $result;
    }

    protected function generatePaymentForm($data)
    {
        $formHtml =
            '<form id="form" method="POST" action="' . $this->action . '">
                <input type="hidden" name="mid" value="' . $this->merchantId . '">
                <input type="hidden" name="posnetID" value="' . $this->posnetId . '">
                <input type="hidden" name="posnetData" value="' . $data->data1 . '">
                <input type="hidden" name="posnetData2" value="' . $data->data2 . '">
                <input type="hidden" name="digest" value="' . $data->sign . '">
                <input type="hidden" name="vftCode" value="">
                <input type="hidden" name="merchantReturnURL" value="'. $this->data['ok_url'] .'">
                <input type="hidden" name="lang" value="tr">
                <input type="hidden" name="url" value="">
                <input type="hidden" name="openANewWindow" value="0">
            </form>';

        logSession("YapiKrediPosClient ödeme formu başarıyla oluşturuluyor.", null, 'info', 'payment_logs');

        return $formHtml;
    }

    public function hashString($originalString)
    {
        return base64_encode(hash('sha256', mb_convert_encoding($originalString, 'UTF-8', 'auto'), true));
    }

    public function generateMac()
    {
        $firstHash = $this->hashString($this->encKey . ";" . $this->terminalId);

        return $this->hashString($this->data['Xid'] . ";" . $this->data['amount'] . ";TL;" . $this->merchantId . ";". $firstHash);
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

    private static function isDefinitiveFailureCode(string $code, string $text): bool
    {
        $text = strtolower(trim($text));
        if (in_array($code, ['05', '12', '51', '54', '57', '58', '91', '96'], true)) {
            return true;
        }

        return str_contains($text, 'declin')
            || str_contains($text, 'reject')
            || str_contains($text, 'error')
            || str_contains($text, 'hata')
            || str_contains($text, 'fail');
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
