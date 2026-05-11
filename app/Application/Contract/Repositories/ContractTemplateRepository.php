<?php

namespace App\Application\Contract\Repositories;

use App\Models\ContractTemplate;
use Illuminate\Support\Facades\Cache;

class ContractTemplateRepository
{
    public function findActiveForActorType(string $actorType): ?ContractTemplate
    {
        $cacheKey = "contract.active_template.{$actorType}";

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($actorType) {
            return ContractTemplate::query()
                ->where('is_active', 1)
                ->whereIn('dealer_type', [$actorType, 'all'])
                ->orderByRaw('CASE WHEN dealer_type = ? THEN 0 ELSE 1 END', [$actorType])
                ->orderByDesc('id')
                ->first();
        });
    }

    public function clearActiveTemplateCache(): void
    {
        Cache::forget('contract.active_template.dealer');
        Cache::forget('contract.active_template.subdealer');
    }
}

