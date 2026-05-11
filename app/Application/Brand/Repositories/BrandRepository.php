<?php

namespace App\Application\Brand\Repositories;

use App\Models\Brand;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BrandRepository
{
    protected string $cacheKeyAll = 'brand:all';
    protected string $cacheKeyActive = 'brand:active';
    protected string $cacheKeyActiveWithImage = 'brand:active:with_image';
    protected string $cacheKeySlugFirstPrefix = 'brand:slug:first:';

    public function create(array $data): Brand
    {
        return Brand::create($data);
    }

    public function update(Brand $brand, array $data): Brand
    {
        $oldSlug = $brand->slug;

        $brand->update($data);

        $this->clearAllCachesForBrand($oldSlug, $brand->slug);

        return $brand;
    }

    public function delete(Brand $brand): bool
    {
        $slug = $brand->slug;

        $brand->delete();

        $this->clearAllCachesForBrand($slug, null);

        return true;
    }

    public function getAllBrands()
    {
        return Cache::rememberForever($this->cacheKeyAll, function () {
            return Brand::query()->get();
        });
    }

    public function getActiveBrands()
    {
        return Cache::rememberForever($this->cacheKeyActive, function () {
            return Brand::query()
                ->active()
                ->get();
        });
    }

    public function getActiveBrandsWithImage()
    {
        return Cache::rememberForever($this->cacheKeyActiveWithImage, function () {
            return Brand::query()
                ->active()
                ->image()
                ->get();
        });
    }

    public function getFirstBySlug(string $slug): ?Brand
    {
        $cacheKey = $this->cacheKeySlugFirstPrefix . $slug;

        return Cache::rememberForever($cacheKey, function () use ($slug) {
            return Brand::query()
                ->active()
                ->whereSlug($slug)
                ->first();
        });
    }

    public function clearAllCachesForBrand(?string $oldSlug, ?string $newSlug): void
    {
        $slugs = array_filter(array_unique([$oldSlug, $newSlug]));

        $keys = [
            $this->cacheKeyAll,
            $this->cacheKeyActive,
            $this->cacheKeyActiveWithImage,
        ];

        foreach ($slugs as $slug) {
            $keys[] = $this->cacheKeySlugFirstPrefix . $slug;
        }

        forget_cache_keys($keys);
    }

    public function paginateAll(int $perPage = 50, $filters = null): LengthAwarePaginator
    {
        return Brand::query()
            ->filter($filters)
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    public function updateSortOrders(array $orders): void
    {
        $ids = collect($orders)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return;
        }

        $brands = Brand::query()
            ->whereNull('deleted_at')
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        foreach ($orders as $item) {
            $brand = $brands->get((int) ($item['id'] ?? 0));

            if (!$brand || !isset($item['sort_order'])) {
                continue;
            }

            $brand->update([
                'sort_order' => (int) $item['sort_order'],
            ]);
        }

        foreach ($brands as $brand) {
            $this->clearAllCachesForBrand($brand->slug, $brand->slug);
        }
    }

    public function updateSortOrderWithShift(Brand $brand, int $targetSortOrder): void
    {
        $currentBrand = Brand::query()
            ->whereNull('deleted_at')
            ->whereKey($brand->id)
            ->lockForUpdate()
            ->firstOrFail();

        $brandScope = Brand::query()->whereNull('deleted_at');

        $maxSortOrder = (int) ((clone $brandScope)->max('sort_order') ?? 1);
        $newSortOrder = min($targetSortOrder, max(1, $maxSortOrder));
        $currentSortOrder = max(1, (int) $currentBrand->sort_order);

        if ($newSortOrder === $currentSortOrder) {
            return;
        }

        $affectedBrands = collect();

        if ($newSortOrder < $currentSortOrder) {
            $brandsToShift = (clone $brandScope)
                ->where('id', '!=', $currentBrand->id)
                ->where('sort_order', '>=', $newSortOrder)
                ->where('sort_order', '<', $currentSortOrder)
                ->get();

            foreach ($brandsToShift as $shiftBrand) {
                $shiftBrand->update([
                    'sort_order' => (int) $shiftBrand->sort_order + 1,
                ]);
                $affectedBrands->push($shiftBrand);
            }
        } else {
            $brandsToShift = (clone $brandScope)
                ->where('id', '!=', $currentBrand->id)
                ->where('sort_order', '<=', $newSortOrder)
                ->where('sort_order', '>', $currentSortOrder)
                ->get();

            foreach ($brandsToShift as $shiftBrand) {
                $shiftBrand->update([
                    'sort_order' => max(1, (int) $shiftBrand->sort_order - 1),
                ]);
                $affectedBrands->push($shiftBrand);
            }
        }

        $currentBrand->update([
            'sort_order' => $newSortOrder,
        ]);
        $affectedBrands->push($currentBrand);

        $affectedBrands
            ->unique('id')
            ->each(function (Brand $affectedBrand) {
                $this->clearAllCachesForBrand($affectedBrand->slug, $affectedBrand->slug);
            });
    }

    public function findBySlugIncludingTrashed(string $slug): ?Brand
    {
        return Brand::withTrashed()
            ->where('slug', $slug)
            ->first();
    }

    public function findByNameIncludingTrashed(string $name): ?Brand
    {
        return Brand::withTrashed()
            ->where('name', $name)
            ->first();
    }

    public function deactivateNotInIds(array $ids): int
    {
        $brands = Brand::query()
            ->whereNotIn('id', $ids)
            ->get();

        foreach ($brands as $brand) {
            $this->delete($brand);
        }

        return $brands->count();
    }
}
