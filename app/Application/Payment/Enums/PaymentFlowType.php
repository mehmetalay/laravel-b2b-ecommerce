<?php

namespace App\Application\Payment\Enums;

enum PaymentFlowType: string
{
    case PAYMENT = 'payment';
    case PAYMENT_LINK = 'paymentLink';

    public function requestIdKey(): string
    {
        return match ($this) {
            self::PAYMENT => 'paymentId',
            self::PAYMENT_LINK => 'paymentLinkId',
        };
    }

    public function contextIdKey(): string
    {
        return match ($this) {
            self::PAYMENT => 'payment_id',
            self::PAYMENT_LINK => 'payment_link_id',
        };
    }

    public function notFoundMessage(): string
    {
        return match ($this) {
            self::PAYMENT => 'Odeme kaydi bulunamadi.',
            self::PAYMENT_LINK => 'Odeme link kaydi bulunamadi.',
        };
    }
}
