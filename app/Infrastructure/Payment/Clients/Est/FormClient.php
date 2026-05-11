<?php

namespace App\Infrastructure\Payment\Clients\Est;

class FormClient
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
        logSession("Est FormClient ödeme işlemi için form başlatıldı.", null, 'info', 'payment_logs');

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
            'storeKey' => $this->storeKey
        ];

        $hashString = implode('|', array_map(function($value) {
            return str_replace(['|', '\\'], ['\|', '\\\\'], $value);
        }, $params));

        return base64_encode(hash('sha512', $hashString, true));
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
            'hashAlgorithm' => "ver3",
            'islemtipi' => $this->transactionType,
            'taksit' => $this->installment,
            'storetype' => "3d_pay",
            'lang' => "tr",
            'currency' => "949",
            'creditcard_name' => $this->credit_card_name,
            'pan' => $this->credit_card_number,
            'Ecom_Payment_Card_ExpDate_Month' => $this->credit_card_exp_date_month,
            'Ecom_Payment_Card_ExpDate_Year' => $this->credit_card_exp_date_year,
            'cv2' => $this->cvc
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            logSession("Est FormClient ödeme formu oluşturma başarısız. cURL Error", ['message' => curl_error($ch)], 'info', 'payment_logs');
        } else {
            logSession("Est FormClient ödeme formu başarıyla oluşturuluyor.", null, 'info', 'payment_logs');
        }

        curl_close($ch);

        return $response;
    }
}

