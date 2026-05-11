<?php

namespace App\Application\Contract\Repositories;

use App\Models\Contract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContractRepository
{
    private ?bool $hasActorTypeColumn = null;

    public function findByContext(array $actorContext): ?Contract
    {
        return Contract::query()
            ->where('user_id', (int) $actorContext['contract_user_id'])
            ->when(
                $this->hasActorTypeColumn(),
                fn ($query) => $query->where('actor_type', $actorContext['type']->value)
            )
            ->latest('id')
            ->first();
    }

    public function upsertByContext(array $actorContext, array $attributes): Contract
    {
        $lookup = ['user_id' => (int) $actorContext['contract_user_id']];
        if ($this->hasActorTypeColumn()) {
            $lookup['actor_type'] = $actorContext['type']->value;
        }

        return Contract::query()->updateOrCreate($lookup, $attributes);
    }

    public function replaceBankAccounts(Contract $contract, array $rows): void
    {
        $contract->bankAccounts()->whereBetween('sort_order', [1, 5])->delete();
        if (!empty($rows)) {
            DB::table('contract_bank_accounts')->insert($rows);
        }
    }

    public function replaceEmails(Contract $contract, array $rows): void
    {
        $contract->emails()->whereBetween('sort_order', [1, 5])->delete();
        if (!empty($rows)) {
            DB::table('contract_emails')->insert($rows);
        }
    }

    public function replaceGsms(Contract $contract, array $rows): void
    {
        $contract->gsms()->whereBetween('sort_order', [1, 5])->delete();
        if (!empty($rows)) {
            DB::table('contract_gsms')->insert($rows);
        }
    }

    public function replaceShipLocations(Contract $contract, array $rows): void
    {
        $contract->shipLocations()->whereBetween('sort_order', [1, 5])->delete();
        if (!empty($rows)) {
            DB::table('contract_ship_locations')->insert($rows);
        }
    }

    private function hasActorTypeColumn(): bool
    {
        if ($this->hasActorTypeColumn === null) {
            $this->hasActorTypeColumn = Schema::hasColumn('contracts', 'actor_type');
        }

        return $this->hasActorTypeColumn;
    }
}
