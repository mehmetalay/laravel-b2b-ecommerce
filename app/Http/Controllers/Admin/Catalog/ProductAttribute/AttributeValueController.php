<?php

namespace App\Http\Controllers\Admin\Catalog\ProductAttribute;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\ProductAttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AttributeValueController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Attribute $attribute)
    {
        return view('admin.catalog.product-attribute.attribute-value.index', compact('attribute'));
    }

    public function create(Attribute $attribute)
    {
        return view('admin.catalog.product-attribute.attribute-value.create', compact('attribute'));
    }

    public function store(Request $request, Attribute $attribute)
    {
        $validator = $this->validator($request->all(), null, $attribute->id);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Lütfen formdaki hataları kontrol edin.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $slug = Str::slug((string) $request->input('name'), '-');
        $originalSlug = $slug;
        $counter = 1;

        while (AttributeValue::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $model = AttributeValue::create([
            'attribute_id' => $attribute->id,
            'name' => (string) $request->input('name'),
            'name_en' => (string) $request->input('name_en'),
            'slug' => $slug,
            'status' => (int) $request->input('status', 1),
            'sort_order' => max(1, ((int) (AttributeValue::where('attribute_id', $attribute->id)->max('sort_order') ?? 0)) + 1),
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

    public function show(Attribute $attribute, AttributeValue $attributeValue)
    {
        if ((int) $attributeValue->attribute_id !== (int) $attribute->id) {
            abort(404);
        }

        return response()->json([
            'data' => [
                'id' => $attributeValue->id,
                'name' => $attributeValue->name,
                'name_en' => $attributeValue->name_en,
                'status' => (bool) $attributeValue->status,
                'show_in_filter' => (bool) $attributeValue->show_in_filter,
                'sort_order' => max(1, (int) ($attributeValue->sort_order ?? 1)),
            ],
        ]);
    }

    public function apiShow(AttributeValue $attributeValue)
    {
        return response()->json([
            'data' => [
                'id' => $attributeValue->id,
                'name' => $attributeValue->name,
                'name_en' => $attributeValue->name_en,
                'status' => (bool) $attributeValue->status,
                'show_in_filter' => (bool) $attributeValue->show_in_filter,
                'sort_order' => max(1, (int) ($attributeValue->sort_order ?? 1)),
            ],
        ]);
    }

    public function edit(Attribute $attribute, AttributeValue $attributeValue)
    {
        return view('admin.catalog.product-attribute.attribute-value.edit', ['attribute' => $attribute, 'model' => $attributeValue]);
    }

    public function update(Request $request, Attribute $attribute, AttributeValue $attributeValue)
    {
        $validator = $this->validator($request->all(), $attributeValue->id, $attribute->id);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Lütfen formdaki hataları kontrol edin.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $slug = $attributeValue->slug;
        if ((string) $attributeValue->name !== (string) $request->input('name')) {
            $slug = Str::slug((string) $request->input('name'), '-');
            $originalSlug = $slug;
            $counter = 1;

            while (AttributeValue::where('id', '!=', $attributeValue->id)->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $attributeValue->update([
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
                'id' => $attributeValue->id,
                'name' => $attributeValue->name,
                'name_en' => $attributeValue->name_en,
                'status' => (bool) $attributeValue->status,
                'show_in_filter' => (bool) $attributeValue->show_in_filter,
                'sort_order' => max(1, (int) ($attributeValue->sort_order ?? 1)),
            ],
        ]);
    }

    public function inlineUpdate(Request $request, AttributeValue $attributeValue)
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
                    Rule::unique('attribute_values', 'name')
                        ->where(function ($query) use ($attributeValue) {
                            return $query->where('attribute_id', $attributeValue->attribute_id);
                        })
                        ->ignore($attributeValue->id),
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

            while (AttributeValue::where('id', '!=', $attributeValue->id)->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $attributeValue->update([
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
            $attributeValue->update([
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

            DB::transaction(function () use ($attributeValue, $request): void {
                $currentValue = AttributeValue::query()->findOrFail($attributeValue->id);
                $scope = AttributeValue::query()->where('attribute_id', $currentValue->attribute_id);

                $maxSortOrder = (int) ((clone $scope)->max('sort_order') ?? 1);
                $currentSortOrder = max(1, (int) $currentValue->sort_order);
                $newSortOrder = min(max(1, (int) $request->input('value')), max(1, $maxSortOrder));

                if ($newSortOrder === $currentSortOrder) {
                    return;
                }

                if ($newSortOrder < $currentSortOrder) {
                    $toShift = (clone $scope)
                        ->where('id', '!=', $currentValue->id)
                        ->where('sort_order', '>=', $newSortOrder)
                        ->where('sort_order', '<', $currentSortOrder)
                        ->orderBy('sort_order', 'asc')
                        ->get();

                    $toShift->each(function (AttributeValue $shiftItem): void {
                        $shiftItem->update([
                            'sort_order' => max(1, (int) $shiftItem->sort_order + 1),
                        ]);
                    });
                } else {
                    $toShift = (clone $scope)
                        ->where('id', '!=', $currentValue->id)
                        ->where('sort_order', '<=', $newSortOrder)
                        ->where('sort_order', '>', $currentSortOrder)
                        ->orderBy('sort_order', 'asc')
                        ->get();

                    $toShift->each(function (AttributeValue $shiftItem): void {
                        $shiftItem->update([
                            'sort_order' => max(1, (int) $shiftItem->sort_order - 1),
                        ]);
                    });
                }

                $currentValue->update([
                    'sort_order' => max(1, $newSortOrder),
                ]);
            });

            $attributeValue->refresh();
        }

        return response()->json([
            'success' => true,
            'message' => 'Güncellendi.',
            'data' => [
                'id' => $attributeValue->id,
                'name' => $attributeValue->name,
                'status' => $attributeValue->status ? 'Aktif' : 'Pasif',
                'status_value' => (int) $attributeValue->status,
                'sort_order' => max(1, (int) ($attributeValue->sort_order ?? 1)),
            ],
        ]);
    }

    public function destroy(Attribute $attribute, AttributeValue $attributeValue)
    {
        $this->deleteAttributeValue($attributeValue);

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:activate,deactivate,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:attribute_values,id'],
        ]);

        $ids = collect($validated['ids'])->map(fn ($id) => (int) $id)->values();
        $action = (string) $validated['action'];

        if ($action === 'activate') {
            AttributeValue::query()->whereIn('id', $ids)->update(['status' => 1]);
        }

        if ($action === 'deactivate') {
            AttributeValue::query()->whereIn('id', $ids)->update(['status' => 0]);
        }

        if ($action === 'delete') {
            $attributeValues = AttributeValue::query()->whereIn('id', $ids)->get();
            $attributeValues->each(function (AttributeValue $attributeValue): void {
                $this->deleteAttributeValue($attributeValue);
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'İşlem başarıyla tamamlandı.',
        ]);
    }

    private function deleteAttributeValue(AttributeValue $attributeValue): void
    {
        ProductAttributeValue::where('attribute_value_id', $attributeValue->id)->delete();
        $attributeValue->delete();
    }

    public function sort(Attribute $attribute, Request $request)
    {
        $orderItems = collect($request->input('order', []))
            ->filter(fn ($item) => isset($item['id'], $item['sort_order']))
            ->values()
            ->all();

        DB::transaction(function () use ($attribute, $orderItems): void {
            $ids = collect($orderItems)->pluck('id')->map(fn ($id) => (int) $id)->all();

            $values = AttributeValue::query()
                ->where('attribute_id', $attribute->id)
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');

            foreach ($orderItems as $item) {
                $value = $values->get((int) $item['id']);

                if (!$value) {
                    continue;
                }

                $value->update([
                    'sort_order' => max(1, (int) $item['sort_order']),
                ]);
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Sıralama güncellendi.',
        ]);
    }

    public function updateSortOrder(Request $request, Attribute $attribute, AttributeValue $attributeValue)
    {
        $validated = $request->validate([
            'sort_order' => ['required', 'integer', 'min:1'],
        ]);

        if ((int) $attributeValue->attribute_id !== (int) $attribute->id) {
            abort(404);
        }

        DB::transaction(function () use ($attribute, $attributeValue, $validated): void {
            $scope = AttributeValue::query()->where('attribute_id', $attribute->id);

            $currentValue = (clone $scope)
                ->where('id', $attributeValue->id)
                ->lockForUpdate()
                ->firstOrFail();

            $maxSortOrder = (int) ((clone $scope)->max('sort_order') ?? 1);
            $currentSortOrder = max(1, (int) $currentValue->sort_order);
            $newSortOrder = min(max(1, (int) $validated['sort_order']), max(1, $maxSortOrder));

            if ($newSortOrder === $currentSortOrder) {
                return;
            }

            if ($newSortOrder < $currentSortOrder) {
                $shiftItems = (clone $scope)
                    ->where('id', '!=', $currentValue->id)
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
                    ->where('id', '!=', $currentValue->id)
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

            $currentValue->update([
                'sort_order' => max(1, $newSortOrder),
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Sıralama güncellendi.',
        ]);
    }

    public function validator($request, $modelId = null, $attributeId = null)
    {
        $validators = [
            'name' => [
                'required',
                Rule::unique('attribute_values', 'name')
                    ->where(function ($query) use ($attributeId) {
                        return $query->where('attribute_id', $attributeId);
                    })
                    ->ignore($modelId),
            ],
            'name_en' => [
                'required',
                Rule::unique('attribute_values', 'name_en')
                    ->where(function ($query) use ($attributeId) {
                        return $query->where('attribute_id', $attributeId);
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

    public function tableData(Attribute $attribute, Request $request)
    {
        $search = $request->input('search', $request->input('q'));
        $status = $request->input('status');
        $perPage = max(1, (int) $request->input('per_page', 50));
        $page = max(1, (int) $request->input('page', 1));

        $items = AttributeValue::query()
            ->where('attribute_id', $attribute->id)
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('status', (int) $status);
            })
            ->orderByRaw('COALESCE(sort_order, 999999) ASC')
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $items->getCollection()->map(function (AttributeValue $item) use ($attribute) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'name_en' => $item->name_en,
                'status' => $item->status ? 'Aktif' : 'Pasif',
                'status_value' => (int) $item->status,
                'show_in_filter' => (bool) $item->show_in_filter,
                'sort_order' => max(1, (int) ($item->sort_order ?? 1)),
                'sort_order_url' => route('admin.catalog.product-attributes.attributes.attribute-values.sort-order', [
                    'attribute' => $attribute->id,
                    'attributeValue' => $item->id,
                ]),
                'inline_update_url' => url('/admin/api/attribute-values/' . $item->id . '/inline'),
                'edit_url' => url('/admin/api/attribute-values/' . $item->id),
                'delete_url' => route('admin.catalog.product-attributes.attributes.attribute-values.destroy', [
                    'attribute' => $attribute->id,
                    'attribute_value' => $item->id,
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
