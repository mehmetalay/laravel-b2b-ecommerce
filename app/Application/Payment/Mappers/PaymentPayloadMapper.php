<?php

namespace App\Application\Payment\Mappers;

use App\Application\Payment\DTO\PaymentCallbackRequest;
use App\Application\Payment\DTO\PaymentGatewayRequest;
use App\Application\Payment\DTO\PaymentRefundRequest;
use App\Application\Payment\DTO\PaymentRequestData;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\PaymentLink;
use App\Services\CurrencyService;
use InvalidArgumentException;

class PaymentPayloadMapper
{
    public function toPaymentCreatePayload(
        PaymentRequestData $dto,
        Installment $installment,
        array $userQuery,
        CurrencyService $currencyService,
        string $oid
    ): array {
        $commissionAmount = calculate_percentage($dto->amount, $installment->commission_rate);

        return [
            'plasiyer_id' => $userQuery['plasiyer_id'] ?? null,
            'user_id' => $userQuery['user_id'],
            'sub_dealer_id' => $userQuery['sub_dealer_id'] ?? null,
            'creator_type' => $userQuery['creator_type'],
            'oid' => $oid,
            'bank_integration_id' => $dto->bankIntegrationId,
            'entered_amount' => $dto->amount,
            'amount_paid' => $dto->amount + $commissionAmount,
            'installment' => $installment->installment,
            'plus_installment' => $installment->plus_installment,
            'commission_rate' => $installment->commission_rate,
            'commission_amount' => $commissionAmount,
            'card_name' => $dto->creditCardName,
            'card_number' => mask_card_number($dto->creditCardNumber),
            'explanation' => $dto->explanation,
            'phone_number' => $dto->phoneNumber,
            'ip_address' => $dto->ipAddress,
            'option_3d_payment' => $dto->option3DPaymentHidden,
            'usd_exchange_rate' => $currencyService->getFirstByCode('USD')->selling_price,
            'eur_exchange_rate' => $currencyService->getFirstByCode('EUR')->selling_price,
            'gbp_exchange_rate' => $currencyService->getFirstByCode('GBP')->selling_price,
            'erp_status' => 'pending',
        ];
    }

    public function toPaymentLinkUpdatePayload(
        PaymentRequestData $dto,
        Installment $installment,
        CurrencyService $currencyService,
        string $oid
    ): array {
        $commissionAmount = calculate_percentage($dto->amount, $installment->commission_rate);

        return [
            'oid' => $oid,
            'bank_integration_id' => $dto->bankIntegrationId,
            'entered_amount' => $dto->amount,
            'amount_paid' => $dto->amount + $commissionAmount,
            'installment' => $installment->installment,
            'plus_installment' => $installment->plus_installment,
            'commission_rate' => $installment->commission_rate,
            'commission_amount' => $commissionAmount,
            'card_name' => $dto->creditCardName,
            'card_number' => mask_card_number($dto->creditCardNumber),
            'explanation' => $dto->explanation,
            'phone_number' => $dto->phoneNumber,
            'ip_address' => $dto->ipAddress,
            'usd_exchange_rate' => $currencyService->getFirstByCode('USD')->selling_price,
            'eur_exchange_rate' => $currencyService->getFirstByCode('EUR')->selling_price,
            'gbp_exchange_rate' => $currencyService->getFirstByCode('GBP')->selling_price,
        ];
    }

    public function toGatewayRequest(
        PaymentRequestData $dto,
        Installment $installment,
        string $oid,
        string $okUrl,
        string $failUrl,
        ?int $paymentId = null,
        ?int $paymentLinkId = null
    ): PaymentGatewayRequest {
        $commissionAmount = calculate_percentage($dto->amount, $installment->commission_rate);
        $amount = $dto->amount + $commissionAmount;

        return new PaymentGatewayRequest(
            bankIntegrationId: $dto->bankIntegrationId,
            bankCode: strtolower(trim((string) ($installment->bankIntegration?->bank_code ?? ''))) ?: null,
            bankIntegrationInformation: json_decode($installment->bankIntegration->json),
            amount: $amount,
            oid: $oid,
            installment: (int) $installment->installment,
            creditCardName: $dto->creditCardName,
            creditCardNumber: $dto->creditCardNumber,
            creditCardExpMonth: (string) $dto->creditCardExpMonth,
            creditCardExpYear: (string) $dto->creditCardExpYear,
            cvc: (string) $dto->cvc,
            okUrl: $okUrl,
            failUrl: $failUrl,
            paymentId: $paymentId,
            paymentLinkId: $paymentLinkId
        );
    }

