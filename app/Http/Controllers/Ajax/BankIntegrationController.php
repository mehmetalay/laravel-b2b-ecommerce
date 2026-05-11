<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Models\BankIntegration;
use Illuminate\Http\JsonResponse;

class BankIntegrationController extends Controller
{
    public function getInstallments(int $bankIntegrationId): JsonResponse
    {
        $bankIntegration = BankIntegration::with('installments')->find($bankIntegrationId);

        return response()->json(
            $bankIntegration?->installments ?? []
        );
    }
}
