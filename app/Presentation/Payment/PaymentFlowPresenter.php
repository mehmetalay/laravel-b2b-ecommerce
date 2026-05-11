<?php

namespace App\Presentation\Payment;

use App\Application\Payment\DTO\PaymentFlowResult;

class PaymentFlowPresenter
{
    public function present(PaymentFlowResult $result)
    {
        return match ($result->type) {
            PaymentFlowResult::TYPE_JSON => response()->json($result->payload),
            PaymentFlowResult::TYPE_HTML => response(
                (string) ($result->payload['html'] ?? ''),
                200,
                ['Content-Type' => 'text/html; charset=UTF-8']
            ),
            PaymentFlowResult::TYPE_POST_MESSAGE => view('payments.post-message', $result->payload),
            default => response()->json(['error' => 'Unsupported presentation type.'], 500),
        };
    }
}

