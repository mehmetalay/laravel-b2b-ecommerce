<?php

namespace App\Application\Contract\Services;

use App\Application\Contract\Enums\ContractActorType;
use App\Models\SubDealer;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContractActorResolver
{
    public function resolve(string $actorType, int|string $actorId): array
    {
        $type = ContractActorType::tryFromMixed($actorType);
        if ($type === null) {
            throw new NotFoundHttpException();
        }

        $id = (int) $actorId;
        if ($id <= 0) {
            throw new NotFoundHttpException();
        }

        if ($type === ContractActorType::DEALER) {
            $actor = User::query()->findOrFail($id);

            return [
                'type' => $type,
                'actor' => $actor,
                'contract_user_id' => (int) $actor->current_account_id,
                'route_actor_id' => (int) $actor->id,
            ];
        }

        $actor = SubDealer::query()->findOrFail($id);

        return [
            'type' => $type,
            'actor' => $actor,
            'contract_user_id' => (int) $actor->id,
            'route_actor_id' => (int) $actor->id,
        ];
    }
}
