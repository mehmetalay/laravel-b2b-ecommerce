<?php

namespace App\Application\Contract\Services;

use App\Models\ContractTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContractWorkflowService
{
    public function __construct(
        private ContractActorResolver $contractActorResolver,
        private ContractPersistenceService $contractPersistenceService,
        private ContractSmsVerificationService $contractSmsVerificationService,
        private ContractApprovalService $contractApprovalService
    ) {}

    public function resolveActor(string $actorType, int|string $actorId): array
    {
        return $this->contractActorResolver->resolve($actorType, $actorId);
    }

    public function buildShowPayload(array $actorContext, ContractTemplate $template): array
    {
        return $this->contractPersistenceService->buildShowPayload($actorContext, $template);
    }

    public function store(array $actorContext, array $input): void
    {
        DB::transaction(function () use ($actorContext, $input): void {
            $this->contractPersistenceService->store($actorContext, $input);
        });
    }

    public function prepareAccept(array $actorContext, ContractTemplate $template): string
    {
        return $this->contractSmsVerificationService->prepareAcceptButtonHtml($actorContext, $template);
    }

    public function sendSmsCode(array $actorContext, ContractTemplate $template, string $key): void
    {
        $this->contractSmsVerificationService->sendSmsCode($actorContext, $template, $key);
    }

    public function approve(array $actorContext, ContractTemplate $template, array $input): void
    {
        $this->contractApprovalService->approve($actorContext, $template, $input);
    }

    public function reportException(string $stage, Throwable $e): void
    {
        Log::error("Contract {$stage} exception", ['exception' => $e->getMessage()]);
        if (function_exists('logSession')) {
            logSession("Contract {$stage} exception", ['exception' => $e->getMessage()], 'error', 'contract_logs');
        }

        try {
            Mail::raw("contract {$stage} hatasi: " . $e->getMessage(), function ($message) {
                $message->to(config('services.notifications.error_mail'))->subject(config('app.name') . ' Hata');
            });
        } catch (Throwable $mailException) {
            Log::error('Contract exception mail failed', ['exception' => $mailException->getMessage()]);
        }
    }
}
