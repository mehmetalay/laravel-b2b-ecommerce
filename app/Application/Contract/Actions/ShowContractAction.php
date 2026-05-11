<?php

namespace App\Application\Contract\Actions;

use App\Application\Contract\Services\ContractWorkflowService;
use App\Models\ContractTemplate;

class ShowContractAction
{
    public function __construct(
        private ContractWorkflowService $contractWorkflowService
    ) {}

    public function __invoke(string $actorType, int|string $actorId, ContractTemplate $template): array
    {
        $actorContext = $this->contractWorkflowService->resolveActor($actorType, $actorId);

        return $this->contractWorkflowService->buildShowPayload($actorContext, $template);
    }
}
