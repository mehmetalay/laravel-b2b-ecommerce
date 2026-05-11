<?php

namespace App\Application\Contract\Actions;

use App\Application\Contract\Services\ContractWorkflowService;
use App\Models\ContractTemplate;

class AcceptContractAction
{
    public function __construct(
        private ContractWorkflowService $contractWorkflowService
    ) {}

    public function __invoke(string $actorType, int|string $actorId, ContractTemplate $template): string
    {
        $actorContext = $this->contractWorkflowService->resolveActor($actorType, $actorId);

        return $this->contractWorkflowService->prepareAccept($actorContext, $template);
    }
}
