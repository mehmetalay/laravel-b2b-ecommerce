<?php

namespace App\Application\Contract\Repositories;

use App\Application\Contract\Enums\ContractSignatureStatus;
use App\Models\ContractSignature;

class ContractSignatureRepository
{
    public function firstOrCreatePending(int $userId, string $actorType, int $templateId): ContractSignature
    {
        return ContractSignature::query()->firstOrCreate(
            [
                'user_id' => $userId,
                'actor_type' => $actorType,
                'template_id' => $templateId,
            ],
            [
                'status' => ContractSignatureStatus::PENDING->value,
                'token' => uniqid(),
            ]
        );
    }

    public function findByContext(int $userId, string $actorType, int $templateId): ?ContractSignature
    {
        return ContractSignature::query()
            ->where('user_id', $userId)
            ->where('actor_type', $actorType)
            ->where('template_id', $templateId)
            ->first();
    }

    public function hasVerifiedByContext(int $userId, string $actorType, int $templateId): bool
    {
        return ContractSignature::query()
            ->where('user_id', $userId)
            ->where('actor_type', $actorType)
            ->where('template_id', $templateId)
            ->where('status', ContractSignatureStatus::VERIFIED->value)
            ->exists();
    }
}
