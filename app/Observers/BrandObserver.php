<?php

namespace App\Observers;

use App\Models\Brand;
use App\Application\Brand\Repositories\BrandRepository;

class BrandObserver
{
    public function __construct(protected BrandRepository $repo) {}

    public function created(Brand $brand): void
    {
        $this->repo->clearAllCachesForBrand(null, $brand->slug);
    }

    public function updated(Brand $brand): void
    {
        $oldSlug = $brand->getOriginal('slug');
        $this->repo->clearAllCachesForBrand($oldSlug, $brand->slug);
    }

    public function deleted(Brand $brand): void
    {
        $this->repo->clearAllCachesForBrand($brand->slug, null);
    }

    public function restored(Brand $brand): void
    {
        $this->repo->clearAllCachesForBrand(null, $brand->slug);
    }

    public function forceDeleted(Brand $brand): void
    {
        $this->repo->clearAllCachesForBrand($brand->slug, null);
    }
}
