<?php

namespace App\Infrastructure\Payment\Clients\Vakifbank;

use Illuminate\Http\Request;
use Spatie\ArrayToXml\ArrayToXml;

class VakifReq
{
    protected $MerchantId;
    protected $MerchantPassword;
    protected $VerifyEnrollmentRequestId;
    protected $Pan;
    protected $ExpiryDate;
    protected $PurchaseAmount;
    protected $Currency;
    protected $SuccessUrl;
    protected $FailureUrl;
    protected $InstallmentCount;

    public function setVerifyEnrollmentRequestID($verifyEnrollmentRequestId): VakifReq
    {
        $this->VerifyEnrollmentRequestId = $verifyEnrollmentRequestId;
        return $this;
    }

    public function setPan($pan): VakifReq
    {
        $this->Pan = $pan;
        return $this;
    }

    public function setExpiryDate($expiryDate): VakifReq
    {
        $this->ExpiryDate = $expiryDate;
        return $this;
    }

    public function setPurchaseAmount($purchaseAmount): VakifReq
    {
        $this->PurchaseAmount = str_replace(',', '', number_format($purchaseAmount, 2));
        return $this;
    }

    public function setCurrency(): VakifReq
    {
        $this->Currency = '949';
        return $this;
    }

    public function setSuccessURL($successURL): VakifReq
    {
        $this->SuccessUrl = $successURL;
        return $this;
    }

    public function setFailureURL($failureURL): VakifReq
    {
        $this->FailureUrl = $failureURL;
        return $this;
    }

    public function setInstallmentCount($installmentCount): VakifReq
    {
        $this->InstallmentCount = $installmentCount;
        return $this;
    }

    public function setMerchantID(string $merchantID): VakifReq
    {
        $this->MerchantId = $merchantID;
        return $this;
    }

    public function setMerchantPassword(string $merchantPassword): VakifReq
    {
        $this->MerchantPassword = $merchantPassword;
        return $this;
    }

    public function check()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://3dsecure.vakifbank.com.tr:4443/MPIAPI/MPI_Enrollment.aspx');//https://3dsecuretest.vakifbank.com.tr:4443/MPIAPI/MPI_Enrollment.aspx
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type" => 'application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this));
        $result = curl_exec($ch);

        if ($result === false) {
            logSession("Vakifbank VakifReq check cURL Error", ['message' => curl_error($ch)], 'info', 'payment_logs');
        } else {
            logSession("Vakifbank VakifReq check cURL result", $result, 'info', 'payment_logs');
        }

        curl_close($ch);

        $xml = simplexml_load_string($result, "SimpleXMLElement", LIBXML_NOCDATA);

        return $xml;
    }

    public function pay(Request $request, $bankIntegrationJson)
    {
        $data = [
            'TransactionType' => 'Sale',
            'MerchantId' => $bankIntegrationJson->IsyeriNo,
            'TerminalNo' => $bankIntegrationJson->TerminalNo,
            'Password' => $bankIntegrationJson->IsyeriSifre,
            // 'Pan' => $request->input('Pan'),
            // 'Expiry' => '20' . $request->input('Expiry'),
            // 'CurrencyAmount' => str_replace(',', '', number_format(($request->input('PurchAmount') / 100), 2)),
            // 'CurrencyCode' => $request->input('PurchCurrency'),
            'ECI' => $request->input('Eci'),
            'CAVV' => $request->input('Cavv'),
            'MpiTransactionId' => $request->input('VerifyEnrollmentRequestId'),
            'ClientIp' => '144.76.62.72',
            'TransactionDeviceSource' => 0,
        ];

        if ($request->input('InstallmentCount')) {
            $data['NumberOfInstallments'] = $request->input('InstallmentCount');
        }

        $xml = ArrayToXml::convert($data, 'VposRequest', true, 'utf-8');

        logSession("Vakifbank VakifReq pay request", $xml, 'info', 'payment_logs');

        $result = simplexml_load_string($this->curl($xml), "SimpleXMLElement", LIBXML_NOCDATA);

        logSession("Vakifbank VakifReq pay response", isset($result->original) ? $result->original : (string)$result, 'info', 'payment_logs');

        return response()->json($result);
    }

    /**
     * İptal (Cancel) işlemi
     */
    public function cancel(array $data, $bankIntegrationJson): array
    {
        return $this->processRefundRequest($data, $bankIntegrationJson, 'Cancel');
    }

    /**
     * İade (Refund) işlemi
     */
    public function refund(array $data, $bankIntegrationJson): array
    {
        return $this->processRefundRequest($data, $bankIntegrationJson, 'Refund');
    }

    /**
     * Vakıfbank iptal/iade XML isteği
     */
    private function processRefundRequest(array $data, $bankIntegrationJson, string $type): array
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

        $typeName = $type === 'Cancel' ? 'İptal' : 'İade';

        logSession("VakifReq {$typeName} isteği gönderiliyor.", [
            'oid' => $data['oid'],
            'provider_reference' => $data['provider_reference'],
            'amount' => $data['amount'],
            'xml' => $xml,
        ], 'info', 'payment_logs');

        $result = simplexml_load_string($this->curl($xml), "SimpleXMLElement", LIBXML_NOCDATA);

        logSession("VakifReq {$typeName} yanıtı.", [
            'response' => $result ? json_encode($result) : 'null',
        ], 'info', 'payment_logs');

        if ($result && isset($result->ResultCode) && (string) $result->ResultCode === '0000') {
            return [
                'success' => true,
                'message' => "{$typeName} işlemi başarılı.",
            ];
        }

        $errorMsg = $result && isset($result->ResultDetail) ? (string) $result->ResultDetail : '';

        return [
            'success' => false,
            'message' => "{$typeName} işlemi başarısız." . ($errorMsg ? " Hata: {$errorMsg}" : ''),
        ];
    }

    private function curl($xml)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://onlineodeme.vakifbank.com.tr:4443/VposService/v3/Vposreq.aspx');//https://onlineodemetest.vakifbank.com.tr:4443/VposService/v3/Vposreq.aspx
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'prmstr=' . $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 59);
        curl_setopt($ch, CURLOPT_SSL_OPTIONS, array("CURLOPT_SSLVERSION" => "CURL_SSLVERSION_TLSv1_1"));
        $result = curl_exec($ch);

        if ($result === false) {
            $error = curl_error($ch);
            logSession("Vakifbank VakifReq cURL Error", ['message' => $error], 'info', 'payment_logs');
        }

        curl_close($ch);

        logSession("Vakifbank VakifReq cURL result", $result, 'info', 'payment_logs');

        return $result;
    }
}

