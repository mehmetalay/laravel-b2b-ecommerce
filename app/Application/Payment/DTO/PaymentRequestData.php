<?php

namespace App\Application\Payment\DTO;

use Illuminate\Http\Request;

class PaymentRequestData
{
    public $creditCardName;
    public $creditCardNumber;
    public $creditCardExpMonth;
    public $creditCardExpYear;
    public $cvc;
    public $amount;
    public $bankIntegrationId;
    public $installmentId;
    public $phoneNumber;
    public $explanation;
    public $option3DPaymentHidden;
    public $ipAddress;

    public function __construct($creditCardName, $creditCardNumber, $creditCardExpMonth, $creditCardExpYear, $cvc, $amount, $bankIntegrationId, $installmentId, $phoneNumber, $explanation, $option3DPaymentHidden, $ipAddress)
    {
        $this->creditCardName = $creditCardName;
        $this->creditCardNumber = $creditCardNumber;
        $this->creditCardExpMonth = $creditCardExpMonth;
        $this->creditCardExpYear = $creditCardExpYear;
        $this->cvc = $cvc;
        $this->amount = $amount;
        $this->bankIntegrationId = $bankIntegrationId;
        $this->installmentId = $installmentId;
        $this->phoneNumber = $phoneNumber;
        $this->explanation = $explanation;
        $this->option3DPaymentHidden = $option3DPaymentHidden;
        $this->ipAddress = $ipAddress;
    }

    public static function fromRequest(Request $request)
    {
        $expDate = (string) $request->input('credit_card_exp_date', '');
        [$month, $year] = array_pad(explode('/', $expDate, 2), 2, '');
        $month = preg_replace('/\D+/', '', (string) $month);
        $year = preg_replace('/\D+/', '', (string) $year);

        return new self(
            mb_strtoupper((string) $request->input('credit_card_name', ''), 'utf-8'),
            str_replace(' ', '', (string) $request->input('credit_card_number', '')),
            $month,
            $year,
            (string) $request->input('cvc', ''),
            self::normalizeAmount((string) $request->input('amount', '0')),
            (int) $request->input('bank_integration_id'),
            (int) $request->input('installment_id'),
            sanitize_phone_number($request->input('phone_number')),
            $request->input('explanation'),
            $request->boolean('option_3d_payment_hidden') ? 1 : 0,
            $request->ip()
        );
    }

    private static function normalizeAmount(string $raw): float
    {
        $value = trim(str_replace(' ', '', $raw));
        if ($value === '') {
            return 0.0;
        }

        $commaPos = strrpos($value, ',');
        $dotPos = strrpos($value, '.');

        if ($commaPos !== false && $dotPos !== false) {
            if ($commaPos > $dotPos) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif ($commaPos !== false) {
            $value = str_replace(',', '.', $value);
        }

        return (float) $value;
    }
}
