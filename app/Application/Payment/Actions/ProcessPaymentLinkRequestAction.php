<?php

namespace App\Application\Payment\Actions;

use App\Application\Payment\Mappers\PaymentPayloadMapper;
use App\Application\Payment\Services\PaymentOrchestrationService;
use App\Application\Payment\Validators\PaymentRequestValidator;
use App\Application\Payment\DTO\PaymentRequestData;
use App\Models\BankIntegration;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\PaymentLink;
use App\Services\CurrencyService;
use App\Application\Payment\Services\PaymentSensitiveDataMasker;
use Illuminate\Support\Facades\DB;

class ProcessPaymentLinkRequestAction
{
    public function __construct(
        private CurrencyService $currencyService,
        private PaymentOrchestrationService $paymentOrchestrationService,
        private PaymentPayloadMapper $paymentPayloadMapper,
        private PaymentRequestValidator $paymentRequestValidator,
        private PaymentSensitiveDataMasker $paymentSensitiveDataMasker
    ) {}

    public function execute(string $token, PaymentRequestData $dto)
    {
        logSession(
            "paymentLinkRequest islemi basladi. token: {$token}",
            $this->paymentSensitiveDataMasker->mask([
                'token' => $token,
                'bank_integration_id' => $dto->bankIntegrationId,
                'installment_id' => $dto->installmentId,
                'amount' => $dto->amount,
                'credit_card_number' => $dto->creditCardNumber,
                'cvc' => $dto->cvc,
                'credit_card_exp_month' => $dto->creditCardExpMonth,
                'credit_card_exp_year' => $dto->creditCardExpYear,
            ]),
            'info',
            'payment_logs'
        );

        if ($validationError = $this->paymentRequestValidator->validate($dto)) {
            return \App\Application\Payment\DTO\PaymentFlowResult::json(['error' => $validationError]);
        }

        $installment = Installment::with('bankIntegration')->find($dto->installmentId);
        if (!$installment) {
            return \App\Application\Payment\DTO\PaymentFlowResult::json(['error' => 'Taksit bilgisi bulunamadi. Lutfen tekrar deneyiniz.']);
        }

        if ($bindingError = $this->paymentRequestValidator->validateInstallmentBinding($installment, $dto->bankIntegrationId)) {
            return \App\Application\Payment\DTO\PaymentFlowResult::json(['error' => $bindingError]);
        }

        $gatewayRequest = null;

        try {
            DB::transaction(function () use ($token, $dto, $installment, &$gatewayRequest) {
                $paymentLink = PaymentLink::query()
                    ->where('token', $token)
                    ->active()
                    ->where('is_paid', 0)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($paymentLinkValidationError = $this->validatePaymentLinkConstraints($paymentLink, $dto, $installment)) {
                    throw new \RuntimeException($paymentLinkValidationError);
                }

                $oid = virtual_pos_order_id($dto->bankIntegrationId);

                $paymentLink->update(
                    $this->paymentPayloadMapper->toPaymentLinkUpdatePayload(
                        dto: $dto,
                        installment: $installment,
                        currencyService: $this->currencyService,
                        oid: $oid
                    )
                );

                $userQuery = [
                    'plasiyer_id' => $paymentLink->plasiyer_id,
                    'user_id' => $paymentLink->user_id,
                    'sub_dealer_id' => null,
                    'creator_type' => $paymentLink->plasiyer_id ? 'salesman' : 'dealer',
                ];

                $payment = Payment::query()->create(array_merge(
                    $this->paymentPayloadMapper->toPaymentCreatePayload(
                        dto: $dto,
                        installment: $installment,
                        userQuery: $userQuery,
                        currencyService: $this->currencyService,
                        oid: $oid
                    ),
                    [
                        'payment_link_id' => $paymentLink->id,
                    ]
                ));

                $paymentLink->update([
                    'current_payment_id' => $payment->id,
                ]);

                $gatewayRequest = $this->paymentPayloadMapper->toGatewayRequest(
                    dto: $dto,
                    installment: $installment,
                    oid: $oid,
                    okUrl: route('payment-link.response') . '?paymentLinkId=' . $paymentLink->id . '&paymentId=' . $payment->id,
                    failUrl: route('payment-link.response') . '?paymentLinkId=' . $paymentLink->id . '&paymentId=' . $payment->id,
                    paymentLinkId: $paymentLink->id,
                    paymentId: $payment->id
                );
            }, 3);
        } catch (\RuntimeException $e) {
            return \App\Application\Payment\DTO\PaymentFlowResult::json([
                'error' => $e->getMessage(),
            ]);
        }

        if ($gatewayRequest === null) {
            return \App\Application\Payment\DTO\PaymentFlowResult::json([
                'error' => 'Islem baslatilamadi. Lutfen tekrar deneyiniz.',
            ]);
        }

        $result = $this->paymentOrchestrationService->start3D($gatewayRequest);

        if (!$result->success || empty($result->html)) {
            return \App\Application\Payment\DTO\PaymentFlowResult::postMessage(
                messageType: 'error',
                message: $result->message ?: 'Islem baslatilamadi. Lutfen tekrar deneyiniz.'
            );
        }

        return \App\Application\Payment\DTO\PaymentFlowResult::html((string) $result->html);
    }

    private function validatePaymentLinkConstraints(
        PaymentLink $paymentLink,
        PaymentRequestData $dto,
        Installment $installment
    ): ?string {
        if ((int) $paymentLink->is_paid === 1 || (int) $paymentLink->status !== 1) {
            return 'Bu odeme linki kullanilamaz.';
        }

        if ((int) $paymentLink->amount_locked === 1) {
            $expectedAmount = round((float) $paymentLink->amount, 2);
            $incomingAmount = round((float) $dto->amount, 2);

            if ($incomingAmount !== $expectedAmount) {
                return 'Bu odeme linkinde tutar degistirilemez.';
            }
        }

        $transactionType = (int) $paymentLink->transaction_type;
        if ($transactionType === 2) {
            $automaticSinglePaymentId = (int) BankIntegration::query()
                ->where('automatic_single_payment', 1)
                ->value('id');

            if ($automaticSinglePaymentId <= 0 || (int) $dto->bankIntegrationId !== $automaticSinglePaymentId) {
                return 'Bu odeme linkinde banka secimi degistirilemez.';
            }

            if ((int) $installment->installment !== 1) {
                return 'Bu odeme linkinde taksit secimi degistirilemez.';
            }
        }

        if ($transactionType === 3 && (int) $paymentLink->manual_lock_bank_installment === 1) {
            if ((int) $paymentLink->manual_bank_integration_id !== (int) $dto->bankIntegrationId) {
                return 'Bu odeme linkinde banka secimi degistirilemez.';
            }

            if ((int) $paymentLink->manual_installment > 0 && (int) $installment->installment !== (int) $paymentLink->manual_installment) {
                return 'Bu odeme linkinde taksit secimi degistirilemez.';
            }
        }

        return null;
    }
}
