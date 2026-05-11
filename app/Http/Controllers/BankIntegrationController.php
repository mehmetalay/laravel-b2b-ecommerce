<?php

namespace App\Http\Controllers;

use App\Application\Payment\Actions\{ProcessPaymentLinkRequestAction, ProcessPaymentLinkResponseAction, ProcessPaymentRequestAction, ProcessPaymentResponseAction};
use App\Application\Payment\DTO\{PaymentFlowResult, PaymentRequestData};
use App\Application\Mail\Services\PaymentLinkMailDispatchService;
use App\Application\Payment\Services\{PaymentLinkLifecycleService, PaymentLinkPaymentBindingService, PaymentOrchestrationService, PaymentCallbackSecurityService, PaymentEffectService, PaymentSensitiveDataMasker};
use App\Models\{Installment, Payment, PaymentLink};
use Illuminate\Support\Facades\{Auth, DB};
use App\Application\Payment\Mappers\PaymentPayloadMapper;
use App\Application\Payment\Validators\PaymentRequestValidator;
use App\Services\CurrentAccountService;
use App\Services\CurrencyService;
use App\Services\PaymentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Presentation\Payment\PaymentFlowPresenter;

class BankIntegrationController extends Controller
{
    public function __construct(
        private CurrencyService $currencyService,
        private PaymentService $paymentService,
        private PaymentEffectService $paymentEffectService,
        private PaymentCallbackSecurityService $paymentCallbackSecurityService,
        private PaymentSensitiveDataMasker $paymentSensitiveDataMasker,
        private PaymentOrchestrationService $paymentOrchestrationService,
        private PaymentPayloadMapper $paymentPayloadMapper,
        private PaymentRequestValidator $paymentRequestValidator,
        private PaymentLinkLifecycleService $paymentLinkLifecycleService,
        private PaymentLinkMailDispatchService $paymentLinkMailDispatchService,
        private PaymentLinkPaymentBindingService $paymentLinkPaymentBindingService,
        private ProcessPaymentRequestAction $processPaymentRequestAction,
        private ProcessPaymentResponseAction $processPaymentResponseAction,
        private ProcessPaymentLinkRequestAction $processPaymentLinkRequestAction,
        private ProcessPaymentLinkResponseAction $processPaymentLinkResponseAction,
        private PaymentFlowPresenter $paymentFlowPresenter
    ) {
        $this->middleware('auth:web,subdealer', ['except' => ['paymentResponse', 'paymentLinkRequest', 'paymentLinkResponse']]);
    }

    public function paymentRequest(Request $request, CurrentAccountService $currentAccountService)
    {
        if (!$this->useRefactoredFlow()) {
            return $this->legacyPaymentRequest($request, $currentAccountService);
        }

        try {
            $dto = PaymentRequestData::fromRequest($request);
            $userQuery = $currentAccountService->userQuery();

            $flowResult = $this->processPaymentRequestAction->execute($dto, $userQuery);

            return $this->paymentFlowPresenter->present($flowResult);
        } catch (\Throwable $e) {
            logException($e, 'BankIntegrationController::paymentRequest', true);
            return response()->json(['error' => 'Bir hata olustu. Lutfen tekrar deneyiniz.']);
        }
    }

    public function paymentResponse()
    {
        if (!$this->useRefactoredFlow()) {
            return $this->legacyPaymentResponse();
        }

        try {
            $flowResult = $this->processPaymentResponseAction->execute(request()->all());
            return $this->paymentFlowPresenter->present($flowResult);
        } catch (\Throwable $e) {
            logException($e, 'BankIntegrationController::paymentResponse', true);

            return $this->paymentFlowPresenter->present(
                PaymentFlowResult::postMessage('error', 'Odeme durumunuz kisa sure icinde kontrol edilerek guncellenecektir.')
            );
        }
    }

    public function paymentLinkRequest(Request $request, $token = null)
    {
        if (!$this->useRefactoredFlow()) {
            return $this->legacyPaymentLinkRequest($request, $token);
        }

        try {
            $dto = PaymentRequestData::fromRequest($request);
            $flowResult = $this->processPaymentLinkRequestAction->execute((string) $token, $dto);

            return $this->paymentFlowPresenter->present($flowResult);
        } catch (\Throwable $e) {
            if ($e instanceof ModelNotFoundException) {
                throw $e;
            }

            logException($e, 'BankIntegrationController::paymentLinkRequest', true);

            return response()->json(['error' => 'Bir hata olustu. Lutfen tekrar deneyiniz.']);
        }
    }

