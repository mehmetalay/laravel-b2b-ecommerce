<?php

namespace App\Services;

use App\Models\HomepageBlock;
use App\Http\Requests\HomepageBlockRequest;
use App\Repositories\HomepageBlockRepository;
use Illuminate\Support\Str;

class HomepageBlockService
{
    protected $repository;

    public function __construct(HomepageBlockRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createRaw(array $data)
    {
        $data['is_active'] = 1;
        $data['sort_order'] = $data['sort_order'] ? $data['sort_order'] : $this->getAllHomepageBlocks()->max('sort_order') + 1;
        
        if (empty($data['slug']) && !empty($data['title_tr'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title_tr']);
        }

        return $this->repository->create($data);
    }

    public function create(HomepageBlockRequest $request)
    {
        $data = $request->validated();

        return $this->createRaw($data);
    }

    public function update(HomepageBlockRequest $request, $brand)
    {
        $data = $request->validated();

        return $this->updateRaw($brand, $data);
    }

    public function updateRaw(HomepageBlock $homepageBlock, array $data)
    {
        $data['sort_order'] = $data['sort_order'] ? $data['sort_order'] : $homepageBlock->sort_order;

        if (empty($data['slug']) && !empty($data['title_tr'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title_tr'], $homepageBlock->id);
        }

        return $this->repository->update($homepageBlock, $data);
    }

    public function delete(HomepageBlock $homepageBlock)
    {
        return $this->repository->delete($homepageBlock);
    }

    public function getAllHomepageBlocks()
    {
        return $this->repository->getAllHomepageBlocks();
    }

    public function getActiveHomepageBlocks()
    {
        return $this->repository->getActiveHomepageBlocks();
    }

    public function clearCache()
    {
        return $this->repository->clearCache();
    }

    protected function generateUniqueSlug($title, $ignoreId = null)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while (HomepageBlock::where('slug', $slug)->where('id', '!=', $ignoreId)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
