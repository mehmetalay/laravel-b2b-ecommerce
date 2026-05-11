<?php

namespace App\Services\Exports;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;

class PaymentExportQueryService
{
    public function build(array $filters, ?array $selectedIds = null): Builder
    {
        $search = $this->readString($filters, ['search', 'q', 'name']);
        $status = strtoupper((string) $this->readString($filters, ['status']));
        $processType = strtolower((string) $this->readString($filters, ['process_type', 'refund_status', 'stock_status']));
        $salesmanIds = $this->parseIds($filters['salesman_id'] ?? $filters['salesmanId'] ?? null);
        $bankIntegrationIds = $this->parseIds($filters['bank_integration_id'] ?? $filters['bankIntegrationId'] ?? null);
        $dateFrom = $this->readString($filters, ['date_from']);
        $dateTo = $this->readString($filters, ['date_to']);

        $query = Payment::query()
            ->with([
                'bankIntegration.company:id,name',
                'plasiyer:id,current_account_id,name,code,email,phone',
                'user:id,current_account_id,name,code,email,phone',
                'subDealer:id,name',
            ])
            ->when($search !== null && $search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $q) use ($search): void {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhere('oid', 'like', "%{$search}%")
                        ->orWhere('card_name', 'like', "%{$search}%")
                        ->orWhere('card_number', 'like', "%{$search}%")
                        ->orWhereRelation('user', 'name', 'like', "%{$search}%")
                        ->orWhereRelation('user', 'code', 'like', "%{$search}%")
                        ->orWhereRelation('user', 'email', 'like', "%{$search}%")
                        ->orWhereRelation('user', 'phone', 'like', "%{$search}%")
                        ->orWhereRelation('plasiyer', 'name', 'like', "%{$search}%")
                        ->orWhereRelation('plasiyer', 'code', 'like', "%{$search}%")
                        ->orWhereRelation('plasiyer', 'email', 'like', "%{$search}%")
                        ->orWhereRelation('plasiyer', 'phone', 'like', "%{$search}%")
                        ->orWhereRelation('subDealer', 'name', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', function (Builder $query) use ($status): void {
                $query->whereRaw('UPPER(status) = ?', [$status]);
            }, function (Builder $query): void {
                $query->whereIn('status', ['SUCCESS', 'FAILED', 'REFUNDED', 'refunded']);
            })
            ->when($processType === 'payment', function (Builder $query): void {
                $query->whereNull('refund_status');
            })
            ->when(in_array($processType, ['refunded', 'cancelled'], true), function (Builder $query) use ($processType): void {
                $query->where('refund_status', $processType);
            })
            ->when(count($salesmanIds) > 0, function (Builder $query) use ($salesmanIds): void {
                $query->whereIn('plasiyer_id', $salesmanIds);
            })
            ->when(count($bankIntegrationIds) > 0, function (Builder $query) use ($bankIntegrationIds): void {
                $query->whereIn('bank_integration_id', $bankIntegrationIds);
            })
            ->when($dateFrom !== null && $dateFrom !== '', function (Builder $query) use ($dateFrom): void {
                $query->where('created_at', '>=', "{$dateFrom} 00:00:00");
            })
            ->when($dateTo !== null && $dateTo !== '', function (Builder $query) use ($dateTo): void {
                $query->where('created_at', '<=', "{$dateTo} 23:59:59");
            });

        if ($selectedIds !== null && count($selectedIds) > 0) {
            $query->whereIn('id', collect($selectedIds)->map(fn ($id) => (int) $id)->values()->all());
        }

        return $query;
    }

    private function readString(array $filters, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $filters)) {
                continue;
            }

            $value = $filters[$key];
            if ($value === null) {
                return null;
            }

            return is_string($value) ? trim($value) : (string) $value;
        }

        return null;
    }

    /**
     * @param array<int, mixed>|string|int|null $value
     * @return array<int, int>
     */
    private function parseIds($value): array
    {
        if (is_string($value)) {
            $value = array_filter(explode(',', $value), static fn ($item) => trim((string) $item) !== '');
        }

        if (is_int($value)) {
            $value = [$value];
        }

        if (!is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values()
            ->all();
    }
}
