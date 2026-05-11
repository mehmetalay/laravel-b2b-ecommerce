<?php

namespace App\Infrastructure\Payment\Clients;

class GarantiPosClient
{
    private $data;
    private $strProvisionPassword;
    private $strTerminalID_;
    private $strTerminalID;
    private $strStoreKey;
    private $strTerminalMerchantID;
    private $strType;
    private $mode;
    private $action;
    private $amount;
    private $installment;
    private $okUrl;
    private $failUrl;
    private $oid;

    public function __construct($data)
    {
        logSession("GarantiPosService ödeme işlemi için form başlatıldı.", null, 'info', 'payment_logs');
        
        $this->data = $data;

        $installment = $this->data['installment'] ?? 1;
        
        $this->strProvisionPassword = $this->data['bank_integration_information']->strProvisionPassword;
        $this->strTerminalID_ = $this->data['bank_integration_information']->strTerminalID_;
        $this->strTerminalID = $this->data['bank_integration_information']->strTerminalID;
        $this->strStoreKey = $this->data['bank_integration_information']->strStoreKey;
        $this->strTerminalMerchantID = $this->data['bank_integration_information']->strTerminalMerchantID;
        $this->strType = 'sales';
        $this->mode = 'PROD';
        $this->action = 'https://sanalposprov.garanti.com.tr/servlet/gt3dengine';
        $this->amount = round($this->data['amount'], 2) * 100;
        $this->installment = $installment == 1 ? 0 : $installment;
        $this->okUrl = $this->data['ok_url'] ?? '';
        $this->failUrl = $this->data['fail_url'] ?? '';
        $this->oid = $this->data['oid'];
    }

    public function generateHash()
    {
        $SecurityData = strtoupper(sha1($this->strProvisionPassword . $this->strTerminalID_));
        return strtoupper(sha1($this->strTerminalID . $this->oid . $this->amount . $this->okUrl . $this->failUrl . $this->strType . $this->installment . $this->strStoreKey . $SecurityData));
    }

    /**
     * İptal (Void) işlemi
     */
    public static function cancel(array $data): array
    {
        return self::processRefundRequest($data, 'void');
    }

    /**
     * İade (Refund) işlemi
     */
    public static function refund(array $data): array
    {
        return self::processRefundRequest($data, 'refund');
    }

    /**
     * Siparis durum sorgulama (orderinq) islemi.
     */
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

        $strTerminalID_ = $info->strTerminalID_ ?? null;
        $strTerminalID = $info->strTerminalID ?? null;
        $strTerminalMerchantID = $info->strTerminalMerchantID ?? null;
        $strProvisionPassword = $info->strProvisionPassword ?? null;
        $oid = (string) ($data['oid'] ?? '');

        if (!$strTerminalID_ || !$strTerminalID || !$strTerminalMerchantID || !$strProvisionPassword || $oid === '') {
            return [
                'success' => false,
                'status' => 'unknown',
                'message' => 'garanti_status_query_invalid_configuration',
                'raw_payload' => [],
            ];
        }

        $securityData = strtoupper(sha1($strProvisionPassword . $strTerminalID_));
        $hashData = strtoupper(sha1($oid . $strTerminalID . $securityData));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <GVPSRequest>
                <Mode>PROD</Mode>
                <Version>v0.01</Version>
                <Terminal>
                    <ProvUserID>PROVAUT</ProvUserID>
                    <HashData>' . $hashData . '</HashData>
                    <UserID>PROVAUT</UserID>
                    <ID>' . $strTerminalID . '</ID>
                    <MerchantID>' . $strTerminalMerchantID . '</MerchantID>
                </Terminal>
                <Customer>
                    <IPAddress>' . request()->ip() . '</IPAddress>
                    <EmailAddress></EmailAddress>
                </Customer>
                <Order>
                    <OrderID>' . $oid . '</OrderID>
                </Order>
                <Transaction>
                    <Type>orderinq</Type>
                </Transaction>
            </GVPSRequest>';

