<?php

namespace App\Application\Payment\Services;

use App\Application\Payment\Enums\PaymentStatus;

class PaymentStateMachine
{
    public function resolveState(?string $status, ?string $refundStatus): ?PaymentStatus
    {
        $status = strtolower((string) $status);
        $refundStatus = strtolower((string) $refundStatus);

        if ($refundStatus === PaymentStatus::CANCELLED->value) {
            return PaymentStatus::CANCELLED;
        }

        if ($refundStatus !== '') {
            return PaymentStatus::REFUNDED;
        }

        return PaymentStatus::tryFrom($status);
    }

    public function canTransitionToSuccess(?PaymentStatus $currentState): bool
    {
        return in_array($currentState, [PaymentStatus::PENDING, PaymentStatus::FAILED, null], true);
    }

    public function isSuccessTerminal(?PaymentStatus $currentState): bool
    {
        return in_array(
            $currentState,
            [PaymentStatus::SUCCESS, PaymentStatus::REFUNDED, PaymentStatus::CANCELLED],
            true
        );
    }

    public function canTransitionToFailure(?PaymentStatus $currentState): bool
    {
        return in_array($currentState, [PaymentStatus::PENDING, null], true);
    }

    public function isFailureTerminal(?PaymentStatus $currentState): bool
    {
        return in_array(
            $currentState,
            [PaymentStatus::SUCCESS, PaymentStatus::REFUNDED, PaymentStatus::CANCELLED, PaymentStatus::FAILED],
            true
        );
    }

    public function isRefundAlreadyApplied(?PaymentStatus $currentState): bool
    {
        return in_array($currentState, [PaymentStatus::REFUNDED, PaymentStatus::CANCELLED], true);
    }

    public function canTransitionToRefund(?PaymentStatus $currentState): bool
    {
        return $currentState === PaymentStatus::SUCCESS;
    }

    public function resolveRefundStatus(array $payload): PaymentStatus
    {
        $value = strtolower((string) ($payload['refund_status'] ?? PaymentStatus::REFUNDED->value));

        return $value === PaymentStatus::CANCELLED->value
            ? PaymentStatus::CANCELLED
            : PaymentStatus::REFUNDED;
    }

    public function toStoredStatus(PaymentStatus $state): string
    {
        return match ($state) {
            PaymentStatus::PENDING => 'PENDING',
            PaymentStatus::SUCCESS => 'SUCCESS',
            PaymentStatus::FAILED => 'FAILED',
            PaymentStatus::REFUNDED, PaymentStatus::CANCELLED => 'SUCCESS',
            default => 'PENDING',
        };
    }
}
