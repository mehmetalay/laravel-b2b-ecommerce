<?php

namespace App\Application\Contract\Services;

use App\Application\Contract\Repositories\ContractSignatureRepository;

class ContractApprovalRequirementService
{
    public function __construct(
        private ContractTemplateResolverService $contractTemplateResolverService,
        private ContractSignatureRepository $contractSignatureRepository
    ) {}

    public function evaluate(string $actorType, int $actorId, int $signatureUserId): array
    {
        $activeTemplate = $this->contractTemplateResolverService->resolveActiveByActorType($actorType);
        if (!$activeTemplate) {
            return [
                'active_template' => null,
                'has_signed' => false,
                'redirect_required' => false,
                'route_parameters' => null,
            ];
        }

        $hasSigned = $this->contractSignatureRepository->hasVerifiedByContext(
            userId: $signatureUserId,
            actorType: $actorType,
            templateId: (int) $activeTemplate->id
        );

        return [
            'active_template' => $activeTemplate,
            'has_signed' => $hasSigned,
            'redirect_required' => !$hasSigned,
            'route_parameters' => [
                'actor_type' => $actorType,
                'actor_id' => $actorId,
                'template' => (int) $activeTemplate->id,
            ],
        ];
    }
}
