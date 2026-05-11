<?php

namespace App\Application\Category\Queries;

use App\Models\Category;
use Illuminate\Http\Request;

class AdminCategoryTableDataQuery
{
    public function handle(Request $request): array
    {
        $search = $request->input('search', $request->input('q'));
        $status = $request->input('status');
        $parentId = $request->input('parent_id');
        $perPage = max(1, (int) $request->input('per_page', 50));
        $page = max(1, (int) $request->input('page', 1));

        $items = Category::query()
            ->notDeleted()
            ->withCount('allChildren')
            ->when($parentId !== null && $parentId !== '', function ($query) use ($parentId) {
                $query->where('parent_id', (int) $parentId);
            }, function ($query) {
                $query->whereNull('parent_id');
            })
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%");
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('status', (int) $status);
            })
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $items->getCollection()->map(function (Category $item) {
            $subcategoriesCount = (int) ($item->all_children_count ?? 0);

            return [
                'id' => $item->id,
                'image_url' => $item->image_url,
                'name' => $this->normalizeUtf8($item->name),
                'subcategories_count' => $subcategoriesCount,
                'subcategories_url' => $subcategoriesCount > 0
                    ? route('admin.catalog.categories.subcategories', [$item->id])
                    : null,
                'subcategories_label' => $subcategoriesCount > 0
                    ? $subcategoriesCount . ' alt kategori var'
                    : 'Alt kategori yok',
                'status' => $item->status ? 'Aktif' : 'Pasif',
                'status_value' => (int) $item->status,
                'sort_order' => $item->sort_order,
                'sort_order_url' => route('admin.catalog.categories.sort-order', [$item->id]),
                'edit_url' => route('admin.catalog.categories.edit', [$item->id]),
                'inline_update_url' => url('/admin/api/categories/' . $item->id . '/inline'),
                'order_handle' => true,
                'created_at' => $item->created_at ? $item->created_at->format('d.m.Y H:i') : '-',
            ];
        })->values();

        return [
            'data' => $data,
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
            ],
            'filters' => [
                'statusOptions' => [
                    ['value' => '', 'label' => 'Tüm Durumlar'],
                    ['value' => '1', 'label' => 'Aktif'],
                    ['value' => '0', 'label' => 'Pasif'],
                ],
            ],
        ];
    }

    private function normalizeUtf8(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        $converted = @mb_convert_encoding($value, 'UTF-8', 'Windows-1254,ISO-8859-9,ISO-8859-1');

        if (is_string($converted) && mb_check_encoding($converted, 'UTF-8')) {
            return $converted;
        }

        $fallback = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
        return is_string($fallback) ? $fallback : '';
    }
}

