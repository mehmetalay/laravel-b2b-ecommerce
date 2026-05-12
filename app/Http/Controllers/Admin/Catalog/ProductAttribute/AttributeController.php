<?php

namespace App\Http\Controllers\Admin\Catalog\ProductAttribute;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\ProductAttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AttributeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(AttributeGroup $attributeGroup)
    {
        return view('backend.pages.catalog.product-attribute.attribute.index', compact('attributeGroup'));
    }

    public function create(AttributeGroup $attributeGroup)
    {
        return view('backend.pages.catalog.product-attribute.attribute.create', compact('attributeGroup'));
    }

    public function store(Request $request, AttributeGroup $attributeGroup)
    {
        $validator = $this->validator($request->all(), null, $attributeGroup->id);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Lütfen formdaki hataları kontrol edin.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $slug = Str::slug((string) $request->input('name'), '-');
        $originalSlug = $slug;
        $counter = 1;

        while (Attribute::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $model = Attribute::create([
            'attribute_group_id' => $attributeGroup->id,
            'name' => (string) $request->input('name'),
            'name_en' => (string) $request->input('name_en'),
            'slug' => $slug,
            'status' => (int) $request->input('status', 1),
            'sort_order' => max(1, ((int) (Attribute::where('attribute_group_id', $attributeGroup->id)->max('sort_order') ?? 0)) + 1),
            'show_in_filter' => (int) $request->input('show_in_filter', 1),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Başarıyla eklendi.',
            'data' => [
                'id' => $model->id,
                'name' => $model->name,
                'name_en' => $model->name_en,
                'status' => (bool) $model->status,
                'show_in_filter' => (bool) $model->show_in_filter,
                'sort_order' => max(1, (int) $model->sort_order),
            ],
        ]);
    }

    public function show(AttributeGroup $attributeGroup, Attribute $attribute)
    {
        if ((int) $attribute->attribute_group_id !== (int) $attributeGroup->id) {
            abort(404);
        }

        return response()->json([
            'data' => [
                'id' => $attribute->id,
                'name' => $attribute->name,
                'name_en' => $attribute->name_en,
                'status' => (bool) $attribute->status,
                'show_in_filter' => (bool) $attribute->show_in_filter,
                'sort_order' => max(1, (int) ($attribute->sort_order ?? 1)),
            ],
        ]);
    }

    public function apiShow(Attribute $attribute)
    {
        return response()->json([
            'data' => [
                'id' => $attribute->id,
                'name' => $attribute->name,
                'name_en' => $attribute->name_en,
                'status' => (bool) $attribute->status,
                'show_in_filter' => (bool) $attribute->show_in_filter,
                'sort_order' => max(1, (int) ($attribute->sort_order ?? 1)),
            ],
        ]);
    }

    public function edit(AttributeGroup $attributeGroup, Attribute $attribute)
    {
        return view('backend.pages.catalog.product-attribute.attribute.edit', ['attributeGroup' => $attributeGroup, 'model' => $attribute]);
    }

    public function update(Request $request, AttributeGroup $attributeGroup, Attribute $attribute)
    {
        $validator = $this->validator($request->all(), $attribute->id, $attributeGroup->id);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Lütfen formdaki hataları kontrol edin.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $slug = $attribute->slug;
        if ((string) $attribute->name !== (string) $request->input('name')) {
            $slug = Str::slug((string) $request->input('name'), '-');
            $originalSlug = $slug;
            $counter = 1;

            while (Attribute::where('id', '!=', $attribute->id)->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $attribute->update([
            'name' => (string) $request->input('name'),
            'name_en' => (string) $request->input('name_en'),
            'slug' => $slug,
            'status' => (int) $request->input('status', 0),
            'show_in_filter' => (int) $request->input('show_in_filter', 0),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Başarıyla güncellendi.',
            'data' => [
                'id' => $attribute->id,
                'name' => $attribute->name,
                'name_en' => $attribute->name_en,
                'status' => (bool) $attribute->status,
                'show_in_filter' => (bool) $attribute->show_in_filter,
                'sort_order' => max(1, (int) ($attribute->sort_order ?? 1)),
            ],
        ]);
    }

    public function inlineUpdate(Request $request, Attribute $attribute)
    {
        $field = (string) $request->input('field', '');

        if (!in_array($field, ['name', 'status', 'sort_order'], true)) {
            return response()->json([
                'message' => 'Lütfen formdaki hataları kontrol edin.',
                'errors' => [
                    'field' => ['Geçersiz alan.'],
                ],
            ], 422);
        }

        if ($field === 'name') {
            $validator = Validator::make($request->all(), [
                'value' => [
                    'required',
                    'string',
                    Rule::unique('attributes', 'name')
                        ->where(function ($query) use ($attribute) {
                            return $query->where('attribute_group_id', $attribute->attribute_group_id);
                        })
                        ->ignore($attribute->id),
                ],
            ], [
                'value.required' => 'Lutfen adini giriniz.',
                'value.unique' => 'Girdiginiz ad sistemde zaten mevcut.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Lütfen formdaki hataları kontrol edin.',
                    'errors' => [
                        'name' => $validator->errors()->get('value'),
                    ],
                ], 422);
            }

            $name = trim((string) $request->input('value'));
            $slug = Str::slug($name, '-');
            $originalSlug = $slug;
            $counter = 1;

            while (Attribute::where('id', '!=', $attribute->id)->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $attribute->update([
                'name' => $name,
                'slug' => $slug,
            ]);
        }

        if ($field === 'status') {
            $validator = Validator::make($request->all(), [
                'value' => 'required|in:0,1,true,false',
            ], [
                'value.required' => 'Durum zorunludur.',
                'value.in' => 'Durum gecersiz.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Lütfen formdaki hataları kontrol edin.',
                    'errors' => [
                        'status' => $validator->errors()->get('value'),
                    ],
                ], 422);
            }

            $statusValue = (int) ((string) $request->input('value') === '1' || (string) $request->input('value') === 'true');
            $attribute->update([
                'status' => $statusValue,
            ]);
        }

        if ($field === 'sort_order') {
            $validator = Validator::make($request->all(), [
                'value' => ['required', 'integer', 'min:1'],
            ], [
                'value.required' => 'Sıra zorunludur.',
                'value.integer' => 'Sıra sayı olmalıdır.',
                'value.min' => 'Sıra en az 1 olmalıdır.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Lütfen formdaki hataları kontrol edin.',
                    'errors' => [
                        'sort_order' => $validator->errors()->get('value'),
                    ],
                ], 422);
            }

            DB::transaction(function () use ($attribute, $request): void {
                $currentAttribute = Attribute::query()->findOrFail($attribute->id);
                $scope = Attribute::query()->where('attribute_group_id', $currentAttribute->attribute_group_id);

                $maxSortOrder = (int) ((clone $scope)->max('sort_order') ?? 1);
                $currentSortOrder = max(1, (int) $currentAttribute->sort_order);
                $newSortOrder = min(max(1, (int) $request->input('value')), max(1, $maxSortOrder));

                if ($newSortOrder === $currentSortOrder) {
                    return;
                }

                if ($newSortOrder < $currentSortOrder) {
                    $toShift = (clone $scope)
                        ->where('id', '!=', $currentAttribute->id)
                        ->where('sort_order', '>=', $newSortOrder)
                        ->where('sort_order', '<', $currentSortOrder)
                        ->orderBy('sort_order', 'asc')
                        ->get();

                    $toShift->each(function (Attribute $shiftItem): void {
                        $shiftItem->update([
                            'sort_order' => max(1, (int) $shiftItem->sort_order + 1),
                        ]);
                    });
                } else {
                    $toShift = (clone $scope)
                        ->where('id', '!=', $currentAttribute->id)
                        ->where('sort_order', '<=', $newSortOrder)
                        ->where('sort_order', '>', $currentSortOrder)
                        ->orderBy('sort_order', 'asc')
                        ->get();

                    $toShift->each(function (Attribute $shiftItem): void {
                        $shiftItem->update([
                            'sort_order' => max(1, (int) $shiftItem->sort_order - 1),
                        ]);
                    });
                }

                $currentAttribute->update([
                    'sort_order' => max(1, $newSortOrder),
                ]);
            });

            $attribute->refresh();
        }

        return response()->json([
            'success' => true,
            'message' => 'Güncellendi.',
            'data' => [
                'id' => $attribute->id,
                'name' => $attribute->name,
                'status' => $attribute->status ? 'Aktif' : 'Pasif',
                'status_value' => (int) $attribute->status,
                'sort_order' => max(1, (int) ($attribute->sort_order ?? 1)),
            ],
        ]);
    }

    public function destroy(AttributeGroup $attributeGroup, Attribute $attribute)
    {
        $this->deleteAttribute($attribute);

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:activate,deactivate,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:attributes,id'],
        ]);

        $ids = collect($validated['ids'])->map(fn ($id) => (int) $id)->values();
        $action = (string) $validated['action'];

        if ($action === 'activate') {
            Attribute::query()->whereIn('id', $ids)->update(['status' => 1]);
        }

        if ($action === 'deactivate') {
            Attribute::query()->whereIn('id', $ids)->update(['status' => 0]);
        }

        if ($action === 'delete') {
            $attributes = Attribute::query()->whereIn('id', $ids)->get();
            $attributes->each(function (Attribute $attribute): void {
                $this->deleteAttribute($attribute);
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'İşlem başarıyla tamamlandı.',
        ]);
    }

    private function deleteAttribute(Attribute $attribute): void
    {
        $attribute->attributeValues->each(function ($attributeValue): void {
            ProductAttributeValue::where('attribute_value_id', $attributeValue->id)->delete();
            $attributeValue->delete();
        });

        $attribute->delete();
    }

    public function sort(AttributeGroup $attributeGroup, Request $request)
    {
        $orderItems = collect($request->input('order', []))
            ->filter(fn ($item) => isset($item['id'], $item['sort_order']))
            ->values()
            ->all();

        DB::transaction(function () use ($attributeGroup, $orderItems): void {
            $ids = collect($orderItems)->pluck('id')->map(fn ($id) => (int) $id)->all();

            $attributes = Attribute::query()
                ->where('attribute_group_id', $attributeGroup->id)
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');

            foreach ($orderItems as $item) {
                $attribute = $attributes->get((int) $item['id']);

                if (!$attribute) {
                    continue;
                }

                $attribute->update([
                    'sort_order' => max(1, (int) $item['sort_order']),
                ]);
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Sıralama güncellendi.',
        ]);
    }

    public function updateSortOrder(Request $request, AttributeGroup $attributeGroup, Attribute $attribute)
    {
        $validated = $request->validate([
            'sort_order' => ['required', 'integer', 'min:1'],
        ]);

        if ((int) $attribute->attribute_group_id !== (int) $attributeGroup->id) {
            abort(404);
        }

        DB::transaction(function () use ($attributeGroup, $attribute, $validated): void {
            $scope = Attribute::query()->where('attribute_group_id', $attributeGroup->id);

            $currentAttribute = (clone $scope)
                ->where('id', $attribute->id)
                ->lockForUpdate()
                ->firstOrFail();

            $maxSortOrder = (int) ((clone $scope)->max('sort_order') ?? 1);
            $currentSortOrder = max(1, (int) $currentAttribute->sort_order);
            $newSortOrder = min(max(1, (int) $validated['sort_order']), max(1, $maxSortOrder));

            if ($newSortOrder === $currentSortOrder) {
                return;
            }

            if ($newSortOrder < $currentSortOrder) {
                $shiftItems = (clone $scope)
                    ->where('id', '!=', $currentAttribute->id)
                    ->where('sort_order', '>=', $newSortOrder)
                    ->where('sort_order', '<', $currentSortOrder)
                    ->orderBy('sort_order', 'asc')
                    ->get();

                foreach ($shiftItems as $shiftItem) {
                    $shiftItem->update([
                        'sort_order' => max(1, (int) $shiftItem->sort_order + 1),
                    ]);
                }
            } else {
                $shiftItems = (clone $scope)
                    ->where('id', '!=', $currentAttribute->id)
                    ->where('sort_order', '<=', $newSortOrder)
                    ->where('sort_order', '>', $currentSortOrder)
                    ->orderBy('sort_order', 'asc')
                    ->get();

                foreach ($shiftItems as $shiftItem) {
                    $shiftItem->update([
                        'sort_order' => max(1, (int) $shiftItem->sort_order - 1),
                    ]);
                }
            }

            $currentAttribute->update([
                'sort_order' => max(1, $newSortOrder),
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Sıralama güncellendi.',
        ]);
    }

    public function validator($request, $modelId = null, $attributeGroupId = null)
    {
        $validators = [
            'name' => [
                'required',
                Rule::unique('attributes', 'name')
                    ->where(function ($query) use ($attributeGroupId) {
                        return $query->where('attribute_group_id', $attributeGroupId);
                    })
                    ->ignore($modelId),
            ],
            'name_en' => [
                'required',
                Rule::unique('attributes', 'name_en')
                    ->where(function ($query) use ($attributeGroupId) {
                        return $query->where('attribute_group_id', $attributeGroupId);
                    })
                    ->ignore($modelId),
            ],
        ];

        $messages = [
            'name.required' => 'Lutfen adini giriniz.',
            'name.unique' => 'Girdiginiz ad sistemde zaten mevcut.',
            'name_en.required' => 'Lutfen adini (EN) giriniz.',
            'name_en.unique' => 'Girdiginiz ad (EN) sistemde zaten mevcut.',
        ];

        return Validator::make($request, $validators, $messages);
    }

    public function tableData(AttributeGroup $attributeGroup, Request $request)
    {
        $search = $request->input('search', $request->input('q'));
        $status = $request->input('status');
        $perPage = max(1, (int) $request->input('per_page', 50));
        $page = max(1, (int) $request->input('page', 1));

        $items = Attribute::query()
            ->where('attribute_group_id', $attributeGroup->id)
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('status', (int) $status);
            })
            ->orderByRaw('COALESCE(sort_order, 999999) ASC')
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $items->getCollection()->map(function (Attribute $item) use ($attributeGroup) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'name_en' => $item->name_en,
                'status' => $item->status ? 'Aktif' : 'Pasif',
                'status_value' => (int) $item->status,
                'show_in_filter' => (bool) $item->show_in_filter,
                'sort_order' => max(1, (int) ($item->sort_order ?? 1)),
                'sort_order_url' => route('admin.catalog.product-attributes.attribute-groups.attributes.sort-order', [
                    'attributeGroup' => $attributeGroup->id,
                    'attribute' => $item->id,
                ]),
                'inline_update_url' => url('/admin/api/attributes/' . $item->id . '/inline'),
                'values_url' => route('admin.catalog.product-attributes.attributes.attribute-values.index', ['attribute' => $item->id]),
                'edit_url' => url('/admin/api/attributes/' . $item->id),
                'delete_url' => route('admin.catalog.product-attributes.attribute-groups.attributes.destroy', [
                    'attributeGroup' => $attributeGroup->id,
                    'attribute' => $item->id,
                ]),
            ];
        })->values();

        return response()->json([
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
        ]);
    }
}