    public function paymentLinkResponse()
    {
        if (!$this->useRefactoredFlow()) {
            return $this->legacyPaymentLinkResponse();
        }

        try {
            $flowResult = $this->processPaymentLinkResponseAction->execute(
                data: request()->all(),
                isAuthenticated: Auth::check()
            );
            return $this->paymentFlowPresenter->present($flowResult);
        } catch (\Throwable $e) {
            logException($e, 'BankIntegrationController::paymentLinkResponse', true);

            return $this->paymentFlowPresenter->present(
                PaymentFlowResult::postMessage('error', 'Odeme durumunuz kisa sure icinde kontrol edilecektir.')
            );
        }
    }

    private function legacyPaymentRequest(Request $request, CurrentAccountService $currentAccountService)
    {
        try {
            $userQuery = $currentAccountService->userQuery();
            $dto = PaymentRequestData::fromRequest($request);
            $maskedRequestData = $this->paymentSensitiveDataMasker->mask([
                'bank_integration_id' => $dto->bankIntegrationId,
                'installment_id' => $dto->installmentId,
                'amount' => $dto->amount,
                'credit_card_number' => $dto->creditCardNumber,
                'cvc' => $dto->cvc,
                'credit_card_exp_month' => $dto->creditCardExpMonth,
                'credit_card_exp_year' => $dto->creditCardExpYear,
            ]);
            logSession('paymentRequest islemi basladi.', $maskedRequestData, 'info', 'payment_logs');

            if ($validationError = $this->paymentRequestValidator->validate($dto)) {
                return response()->json(['error' => $validationError]);
            }

            $installment = Installment::with('bankIntegration')->find($dto->installmentId);
            if (!$installment) {
                return response()->json(['error' => 'Taksit bilgisi bulunamadi. Lutfen tekrar deneyiniz.']);
            }

            if ($bindingError = $this->paymentRequestValidator->validateInstallmentBinding($installment, $dto->bankIntegrationId)) {
                return response()->json(['error' => $bindingError]);
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
                    return view('payments.post-message', [
                        'type' => 'error',
                        'message' => $result->message ?: 'Islem baslatilamadi. Lutfen tekrar deneyiniz.',
                    ]);
                }

                return response(
                    (string) $result->html,
                    200,
                    ['Content-Type' => 'text/html; charset=UTF-8']
                );
            }

            $result = $this->paymentOrchestrationService->startNon3D($gatewayRequest);

            if ($result->success) {
                $this->paymentService->handleSuccess($payment, $result->payload);

                return response()->json([
                    'without_3d' => true,
                    'success' => $result->message ?: 'Odeme Islemi Basariyla Gerceklesti!',
                ]);
            }

            $this->paymentService->handleFailure($payment, array_merge($result->payload, [
                'failure_reason' => $result->message ?: 'Odeme Islemi Basarisiz.',
            ]));

            return response()->json([
                'without_3d' => true,
                'error' => $result->message ?: 'Odeme Islemi Basarisiz.',
            ]);
        } catch (\Throwable $e) {
            logException($e, 'BankIntegrationController::paymentRequest', true);
            return response()->json(['error' => 'Bir hata olustu. Lutfen tekrar deneyiniz.']);
        }
    }

    private function legacyPaymentResponse()
    {
        try {
            $data = request()->all();
            $paymentId = (int) ($data['paymentId'] ?? 0);
            $maskedData = $this->paymentSensitiveDataMasker->mask($data);

            logSession("paymentResponse islemi basladi. paymentId {$paymentId} response", $maskedData, 'info', 'payment_logs');

            $payment = Payment::find($paymentId);
            if (!$payment) {
                logSession("paymentResponse payment bulunamadi. paymentId {$paymentId}", $maskedData, 'error', 'payment_logs');

                return view('payments.post-message', [
                    'type' => 'error',
                    'message' => 'Odeme kaydi bulunamadi.',
                ]);
            }

            if (!$this->paymentCallbackSecurityService->verifyPaymentSignature($payment, $data)) {
                logSession(
                    "paymentResponse invalid signature. paymentId {$paymentId}",
                    [
                        'payment_id' => $paymentId,
                        'payload' => $maskedData,
                    ],
                    'error',
                    'payment_logs'
                );

                return view('payments.post-message', [
                    'type' => 'error',
                    'message' => 'Gecersiz callback imzasi.',
                ]);
            }

            $callbackRequest = $this->paymentPayloadMapper->toCallbackRequestFromPayment($payment, $data);
            $callbackResult = $this->paymentOrchestrationService->resolveCallback($callbackRequest);
            $bankIntegrationName = $payment->bankIntegration->full_name;

            if ($callbackResult->success) {
                return $this->handleSuccess(
                    model: $payment,
                    type: 'payment',
                    bankIntegrationName: $bankIntegrationName,
                    payload: array_merge($data, $callbackResult->payload)
                );
            }

            return $this->handleError(
                model: $payment,
                message: $callbackResult->message ?: 'Odeme Islemi Basarisiz.',
                type: 'payment',
                bankIntegrationName: $bankIntegrationName,
                payload: array_merge($data, $callbackResult->payload)
            );
        } catch (\Throwable $e) {
            logException($e, 'BankIntegrationController::paymentResponse', true);

            return view('payments.post-message', [
                'type' => 'error',
                'message' => 'Odeme durumunuz kisa sure icinde kontrol edilerek guncellenecektir.',
            ]);
        }
    }

    private function legacyPaymentLinkRequest(Request $request, string $token)
    {
        try {
            $dto = PaymentRequestData::fromRequest($request);
            $maskedRequestData = $this->paymentSensitiveDataMasker->mask([
                'token' => $token,
                'bank_integration_id' => $dto->bankIntegrationId,
                'installment_id' => $dto->installmentId,
                'amount' => $dto->amount,
                'credit_card_number' => $dto->creditCardNumber,
                'cvc' => $dto->cvc,
                'credit_card_exp_month' => $dto->creditCardExpMonth,
                'credit_card_exp_year' => $dto->creditCardExpYear,
            ]);
            logSession("paymentLinkRequest islemi basladi. token: {$token}", $maskedRequestData, 'info', 'payment_logs');

            if ($validationError = $this->paymentRequestValidator->validate($dto)) {
                return response()->json(['error' => $validationError]);
            }

            $installment = Installment::with('bankIntegration')->find($dto->installmentId);
            if (!$installment) {
                return response()->json(['error' => 'Taksit bilgisi bulunamadi. Lutfen tekrar deneyiniz.']);
            }

            if ($bindingError = $this->paymentRequestValidator->validateInstallmentBinding($installment, $dto->bankIntegrationId)) {
                return response()->json(['error' => $bindingError]);
            }

            $gatewayRequest = null;

            DB::transaction(function () use ($token, $dto, $installment, &$gatewayRequest) {
                $paymentLink = PaymentLink::query()
                    ->where('token', $token)
                    ->active()
                    ->where('is_paid', 0)
                    ->lockForUpdate()
                    ->first() ?? abort(404);

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

            if ($gatewayRequest === null) {
                return response()->json(['error' => 'Islem baslatilamadi. Lutfen tekrar deneyiniz.']);
            }

            $result = $this->paymentOrchestrationService->start3D($gatewayRequest);

            if (!$result->success || empty($result->html)) {
                return view('payments.post-message', [
                    'type' => 'error',
                    'message' => $result->message ?: 'Islem baslatilamadi. Lutfen tekrar deneyiniz.',
                ]);
            }

            return response(
                (string) $result->html,
                200,
                ['Content-Type' => 'text/html; charset=UTF-8']
            );
        } catch (\Throwable $e) {
            logException($e, 'BankIntegrationController::paymentLinkRequest', true);

            return response()->json(['error' => 'Bir hata olustu. Lutfen tekrar deneyiniz.']);
        }
    }

    private function legacyPaymentLinkResponse()
    {
        try {
            $data = request()->all();
            $paymentLinkId = (int) ($data['paymentLinkId'] ?? 0);
            $maskedData = $this->paymentSensitiveDataMasker->mask($data);

            logSession("paymentLinkResponse islemi basladi. paymentLinkId {$paymentLinkId} response", $maskedData, 'info', 'payment_logs');

            $paymentLink = PaymentLink::find($paymentLinkId);
            if (!$paymentLink) {
                logSession("paymentLinkResponse paymentLink bulunamadi. paymentLinkId {$paymentLinkId}", $maskedData, 'error', 'payment_logs');

                return view('payments.post-message', [
                    'type' => 'error',
                    'message' => 'Odeme link kaydi bulunamadi.',
                ]);
            }

            if (!$this->paymentCallbackSecurityService->verifyPaymentLinkSignature($paymentLink, $data)) {
                logSession(
                    "paymentLinkResponse invalid signature. paymentLinkId {$paymentLinkId}",
                    [
                        'payment_link_id' => $paymentLinkId,
                        'payload' => $maskedData,
                    ],
                    'error',
                    'payment_logs'
                );

                return view('payments.post-message', [
                    'type' => 'error',
                    'message' => 'Gecersiz callback imzasi.',
                ]);
            }

            $callbackRequest = $this->paymentPayloadMapper->toCallbackRequestFromPaymentLink($paymentLink, $data);
            $callbackResult = $this->paymentOrchestrationService->resolveCallback($callbackRequest);
            $bankIntegrationName = $paymentLink->bankIntegration->full_name;
            $this->paymentLinkPaymentBindingService->bindFromCallback($paymentLink, $data, false);

            if ($callbackResult->success) {
                return $this->handleSuccess(
                    model: $paymentLink,
                    type: 'paymentLink',
                    bankIntegrationName: $bankIntegrationName,
                    payload: array_merge($data, $callbackResult->payload)
                );
            }

            return $this->handleError(
                model: $paymentLink,
                message: $callbackResult->message ?: 'Odeme Islemi Basarisiz.',
                type: 'paymentLink',
                bankIntegrationName: $bankIntegrationName,
                payload: array_merge($data, $callbackResult->payload)
            );
        } catch (\Throwable $e) {
            logException($e, 'BankIntegrationController::paymentLinkResponse', true);

            return view('payments.post-message', [
                'type' => 'error',
                'message' => 'Odeme durumunuz kisa sure icinde kontrol edilecektir.',
            ]);
        }
    }

    private function useRefactoredFlow(): bool
    {
        return filter_var(env('PAYMENT_REFACTORED_BANK_CONTROLLER', false), FILTER_VALIDATE_BOOL);
    }

    private function handleSuccess($model, string $type, string $bankIntegrationName, array $payload = [])
    {
        $message = 'Odeme Islemi Basariyla Gerceklesti!';

        if ($type === 'payment') {
            $transition = $this->paymentService->applySuccessTransition($model, $payload);
            $model = $transition['payment'];
            $statusTransitioned = (bool) ($transition['transitioned'] ?? false);
            $url = route('index');
            $message .= $this->paymentEffectService->runSuccessEffects($model, $statusTransitioned);
        } else {
            $hasPaymentId = (int) ($payload['paymentId'] ?? 0) > 0;
            $linkedPayment = $this->paymentLinkLifecycleService->handleSuccess($model, $payload);
            $url = Auth::check() ? route('index') : route('login.form');

            if (!$hasPaymentId || $linkedPayment !== null) {
                if ($this->paymentLinkMailDispatchService->sendPaymentSuccessMail($model)) {
                    logSession("Mail gönderimi başarılı. paymentLinkId {$model->id}", null, 'info', 'payment_logs');
                    $message .= optional($model->user)->receipt_enabled ? 'Tahsilat makbuzu e-posta adresinize gonderilmistir.' : '';
                } else {
                    logSession("Mail gönderimi başarısız. paymentLinkId {$model->id}", null, 'info', 'payment_logs');
                }
            }
        }

        logSession("{$bankIntegrationName} {$type}Id: {$model->id} odeme basarili.", null, 'info', 'payment_logs');

        return view('payments.post-message', [
            'type' => 'success',
            'message' => $message,
            'url' => $url,
        ]);
    }

    private function handleError($model, string $message, string $type, string $bankIntegrationName, array $payload = [])
    {
        if ($type === 'payment') {
            $transition = $this->paymentService->applyFailureTransition($model, array_merge($payload, [
                'failure_reason' => $message,
            ]));
            $model = $transition['payment'];
        } else {
            $this->paymentLinkLifecycleService->handleFailure($model, $message, $payload);
        }

        logSession("{$bankIntegrationName} {$type}Id: {$model->id} odeme basarisiz: {$message}", null, 'info', 'payment_logs');

        return view('payments.post-message', [
            'type' => 'error',
            'message' => $message,
        ]);
    }
}
