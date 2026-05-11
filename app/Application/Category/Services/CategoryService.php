<?php

namespace App\Application\Category\Services;

use App\Application\Category\Repositories\CategoryRepository;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Services\ImageService;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    protected $repository;
    protected $imageService;

    public function __construct(CategoryRepository $repository, ImageService $imageService)
    {
        $this->repository = $repository;
        $this->imageService = $imageService;
    }

    public function createRaw(array $data)
    {
        return $this->repository->create($data);
    }

    public function create(CategoryRequest $request)
    {
        $data = $request->validated();

        $data['sort_order'] = $data['sort_order'] ? (int) $data['sort_order'] :
            $this->getAllCategories()
                ->when($parentId = $data['parent_id'], fn ($q) => $q->where('parent_id', $parentId))
                ->max('sort_order') + 1;

        $data['image'] = $request->has('image') ? $data['slug'] . '.jpg' : null;

        if ($request->has('image')) {
            $this->imageService->category($request->file('image'), $data['image']);
        }

        return $this->createRaw($data);
    }

    public function update(CategoryRequest $request, $category)
    {
        $data = $request->validated();

        $data['sort_order'] = $data['sort_order'] ? (int) $data['sort_order'] :
            $this->getAllCategories()
                ->when($parentId = $data['parent_id'], fn ($q) => $q->where('parent_id', $parentId))
                ->where('id', '!=', $category->id)
                ->max('sort_order') + 1;

        $data['image'] = $request->has('image') ? $data['slug'] . '.jpg' : $category->image;

        if ($request->has('image')) {
            $this->imageService->category($request->file('image'), $data['image']);
        }

        return $this->repository->update($category, $data);
    }

    public function updateSortOrders(array $orders, $parentId = null): void
    {
        $this->repository->updateSortOrders($orders, $parentId);
    }

    public function updateSortOrder(Category $category, int $sortOrder): void
    {
        DB::transaction(function () use ($category, $sortOrder) {
            $this->repository->updateSortOrderWithShift($category, $sortOrder);
        });
    }

    public function delete(Category $category)
    {
        return $this->repository->delete($category);
    }

    public function getAllCategories()
    {
        return $this->repository->getAllCategories();
    }

    public function getAllActiveCategories()
    {
        return $this->repository->getAllActiveCategories();
    }

    public function getVisibleParentCategories()
    {
        return $this->repository->getVisibleParentCategories();
    }

    public function getFirstBySlug($slug)
    {
        return $this->repository->getFirstBySlug($slug);
    }

    public function buildFullSlug($categoryId, $childSlug)
    {
        $parent = $this->getAllCategories()->where('id', $categoryId)->first();

        if (!$parent) {
            return $childSlug;
        }

        $slug = $parent->slug . '-' . $childSlug;

        if ($parent->parent_id) {
            return $this->buildFullSlug($parent->parent_id, $slug);
        }

        return $slug;
    }

    public function buildTree($categories, $parentId = null, $prefix = '')
    {
        $tree = [];
        foreach ($categories as $category) {
            if ($category->parent_id == $parentId) {
                $category->full_name = $prefix ? $prefix . ' -> ' . $category->name : $category->name;

                $tree[] = $category;

                $tree = array_merge($tree, self::buildTree($categories, $category->id, $category->full_name));
            }
        }

        return $tree;
    }

    public function getCategoryAndDescendantIds(Category $category): array
    {
        $ids = [$category->id];

        foreach ($category->children as $child) {
            $ids = array_merge($ids, $this->getCategoryAndDescendantIds($child));
        }

        return $ids;
    }

    public function isCategoryActive(?Category $category): bool
    {
        if (!$category) {
            return false;
        }

        if ($category->status === false) {
            return false;
        }

        if ($category->parent_id) {
            return $this->isCategoryActive($category->parent);
        }

        return true;
    }

    public function deleteCategoriesNotIn(array $categoryIds): int
    {
        $categoryIds = array_values(array_unique(array_filter($categoryIds)));

        if (empty($categoryIds)) {
            return 0;
        }

        return DB::transaction(function () use ($categoryIds) {
            return $this->repository->deleteNotInIds($categoryIds);
        });
    }
}
