<?php

namespace App\Infrastructure\Payment\Clients\Vakifbank;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vakifbank extends Model
{
    use HasFactory;

    private $data;
    private $action;
    private $isyeriNo;
    private $terminalNo;
    private $isyeriSifre;
    private $amount;
    private $installment;
    private $okUrl;
    private $failUrl;
    private $oid;
    private $rnd;

    public function __construct($data)
    {
        $this->data = $data;
        $this->action = 'https://onlineodeme.vakifbank.com.tr:4443/VposService/v3/Vposreq.aspx';
        $this->isyeriNo = $this->data['bank_integration_information']->IsyeriNo;
        $this->terminalNo = $this->data['bank_integration_information']->TerminalNo;
        $this->isyeriSifre = $this->data['bank_integration_information']->IsyeriSifre;
        $this->amount = round($this->data['amount'], 2);
        $this->installment = $this->data['installment'] == 1 ? null : $this->data['installment'];
        $this->okUrl = $this->data['ok_url'];
        $this->failUrl = $this->data['fail_url'];
        $this->oid = $this->data['oid'];
        $this->rnd = uniqid();

        $this->creditcard_name = $this->data['credit_card_name'];
        $this->creditcard_number = $this->data['credit_card_number'];
        $this->exp_date_month = $this->data['credit_card_exp_date_month'];
        $this->exp_date_year = $this->data['credit_card_exp_date_year'];
        $this->cvv = $this->data['cvc'];
    }

    public function posXml()
    {
        $data = (new VakifReq())
            ->setMerchantID($this->isyeriNo)
            ->setMerchantPassword($this->isyeriSifre)
            ->setCurrency()
            ->setSuccessURL($this->okUrl)
            ->setFailureURL($this->failUrl)
            ->setPan($this->creditcard_number)
            ->setExpiryDate($this->exp_date_year.$this->exp_date_month)
            ->setPurchaseAmount($this->amount);
            if ($this->installment != null) {
                $data = $data->setInstallmentCount($this->installment);
            }
            $data = $data->setVerifyEnrollmentRequestID($this->oid)
        ->check();

        return $data;
    }
}