    public function toCallbackRequestFromPayment(Payment $payment, array $payload): PaymentCallbackRequest
    {
        return new PaymentCallbackRequest(
            bankIntegrationId: (int) $payment->bank_integration_id,
            bankCode: strtolower(trim((string) ($payment->bankIntegration?->bank_code ?? ''))) ?: null,
            bankIntegrationInformation: json_decode($payment->bankIntegration->json),
            payload: $payload,
            oid: (string) $payment->oid,
            amount: (float) $payment->amount_paid,
            okUrl: route('payment.response') . '?paymentId=' . $payment->id . '&akaPaymentStatus=successful',
            failUrl: route('payment.response') . '?paymentId=' . $payment->id . '&akaPaymentStatus=failed'
        );
    }

    public function toCallbackRequestFromPaymentLink(PaymentLink $paymentLink, array $payload): PaymentCallbackRequest
    {
        $paymentId = (int) ($payload['paymentId'] ?? ($paymentLink->current_payment_id ?? 0));
        $callbackQuery = '?paymentLinkId=' . $paymentLink->id;
        if ($paymentId > 0) {
            $callbackQuery .= '&paymentId=' . $paymentId;
        }

        return new PaymentCallbackRequest(
            bankIntegrationId: (int) $paymentLink->bank_integration_id,
            bankCode: strtolower(trim((string) ($paymentLink->bankIntegration?->bank_code ?? ''))) ?: null,
            bankIntegrationInformation: json_decode($paymentLink->bankIntegration->json),
            payload: $payload,
            oid: (string) $paymentLink->oid,
            amount: (float) $paymentLink->amount_paid,
            okUrl: route('payment-link.response') . $callbackQuery,
            failUrl: route('payment-link.response') . $callbackQuery
        );
    }

    /**
     * @param Payment|PaymentLink $model
     */
    public function toCallbackRequestFromModel($model, array $payload): PaymentCallbackRequest
    {
        if ($model instanceof Payment) {
            return $this->toCallbackRequestFromPayment($model, $payload);
        }

        if ($model instanceof PaymentLink) {
            return $this->toCallbackRequestFromPaymentLink($model, $payload);
        }

        throw new InvalidArgumentException('Unsupported callback model type.');
    }

    public function extractProviderFields(array $payload): array
    {
        $mapping = [
            'provider_reference' => ['provider_reference', 'TransactionId', 'transaction_id'],
            'provider_auth_code' => ['provider_auth_code', 'AuthCode', 'authcode', 'authCode'],
            'provider_rrn' => ['provider_rrn', 'HostRefNum', 'hostrefnum', 'rrn', 'Rrn'],
        ];

        $updateData = [];

        foreach ($mapping as $field => $keys) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $payload) && $payload[$key] !== null && $payload[$key] !== '') {
                    $updateData[$field] = $payload[$key];
                    break;
                }
            }
        }

        return $updateData;
    }

    /**
     * @param Payment|PaymentLink $model
     */
    public function toRefundRequest($model): PaymentRefundRequest
    {
        return new PaymentRefundRequest(
            bankIntegrationId: (int) $model->bank_integration_id,
            bankCode: strtolower(trim((string) ($model->bankIntegration?->bank_code ?? ''))) ?: null,
            bankIntegrationInformation: json_decode($model->bankIntegration->json),
            oid: (string) $model->oid,
            amount: (float) $model->amount_paid,
            providerReference: $model->provider_reference ?? null
        );
    }
}