        logSession('GarantiPosService status query request.', [
            'oid' => $oid,
            'xml' => $xml,
        ], 'info', 'payment_logs');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://sanalposprov.garanti.com.tr/VPServlet');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'data=' . $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $result = curl_exec($ch);

        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);

            logSession('GarantiPosService status query cURL error.', ['message' => $error], 'error', 'payment_logs');

            return [
                'success' => false,
                'status' => 'unknown',
                'message' => 'garanti_status_query_curl_error: ' . $error,
                'raw_payload' => [],
            ];
        }

        curl_close($ch);
        logSession('GarantiPosService status query response.', ['response' => $result], 'info', 'payment_logs');

        $xmlResponse = @simplexml_load_string($result);
        if (!$xmlResponse) {
            return [
                'success' => false,
                'status' => 'unknown',
                'message' => 'garanti_status_query_invalid_response',
                'raw_payload' => ['response' => $result],
            ];
        }

        $rawPayload = self::xmlToArray($xmlResponse);
        $code = trim((string) ($xmlResponse->Transaction->Response->Code ?? ''));
        $reasonCode = trim((string) ($xmlResponse->Transaction->Response->ReasonCode ?? ''));
        $status = self::mapGarantiQueryStatus($xmlResponse);
        $message = self::resolveGarantiQueryMessage($xmlResponse);
        if (self::isAuthOrConfigError($code, $reasonCode, $message)) {
            $status = 'unknown';
            $message = 'garanti_status_query_auth_failed';
        }

        $providerReference = self::firstNonEmptyString([
            (string) ($xmlResponse->Order->OrderInqResult->RetrefNum ?? ''),
            (string) ($xmlResponse->Transaction->RetrefNum ?? ''),
            (string) ($xmlResponse->Order->OrderInqResult->GPID ?? ''),
        ]);
        $authCode = self::firstNonEmptyString([
            (string) ($xmlResponse->Order->OrderInqResult->AuthCode ?? ''),
            (string) ($xmlResponse->Transaction->AuthCode ?? ''),
        ]);
        $rrn = self::firstNonEmptyString([
            (string) ($xmlResponse->Order->OrderInqResult->RetrefNum ?? ''),
            (string) ($xmlResponse->Transaction->RetrefNum ?? ''),
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

    /**
     * Garanti POS iptal/iade XML isteği
     */
    private static function processRefundRequest(array $data, string $type): array
    {
        $info = $data['bank_integration_information'];
        $strTerminalID_ = $info->strTerminalID_;
        $strTerminalID = $info->strTerminalID;
        $strTerminalMerchantID = $info->strTerminalMerchantID;
        $strProvisionPassword = $info->strProvisionPassword;
        $amount = round($data['amount'], 2) * 100;

        $SecurityData = strtoupper(sha1($strProvisionPassword . $strTerminalID_));
        $HashData = strtoupper(sha1($data['oid'] . $strTerminalID . $amount . $SecurityData));

        $provUserID = $type === 'void' ? 'PROVRFN' : 'PROVRFN';

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <GVPSRequest>
                <Mode>PROD</Mode>
                <Version>v0.01</Version>
                <Terminal>
                    <ProvUserID>' . $provUserID . '</ProvUserID>
                    <HashData>' . $HashData . '</HashData>
                    <UserID>' . $provUserID . '</UserID>
                    <ID>' . $strTerminalID . '</ID>
                    <MerchantID>' . $strTerminalMerchantID . '</MerchantID>
                </Terminal>
                <Customer>
                    <IPAddress>' . request()->ip() . '</IPAddress>
                    <EmailAddress></EmailAddress>
                </Customer>
                <Order>
                    <OrderID>' . $data['oid'] . '</OrderID>
                </Order>
                <Transaction>
                    <Type>' . $type . '</Type>
                    <InstallmentCnt/>
                    <Amount>' . $amount . '</Amount>
                    <CurrencyCode>949</CurrencyCode>
                    <CardholderPresentCode>0</CardholderPresentCode>
                    <MotoInd>N</MotoInd>
                </Transaction>
            </GVPSRequest>';

        logSession("GarantiPosService {$type} isteği gönderiliyor.", [
            'oid' => $data['oid'],
            'amount' => $data['amount'],
        ], 'info', 'payment_logs');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://sanalposprov.garanti.com.tr/VPServlet');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'data=' . $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $result = curl_exec($ch);
        curl_close($ch);

        logSession("GarantiPosService {$type} yanıtı.", ['response' => $result], 'info', 'payment_logs');

        $xmlResponse = @simplexml_load_string($result);

        if ($xmlResponse) {
            $reasonCode = (string)($xmlResponse->Transaction->Response->ReasonCode ?? '');
            $message = (string)($xmlResponse->Transaction->Response->ErrorMsg ?? '');

            if ($reasonCode === '00') {
                return [
                    'success' => true,
                    'message' => $type === 'void' ? 'İptal işlemi başarılı.' : 'İade işlemi başarılı.',
                ];
            }

            return [
                'success' => false,
                'message' => ($type === 'void' ? 'İptal' : 'İade') . ' işlemi başarısız.' . ($message ? ' Hata: ' . $message : ''),
            ];
        }

        return [
            'success' => false,
            'message' => ($type === 'void' ? 'İptal' : 'İade') . ' işlemi sırasında bankadan yanıt alınamadı.',
        ];
    }

    private static function mapGarantiQueryStatus(\SimpleXMLElement $xmlResponse): string
    {
        $orderStatus = strtolower(trim((string) ($xmlResponse->Order->OrderInqResult->Status ?? '')));
        $txnCode = trim((string) ($xmlResponse->Transaction->Response->Code ?? ''));
        $reasonCode = trim((string) ($xmlResponse->Transaction->Response->ReasonCode ?? ''));
        $txnMessage = strtolower(trim((string) ($xmlResponse->Transaction->Response->Message ?? '')));
        $errorMsg = strtolower(trim((string) ($xmlResponse->Transaction->Response->ErrorMsg ?? '')));
        $combined = trim($txnMessage . ' ' . $errorMsg);

        if ($txnCode === '00' || $reasonCode === '00') {
            if (in_array($orderStatus, ['approved', 'success', 'successful', 'succeeded', 'sale'], true)) {
                return 'success';
            }

            if (in_array($orderStatus, ['pending', 'inprogress', 'processing', 'wait'], true)) {
                return 'pending';
            }

            if ($combined !== '' && (str_contains($combined, 'approved') || str_contains($combined, 'success'))) {
                return 'success';
            }

            return 'unknown';
        }

        if ($combined !== '' && (
            str_contains($combined, 'not found')
            || str_contains($combined, 'bulunam')
            || str_contains($combined, 'kayıt')
            || str_contains($combined, 'kayit')
        )) {
            return 'unknown';
        }

        if ($combined !== '' && (
            str_contains($combined, 'declin')
            || str_contains($combined, 'fail')
            || str_contains($combined, 'reject')
            || str_contains($combined, 'error')
            || str_contains($combined, 'hata')
        )) {
            return 'failed';
        }

        if (in_array($orderStatus, ['declined', 'failed', 'cancelled', 'canceled', 'rejected'], true)) {
            return 'failed';
        }

        return 'unknown';
    }

    private static function resolveGarantiQueryMessage(\SimpleXMLElement $xmlResponse): ?string
    {
        $values = [
            (string) ($xmlResponse->Transaction->Response->ErrorMsg ?? ''),
            (string) ($xmlResponse->Transaction->Response->Message ?? ''),
            (string) ($xmlResponse->Order->OrderInqResult->SysErrMsg ?? ''),
            (string) ($xmlResponse->Transaction->Response->SysErrMsg ?? ''),
        ];

        foreach ($values as $value) {
            $trimmed = trim($value);
            if ($trimmed !== '') {
                return $trimmed;
            }
        }

        return null;
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

    private static function isAuthOrConfigError(string $code, string $reasonCode, ?string $message): bool
    {
        $value = strtolower(trim((string) $message));

        if ($code === '92' || $reasonCode === '0651') {
            return true;
        }

        return str_contains($value, 'kullanici')
            || str_contains($value, 'kullanıcı')
            || str_contains($value, 'sifre')
            || str_contains($value, 'şifre')
            || str_contains($value, 'password')
            || str_contains($value, 'credential')
            || str_contains($value, 'yetki')
            || str_contains($value, 'auth');
    }

    public function generatePaymentForm()
    {
        $postData = [
            'secure3dsecuritylevel' => "3D_PAY",
            'cardnumber' => $this->data['credit_card_number'],
            'cardexpiredatemonth' => $this->data['credit_card_exp_date_month'],
            'cardexpiredateyear' => $this->data['credit_card_exp_date_year'],
            'cardcvv2' => $this->data['cvc'],
            'mode' => $this->mode,
            'apiversion' => "v0.01",
            'terminalprovuserid' => "PROVAUT",
            'terminaluserid' => "PROVAUT",
            'terminalmerchantid' => $this->strTerminalMerchantID,
            'txntype' => $this->strType,
            'txnamount' => $this->amount,
            'txncurrencycode' => "949",
            'txninstallmentcount' => $this->installment,
            'orderid' => $this->oid,
            'terminalid' => $this->strTerminalID,
            'successurl' => $this->okUrl,
            'errorurl' => $this->failUrl,
            'customeripaddress' => request()->ip(),
            'customeremailaddress' => "mehmet@akademiyazilim.com",
            'secure3dhash' => $this->generateHash(),
            'cardname' => $this->data['credit_card_name'],
            'oid' => $this->oid
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->action,
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
            logSession("GarantiPosService ödeme formu oluşturma başarısız. cURL Error", ['message' => curl_error($ch)], 'info', 'payment_logs');
        } else if ($httpCode !== 200) {
            logSession("GarantiPosService HTTP hatası.", ['http_code' => $httpCode], 'error', 'payment_logs');
        } else {
            $result = true;
            logSession("GarantiPosService ödeme formu başarıyla oluşturuldu.", ['http_code' => $httpCode], 'info', 'payment_logs');
        }

        curl_close($ch);

        return [
            'result' => $result,
            'response' => $response
        ];
    }
}

