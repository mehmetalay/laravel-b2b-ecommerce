<?php

namespace App\Application\Contract\Actions;

use App\Application\Contract\Services\ContractWorkflowService;
use App\Models\ContractTemplate;

class ApproveContractAction
{
    public function __construct(
        private ContractWorkflowService $contractWorkflowService
    ) {}

    public function __invoke(string $actorType, int|string $actorId, ContractTemplate $template, array $input): void
    {
        $actorContext = $this->contractWorkflowService->resolveActor($actorType, $actorId);
        $this->contractWorkflowService->approve($actorContext, $template, $input);
    }
}
