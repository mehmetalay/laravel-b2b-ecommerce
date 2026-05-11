<?php

namespace App\Application\Payment\Actions;

use App\Application\Payment\Mappers\PaymentPayloadMapper;
use App\Application\Payment\Services\PaymentOrchestrationService;
use App\Application\Payment\Validators\PaymentRequestValidator;
use App\Application\Payment\DTO\PaymentRequestData;
use App\Models\Installment;
use App\Models\Payment;
use App\Services\CurrentAccountService;
use App\Services\CurrencyService;
use App\Application\Payment\Services\PaymentSensitiveDataMasker;
use App\Services\PaymentService;

class ProcessPaymentRequestAction
{
    public function __construct(
        private CurrencyService $currencyService,
        private PaymentService $paymentService,
        private PaymentOrchestrationService $paymentOrchestrationService,
        private PaymentPayloadMapper $paymentPayloadMapper,
        private PaymentRequestValidator $paymentRequestValidator,
        private CurrentAccountService $currentAccountService,
        private PaymentSensitiveDataMasker $paymentSensitiveDataMasker
    ) {}

    public function execute(PaymentRequestData $dto, array $userQuery)
    {
        logSession(
            'paymentRequest islemi basladi.',
            $this->paymentSensitiveDataMasker->mask([
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

        if ($bankAccessError = $this->validateBankAccessForCurrentAccount($installment)) {
            return \App\Application\Payment\DTO\PaymentFlowResult::json(['error' => $bankAccessError]);
        }

        $oid = virtual_pos_order_id($dto->bankIntegrationId);
        $payment = Payment::create(
            $this->paymentPayloadMapper->toPaymentCreatePayload(
                dto: $dto,
                installment: $installment,
                userQuery: $userQuery,
                currencyService: $this->currencyService,
                oid: $oid
            )
        );

        $gatewayRequest = $this->paymentPayloadMapper->toGatewayRequest(
            dto: $dto,
            installment: $installment,
            oid: $oid,
            okUrl: route('payment.response') . "?paymentId={$payment->id}&akaPaymentStatus=successful",
            failUrl: route('payment.response') . "?paymentId={$payment->id}&akaPaymentStatus=failed",
            paymentId: $payment->id
        );

        if ((int) $dto->option3DPaymentHidden === 1) {
            $result = $this->paymentOrchestrationService->start3D($gatewayRequest);

            if (!$result->success || empty($result->html)) {
                return \App\Application\Payment\DTO\PaymentFlowResult::postMessage(
                    messageType: 'error',
                    message: $result->message ?: 'Islem baslatilamadi. Lutfen tekrar deneyiniz.'
                );
            }

            return \App\Application\Payment\DTO\PaymentFlowResult::html((string) $result->html);
        }

        $result = $this->paymentOrchestrationService->startNon3D($gatewayRequest);

        if ($result->success) {
            $this->paymentService->handleSuccess($payment, $result->payload);

            return \App\Application\Payment\DTO\PaymentFlowResult::json([
                'without_3d' => true,
                'success' => $result->message ?: 'Odeme Islemi Basariyla Gerceklesti!',
            ]);
        }

        $this->paymentService->handleFailure($payment, array_merge($result->payload, [
            'failure_reason' => $result->message ?: 'Odeme Islemi Basarisiz.',
        ]));

        return \App\Application\Payment\DTO\PaymentFlowResult::json([
            'without_3d' => true,
            'error' => $result->message ?: 'Odeme Islemi Basarisiz.',
        ]);
    }

    private function validateBankAccessForCurrentAccount(Installment $installment): ?string
    {
        if (!$installment->relationLoaded('bankIntegration')) {
            $installment->load('bankIntegration');
        }

        $bankCompanyId = (int) ($installment->bankIntegration->company_id ?? 0);
        $currentAccount = $this->currentAccountService->currentAccount();
        $companyId = (int) ($currentAccount?->company_id ?: additional_setting('default_company_id'));

        if ($bankCompanyId > 0 && $companyId > 0 && $bankCompanyId !== $companyId) {
            return 'Secilen banka bu hesap icin kullanilamaz.';
        }

        return null;
    }
}
