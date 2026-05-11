<?php

namespace App\Application\Contract\Actions;

use App\Application\Contract\Services\ContractWorkflowService;
use App\Models\ContractTemplate;

class SendSmsCodeAction
{
    public function __construct(
        private ContractWorkflowService $contractWorkflowService
    ) {}

    public function __invoke(string $actorType, int|string $actorId, ContractTemplate $template, string $key): void
    {
        $actorContext = $this->contractWorkflowService->resolveActor($actorType, $actorId);
        $this->contractWorkflowService->sendSmsCode($actorContext, $template, $key);
    }
}
