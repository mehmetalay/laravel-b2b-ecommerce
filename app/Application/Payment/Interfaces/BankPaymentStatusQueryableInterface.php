<?php

namespace App\Application\Payment\Interfaces;

use App\Application\Payment\DTO\PaymentStatusQueryRequest;
use App\Application\Payment\DTO\PaymentStatusQueryResult;

interface BankPaymentStatusQueryableInterface
{
    public function checkStatus(PaymentStatusQueryRequest $request): PaymentStatusQueryResult;
}

