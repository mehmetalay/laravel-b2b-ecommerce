<?php

namespace App\Services\Exports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductExportQueryService
{
    public function build(array $filters, ?array $selectedIds = null): Builder
    {
        $search = $this->readString($filters, ['search', 'q']);
        $categoryId = $this->readString($filters, ['category_id']);
        $brandId = $this->readString($filters, ['brand_id']);
        $status = $this->readString($filters, ['status']);
        $stockStatus = $this->readString($filters, ['stock_status']);

        $query = Product::query()
            ->with(['brand:id,name', 'category:id,name'])
            ->when($search !== null && $search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $q) use ($search): void {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('code', 'LIKE', "%{$search}%")
                        ->orWhere('code_group', 'LIKE', "%{$search}%")
                        ->orWhere('barcode', 'LIKE', "%{$search}%")
                        ->orWhereHas('brand', function (Builder $q2) use ($search): void {
                            $q2->where('name', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->when($categoryId !== null && $categoryId !== '', function (Builder $query) use ($categoryId): void {
                $query->where('category_id', $categoryId);
            })
            ->when($brandId !== null && $brandId !== '', function (Builder $query) use ($brandId): void {
                $query->where('brand_id', $brandId);
            })
            ->when($status !== null && $status !== '', function (Builder $query) use ($status): void {
                $query->where('status', (int) $status);
            })
            ->when($stockStatus === 'in_stock', function (Builder $query): void {
                $query->where('stock', '>', 0);
            })
            ->when($stockStatus === 'out_of_stock', function (Builder $query): void {
                $query->where('stock', '<=', 0);
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
