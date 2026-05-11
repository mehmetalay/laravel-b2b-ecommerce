<?php

namespace App\Services\Exports;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class OrderExportQueryService
{
    public function build(array $filters, ?array $selectedIds = null): Builder
    {
        $search = $this->readString($filters, ['search', 'q', 'name']);
        $status = $this->readString($filters, ['status']);
        $erpStatus = $this->readString($filters, ['stock_status', 'erp_status']);
        $firstDate = $this->readString($filters, ['first_date']);
        $lastDate = $this->readString($filters, ['last_date']);

        $query = Order::query()
            ->with(['plasiyer', 'user', 'subDealer', 'orderStatus'])
            ->where('status', 'approved')
            ->when($search !== null && $search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $nested) use ($search): void {
                    $nested->where('id', 'like', "%{$search}%")
                        ->orWhereRelation('plasiyer', 'name', 'like', "%{$search}%")
                        ->orWhereRelation('plasiyer', 'code', 'like', "%{$search}%")
                        ->orWhereRelation('plasiyer', 'email', 'like', "%{$search}%")
                        ->orWhereRelation('plasiyer', 'phone', 'like', "%{$search}%")
                        ->orWhereRelation('user', 'name', 'like', "%{$search}%")
                        ->orWhereRelation('user', 'code', 'like', "%{$search}%")
                        ->orWhereRelation('user', 'email', 'like', "%{$search}%")
                        ->orWhereRelation('user', 'phone', 'like', "%{$search}%")
                        ->orWhereRelation('subDealer', 'name', 'like', "%{$search}%");
                });
            })
            ->when($status !== null && $status !== '', function (Builder $query) use ($status): void {
                $query->where('order_status_id', (int) $status);
            })
            ->when($erpStatus !== null && $erpStatus !== '', function (Builder $query) use ($erpStatus): void {
                $query->where('erp_status', (string) $erpStatus);
            })
            ->when($firstDate && $lastDate, function (Builder $query) use ($firstDate, $lastDate): void {
                $query->whereBetween('created_at', [$firstDate . ' 00:00:00', $lastDate . ' 23:59:59']);
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
}
