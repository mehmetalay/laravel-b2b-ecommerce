<?php

namespace App\Application\Contract\Services;

use App\Application\Contract\Exceptions\ContractTemplateNotFoundException;
use App\Application\Contract\Repositories\ContractTemplateRepository;
use App\Models\ContractTemplate;

class ContractTemplateResolverService
{
    public function __construct(
        private ContractTemplateRepository $contractTemplateRepository
    ) {}

    public function resolveActiveByActorType(string $actorType): ?ContractTemplate
    {
        return $this->contractTemplateRepository->findActiveForActorType($actorType);
    }

    public function resolveActiveByActorTypeOrFail(string $actorType): ContractTemplate
    {
        $template = $this->resolveActiveByActorType($actorType);
        if (!$template) {
            throw new ContractTemplateNotFoundException('Aktif sözleşme şablonu bulunamadı.');
        }

        return $template;
    }

    public function clearTemplateCache(): void
    {
        $this->contractTemplateRepository->clearActiveTemplateCache();
    }
}
