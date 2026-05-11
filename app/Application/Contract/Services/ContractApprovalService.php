<?php

namespace App\Application\Contract\Services;

use App\Application\Contract\Enums\ContractSignatureStatus;
use App\Application\Contract\Events\ContractApprovalFailed;
use App\Application\Contract\Events\ContractApproved;
use App\Application\Contract\Exceptions\ContractSmsValidationException;
use App\Application\Contract\Repositories\ContractSignatureRepository;
use App\Application\Contract\Validators\ContractSmsCodeValidator;
use App\Models\ContractTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class ContractApprovalService
{
    public function __construct(
        private ContractSmsCodeValidator $contractSmsCodeValidator,
        private ContractSignatureRepository $contractSignatureRepository,
        private ContractSmsVerificationService $contractSmsVerificationService,
        private ContractPdfService $contractPdfService
    ) {}

    public function approve(array $actorContext, ContractTemplate $template, array $input): void
    {
        try {
            $validator = $this->contractSmsCodeValidator->validate($input);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $signature = $this->contractSignatureRepository->findByContext(
                (int) $actorContext['contract_user_id'],
                $actorContext['type']->value,
                (int) $template->id
            );

            if (!$signature) {
                throw new ContractSmsValidationException(trans('translations.contract_controller.onay_kodu_hatali'));
            }

            if ((string) $signature->status === ContractSignatureStatus::VERIFIED->value) {
                $this->audit('info', 'Contract approve idempotent hit', [
                    'signature_id' => $signature->id,
                    'actor_type' => $actorContext['type']->value,
                    'actor_id' => $actorContext['route_actor_id'],
                ]);

                return;
            }

            $smsCode = (string) ($input['sms_code'] ?? '');
            $this->contractSmsVerificationService->validateSmsCodeOrFail($signature, $smsCode);

            $signature->update([
                'sms_code' => null,
                'status' => ContractSignatureStatus::VERIFIED->value,
                'signed_at' => now(),
                'ip_address' => request()->ip(),
            ]);

            $this->contractSmsVerificationService->clearVerificationState($signature);
            $this->contractPdfService->generateForSignature($actorContext, $signature, $template);

            ContractApproved::dispatch(
                (int) $signature->id,
                (string) $actorContext['type']->value,
                (int) $actorContext['route_actor_id'],
                (int) $template->id
            );

            $this->audit('info', 'Contract approved successfully', [
                'signature_id' => $signature->id,
                'actor_type' => $actorContext['type']->value,
                'actor_id' => $actorContext['route_actor_id'],
            ]);
        } catch (Throwable $e) {
            ContractApprovalFailed::dispatch(
                isset($signature) && $signature ? (int) $signature->id : null,
                (string) $actorContext['type']->value,
                (int) $actorContext['route_actor_id'],
                (int) $template->id,
                $e->getMessage()
            );

            throw $e;
        }
    }

    private function audit(string $level, string $message, array $context = []): void
    {
        match ($level) {
            'debug' => Log::debug($message, $context),
            'info' => Log::info($message, $context),
            'notice' => Log::notice($message, $context),
            'warning' => Log::warning($message, $context),
            'error' => Log::error($message, $context),
            'critical' => Log::critical($message, $context),
            'alert' => Log::alert($message, $context),
            'emergency' => Log::emergency($message, $context),
            default => Log::info($message, $context),
        };

        if (function_exists('logSession')) {
            logSession($message, $context, $level, 'contract_logs');
        }
    }
}
