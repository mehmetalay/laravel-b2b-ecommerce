<?php

namespace App\Application\Contract\Actions;

use App\Application\Contract\Services\ContractWorkflowService;
use App\Models\ContractTemplate;

class StoreContractAction
{
    public function __construct(
        private ContractWorkflowService $contractWorkflowService
    ) {}

    public function __invoke(string $actorType, int|string $actorId, ContractTemplate $template, array $input): array
    {
        $actorContext = $this->contractWorkflowService->resolveActor($actorType, $actorId);
        $this->contractWorkflowService->store($actorContext, $input);

        return [
            'actor_type' => $actorContext['type']->value,
            'actor_id' => $actorContext['route_actor_id'],
            'template_id' => (int) $template->id,
        ];
    }
}
