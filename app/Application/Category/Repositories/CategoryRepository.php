<?php

namespace App\Application\Category\Repositories;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryRepository
{
    protected $cacheKeyAll = 'category:all';
    protected $cacheKeyActive = 'category:active';
    protected $cacheKeyActiveVisibleParent = 'category:active:visible_parent';
    protected $cacheKeyActiveHomepage = 'category:active:homepage';
    protected $cacheKeySlugFirst = 'category:slug:first:';
    protected $cacheKeyHtml = 'homepagecategory:html';

    public function create(array $data): Category
    {
        $category = Category::create($data);

        $this->clearCache($category->slug);

        return $category;
    }

    public function update(Category $category, array $data): Category
    {
        $oldSlug = (string) $category->slug;

        $category->update($data);

        $this->clearCacheBySlugs([
            $oldSlug,
            (string) $category->slug,
        ]);

        return $category;
    }

    public function updateSortOrders(array $orders, $parentId = null): void
    {
        foreach ($orders as $item) {
            $category = Category::where('id', $item['id'])->where('parent_id', $parentId)->first();

            if (!$category) {
                continue;
            }

            $category->update(['sort_order' => $item['sort_order']]);

            $this->clearCache($category->slug);
        }
    }

    public function updateSortOrderWithShift(Category $category, int $targetSortOrder): void
    {
        $currentCategory = Category::query()
            ->notDeleted()
            ->whereKey($category->id)
            ->lockForUpdate()
            ->firstOrFail();

        $parentId = $currentCategory->parent_id;

        $siblingScope = Category::query()
            ->notDeleted()
            ->where(function ($query) use ($parentId) {
                if ($parentId === null) {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $parentId);
                }
            });

        $maxSortOrder = (int) ((clone $siblingScope)->max('sort_order') ?? 1);
        $newSortOrder = min($targetSortOrder, max(1, $maxSortOrder));
        $currentSortOrder = max(1, (int) $currentCategory->sort_order);

        if ($newSortOrder === $currentSortOrder) {
            return;
        }

        $affectedCategories = collect();

        if ($newSortOrder < $currentSortOrder) {
            $categoriesToShift = (clone $siblingScope)
                ->where('id', '!=', $currentCategory->id)
                ->where('sort_order', '>=', $newSortOrder)
                ->where('sort_order', '<', $currentSortOrder)
                ->get();

            foreach ($categoriesToShift as $shiftCategory) {
                $shiftCategory->update([
                    'sort_order' => (int) $shiftCategory->sort_order + 1,
                ]);
                $affectedCategories->push($shiftCategory);
            }
        } else {
            $categoriesToShift = (clone $siblingScope)
                ->where('id', '!=', $currentCategory->id)
                ->where('sort_order', '<=', $newSortOrder)
                ->where('sort_order', '>', $currentSortOrder)
                ->get();

            foreach ($categoriesToShift as $shiftCategory) {
                $shiftCategory->update([
                    'sort_order' => max(1, (int) $shiftCategory->sort_order - 1),
                ]);
                $affectedCategories->push($shiftCategory);
            }
        }

        $currentCategory->update([
            'sort_order' => $newSortOrder,
        ]);
        $affectedCategories->push($currentCategory);

        $affectedCategories
            ->unique('id')
            ->each(function (Category $affectedCategory) {
                $this->clearCache($affectedCategory->slug);
            });
    }

    public function delete(Category $category)
    {
        $slugsToClear = $category->allChildren()
            ->pluck('slug')
            ->prepend((string) $category->slug)
            ->filter()
            ->values()
            ->all();

        $category->delete();
        $category->allChildren()->delete();
        $category->products()->update(['category_id' => null]);

        $this->clearCacheBySlugs($slugsToClear);

        return true;
    }

    public function getAllCategories()
    {
        return Cache::rememberForever($this->cacheKeyAll, function () {
            return Category::withRelations()
                ->notDeleted()
                ->orderBy('sort_order', 'asc')
                ->get();
        });
    }

    public function getAllActiveCategories()
    {
        return Cache::rememberForever($this->cacheKeyActive, function () {
            return Category::activeAndNotDeleted()
                ->withRelations()
                ->get();
        });
    }

    public function getVisibleParentCategories()
    {
        $categories = Cache::rememberForever($this->cacheKeyActiveVisibleParent, function () {
            return Category::activeAndNotDeleted()
                ->withRelations()
                ->parent()
                ->orderBy('sort_order', 'asc')
                ->get();
        });

        return $categories->filter(function ($category) {
            return !in_array($category->id, hide_category_ids());
        });
    }

    public function getFirstBySlug($slug)
    {
        $cacheKey = $this->cacheKeySlugFirst . $slug;

        $category = Cache::rememberForever($cacheKey, function () use ($slug) {
            return Category::whereSlug($slug)
                ->activeAndNotDeleted()
                ->first();
        });

        if (!$category) {
            return null;
        }

        return in_array($category->id, hide_category_ids()) ? null : $category;
    }

    public function clearCache($slug)
    {
        $cacheKey = $this->cacheKeySlugFirst . $slug;

        forget_cache_keys([
            $this->cacheKeyAll,
            $this->cacheKeyActive,
            $this->cacheKeyActiveVisibleParent,
            $this->cacheKeyActiveHomepage,
            $this->cacheKeyHtml . ':tr',
            $this->cacheKeyHtml . ':en',
            $cacheKey
        ]);
    }

    public function clearCacheBySlugs(array $slugs): void
    {
        foreach (array_unique(array_filter($slugs, fn ($slug) => $slug !== null && $slug !== '')) as $slug) {
            $this->clearCache((string) $slug);
        }
    }

    public function deleteNotInIds(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        $categories = Category::query()
            ->whereNotIn('id', $ids)
            ->get();

        foreach ($categories as $category) {
            $this->delete($category);
        }

        return $categories->count();
    }
}
