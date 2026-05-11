<?php

namespace App\Application\Brand\Services;

use App\Models\Brand;
use App\Application\Brand\Repositories\BrandRepository;
use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class BrandService
{
    public function __construct(
        protected BrandRepository $repository,
        protected ImageService $imageService
    ) {}

    public function create(array $data, ?UploadedFile $image = null): Brand
    {
        return DB::transaction(function () use ($data, $image) {

            $data['sort_order'] = $data['sort_order'] ?? ($this->repository->getAllBrands()->max('sort_order') + 1);

            $brand = $this->repository->create($data);

            if ($image && $image->isValid()) {
                $filename = $this->makeImageName($brand->slug);

                $this->imageService->brand($image, $filename);

                $this->repository->update($brand, [
                    'image' => $filename,
                ]);
            }

            return $brand;
        });
    }

    public function update(Brand $brand, array $data, ?UploadedFile $image = null): Brand
    {
        return DB::transaction(function () use ($brand, $data, $image) {

            $data['sort_order'] = $data['sort_order'] ?? $brand->sort_order;

            $this->repository->update($brand, $data);

            if ($image && $image->isValid()) {
                $filename = $this->makeImageName($brand->slug);

                $this->imageService->brand($image, $filename);

                $this->repository->update($brand, [
                    'image' => $filename,
                ]);
            }

            return $brand->refresh();
        });
    }

    public function createRaw(array $data): Brand
    {
        return DB::transaction(function () use ($data) {

            $name = mb_strtoupper(trim($data['name'] ?? ''));
            if ($name === '') {
                throw new \InvalidArgumentException('Brand name is required.');
            }

            $slug = $data['slug'] ?? str_slug($name);
            $status = (int) ($data['status'] ?? 1);

            $existing = $this->repository->findBySlugIncludingTrashed($slug) ?? $this->repository->findByNameIncludingTrashed($name);

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }

                return $this->repository->update($existing, [
                    'name' => $name,
                    'slug' => $slug,
                    'status' => $status,
                ]);
            }

            return $this->repository->create([
                'name' => $name,
                'slug' => $slug,
                'status' => $status,
                'image' => $data['image'] ?? null,
            ]);
        });
    }

    public function delete(Brand $brand): bool
    {
        return DB::transaction(fn () => $this->repository->delete($brand));
    }

    public function getAllBrands()
    {
        return $this->repository->getAllBrands();
    }

    public function getActiveBrands()
    {
        return $this->repository->getActiveBrands();
    }

    public function getActiveBrandsWithImage()
    {
        return $this->repository->getActiveBrandsWithImage();
    }

    public function getFirstBySlug($slug)
    {
        return $this->repository->getFirstBySlug($slug);
    }

    public function paginateBrands(int $perPage = 50, $filters = null)
    {
        return $this->repository->paginateAll($perPage, $filters);
    }

    private function makeImageName(string $brandName): string
    {
        return "{$brandName}.png";
    }

    public function deactivateBrandsNotIn(array $brandIds): int
    {
        $brandIds = array_values(array_unique(array_filter($brandIds)));

        if (empty($brandIds)) {
            return 0;
        }

        return DB::transaction(function () use ($brandIds) {
            return $this->repository->deactivateNotInIds($brandIds);
        });
    }

    public function updateSortOrders(array $orders): void
    {
        DB::transaction(function () use ($orders) {
            $this->repository->updateSortOrders($orders);
        });
    }

    public function updateSortOrder(Brand $brand, int $sortOrder): void
    {
        DB::transaction(function () use ($brand, $sortOrder) {
            $this->repository->updateSortOrderWithShift($brand, $sortOrder);
        });
    }
}
