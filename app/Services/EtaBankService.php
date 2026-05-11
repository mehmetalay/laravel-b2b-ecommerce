<?php

namespace App\Services;

class EtaBankService
{
    public function __construct(
        private EtaService $etaService,
        private PaymentService $paymentService
    ) {}

    public function sendPosTransaction($paymentId)
    {
        $lockService = null;
        $lockAcquired = false;

        try {
            $lockService = new BatchLockService('EtaBankService::sendPosTransaction:' . $paymentId, 60);

            $lockAcquired = $lockService->acquire();
            if (! $lockAcquired) {
                return response('İşlem hala devam ediyor.', 409);
            }

            if (! $this->paymentService->markAsProcessing($paymentId)) {
                return;
            }

            logSession("PaymentID: {$paymentId} | ETA sendPosTransaction started", '', 'info', 'eta_pos_transaction_logs');

            $payment = $this->paymentService->getFirst($paymentId);

            $payload = [
                "refNo" => [
                    "harrefmodul" => 39,
                    "harrefkonu" => 1,
                    "harrefdeger" => 25,
                ],
                "fis" => [
                    "banfisrefno" => 0,
                    "banfismuhrefno" => 0,
                    "banfistar" => $payment->eta_created_at,
                    "banfistipi" => 4,
                    "banfiskayonc" => 2,
                    "banfiskaynak" => 39,
                    "banfisgcflag" => 1,
                    "banfisheskod" => $payment->eta_bank_code,
                    "banfishesadi" => $payment->eta_bank_name,
                    "banfisozkoD1" => "eyl",
                    "banfisozkoD2" => "",
                    "banfisozkoD3" => "",
                    "banfisaciklamA1" => $payment->eta_card_holder_info5,
                    "banfisaciklamA2"  => $payment->eta_card_holder_info,
                    "banfisaciklamA3" => "POS Tahsilat",
                    "banfisevraknO1" => "",
                    "banfisevraknO2" => $payment->eta_payment_number,
                    "banfisevraknO3" => "",
                    "banfisdovtar" => $payment->eta_empty_date,
                    "banfisborctop" => $payment->eta_withdrawal_amount,
                    "banfisdovborctop" => 0,
                    "banfishazkod" => "EY",
                    "banfiskontkod" => "EY",
                    "banfisonaykod" => "EY",
                    "banfissevno" => 1,
                    "banfisisykod" => "",
                    "banfisdovkod" => "",
                    "banfisdovtur" => "",
                    "banfisdovkur" => 0,
                    "banfiskartdovtop" => 0,
                ],
                "hareketler" => [
                    [
                        "banharrefno" => 0,
                        "banhartar" => $payment->eta_created_at,
                        "banhartipi" => 4,
                        "banharkayonc" => 2,
                        "banharkaynak" => 39,
                        "banhargcflag" => 1,
                        "banharheskod" => $payment->eta_bank_code,
                        "banharhesadi" => $payment->eta_bank_name,
                        "banharsirano" => 1,
                        "banharistipno" => 8,
                        "banharistipkod" => "KREDİ KART",
                        "banharkartip" => 1,
                        "banharkarkod" => $payment->eta_account_code,
                        "banharkarunvan" => $payment->eta_account_name,
                        "banharkarmuhkod" => "",
                        "banharmuhadi" => "",
                        "banharevrakno" => "",
                        "banharaciklama" => $payment->eta_explanation ? str_limit($payment->eta_explanation, 77, '...') : '',
                        "banharaciklamA1" => $payment->eta_card_holder_info2,
                        "banharaciklamA2" => $payment->eta_card_holder_info3,
                        "banharaciklamA3" => $payment->eta_card_holder_info4,
                        "carharaciklma1" => $payment->eta_card_holder_info2,//
                        "carharaciklma2" => $payment->eta_card_holder_info3,//
                        "carharaciklma3" => $payment->eta_card_holder_info4,//
                        "banhartutar" => $payment->eta_withdrawal_amount,
                        "banhardovkod" => "",
                        "banhardovtur" => "",
                        "banhardovtutar" => 0,
                        "banharvadetar" => $payment->eta_created_at,
                        "banharozkod" => "B2B",
                        "banharodendi" => 1,
                        "banhardovkoD2" => "",
                        "banhardovtuR2" => "",
                        "banhardovtutaR2" => 0,
                        "banhardovkur" => 0,
                        "banhardovkuR2" => 0,
                    ]
                ],
                "mizandeğer" => [
                    [
                        "banhesraktip" => 1,
                        "banheskod" => $payment->eta_bank_code,
                        "banhesyil" => $payment->eta_year,
                        "banhesay" => $payment->eta_month,
                        "banhesdovkod" => "",
                        "banhesborc" => $payment->eta_withdrawal_amount,
                        "banhesalacak" => 0,
                        "banhesisltip" => 6,
                    ],
                    [
                        "banhesraktip" => 2,
                        "banheskod" => $payment->eta_bank_code,
                        "banhesyil" => $payment->eta_year,
                        "banhesay" => $payment->eta_month,
                        "banhesdovkod" => "",
                        "banhesborc" => 0,
                        "banhesalacak" => 0,
                        "banhesisltip" => 6,
                    ]
                ]
            ];

            // $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
            // dd($json);

            logSession("PaymentID: {$paymentId} | ETA Pos Transaction params", [
                'payload' => $payload,
            ], 'info', 'eta_pos_transaction_logs');

            $response = $this->etaService->importPosTransaction($payload);

            logSession("PaymentID: {$paymentId} | Eta API response", ['response' => $response], 'info', 'eta_pos_transaction_logs');

            if (
                is_array($response) &&
                data_get($response, 'evrakNo')
            ) {
                $this->paymentService->markAsSent($paymentId, $response['evrakNo']);
            } else {
                logSession("PaymentID: {$paymentId} | Eta API response", ['response' => $response], 'error', 'eta_pos_transaction_logs');
                $this->paymentService->markAsFailed($paymentId, 'Eta API response is not valid');
            }

            // return $response;
        } catch (\Throwable $e) {
            logSession('Eta API exception', ['error' => $e->getMessage()], 'error', 'eta_pos_transaction_logs');
            $this->paymentService->markAsFailed($paymentId, $e->getMessage());
            return;
        } finally {
            if ($lockService && $lockAcquired) {
                $lockService->release();
            }
        }
    }
}

?>
