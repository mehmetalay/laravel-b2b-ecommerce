<?php

namespace App\Http\Controllers\Admin\Catalog\ProductAttribute;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\AttributeValue;
use App\Models\ProductAttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AttributeGroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        return view('admin.catalog.product-attribute.attribute-group.index');
    }

    public function create()
    {
        return view('admin.catalog.product-attribute.attribute-group.create');
    }

    public function store(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Lütfen formdaki hataları kontrol edin.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $model = AttributeGroup::create([
            'name' => (string) $request->input('name'),
            'slug' => Str::slug((string) $request->input('name'), '-'),
            'status' => (int) $request->input('status', 1),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Başarıyla eklendi.',
            'data' => [
                'id' => $model->id,
                'name' => $model->name,
                'status' => (bool) $model->status,
            ],
        ]);
    }

    public function show(AttributeGroup $attributeGroup)
    {
        return response()->json([
            'data' => [
                'id' => $attributeGroup->id,
                'name' => $attributeGroup->name,
                'status' => (bool) $attributeGroup->status,
            ],
        ]);
    }

    public function edit(AttributeGroup $attributeGroup)
    {
        return view('admin.catalog.product-attribute.attribute-group.edit', ['model' => $attributeGroup]);
    }

    public function update(Request $request, AttributeGroup $attributeGroup)
    {
        $validator = $this->validator($request->all(), $attributeGroup->id);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Lütfen formdaki hataları kontrol edin.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $attributeGroup->update([
            'name' => (string) $request->input('name'),
            'slug' => Str::slug((string) $request->input('name'), '-'),
            'status' => (int) $request->input('status', 0),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Başarıyla güncellendi.',
            'data' => [
                'id' => $attributeGroup->id,
                'name' => $attributeGroup->name,
                'status' => (bool) $attributeGroup->status,
            ],
        ]);
    }

    public function inlineUpdate(Request $request, AttributeGroup $attributeGroup)
    {
        $field = (string) $request->input('field', '');

        if (!in_array($field, ['name', 'status'], true)) {
            return response()->json([
                'message' => 'Lütfen formdaki hataları kontrol edin.',
                'errors' => [
                    'field' => ['Geçersiz alan.'],
                ],
            ], 422);
        }

        if ($field === 'name') {
            $validator = Validator::make($request->all(), [
                'value' => 'required|string|unique:attribute_groups,name,' . $attributeGroup->id,
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

            $attributeGroup->update([
                'name' => $name,
                'slug' => Str::slug($name, '-'),
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
            $attributeGroup->update([
                'status' => $statusValue,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Güncellendi.',
            'data' => [
                'id' => $attributeGroup->id,
                'name' => $attributeGroup->name,
                'status' => $attributeGroup->status ? 'Aktif' : 'Pasif',
                'status_value' => (int) $attributeGroup->status,
            ],
        ]);
    }

    public function destroy(AttributeGroup $attributeGroup)
    {
        $this->deleteAttributeGroup($attributeGroup);

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:activate,deactivate,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:attribute_groups,id'],
        ]);

        $ids = collect($validated['ids'])->map(fn ($id) => (int) $id)->values();
        $action = (string) $validated['action'];

        if ($action === 'activate') {
            AttributeGroup::query()->whereIn('id', $ids)->update(['status' => 1]);
        }

        if ($action === 'deactivate') {
            AttributeGroup::query()->whereIn('id', $ids)->update(['status' => 0]);
        }

        if ($action === 'delete') {
            $groups = AttributeGroup::query()->whereIn('id', $ids)->get();
            $groups->each(function (AttributeGroup $group): void {
                $this->deleteAttributeGroup($group);
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'İşlem başarıyla tamamlandı.',
        ]);
    }

    private function deleteAttributeGroup(AttributeGroup $attributeGroup): void
    {
        $attributes = Attribute::where('attribute_group_id', $attributeGroup->id)->get();

        $attributes->each(function (Attribute $attribute): void {
            $attributeValues = AttributeValue::where('attribute_id', $attribute->id)->get();

            $attributeValues->each(function (AttributeValue $attributeValue): void {
                ProductAttributeValue::where('attribute_value_id', $attributeValue->id)->delete();
                $attributeValue->delete();
            });

            $attribute->delete();
        });

        $attributeGroup->delete();
    }

    public function duplicate($id, Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = $this->validator($request->all());

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Lütfen formdaki hataları kontrol edin.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $group = AttributeGroup::with('attributes.attributeValues')->findOrFail($id);

            $newGroup = AttributeGroup::create([
                'name' => (string) $request->input('name'),
                'slug' => Str::slug((string) $request->input('name'), '-'),
                'status' => $group->status,
            ]);

            foreach ($group->attributes as $attribute) {
                $slug = Str::slug($attribute->name, '-');
                $originalSlug = $slug;
                $counter = 1;

                while (Attribute::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }

                $newAttribute = $newGroup->attributes()->create([
                    'name' => $attribute->name,
                    'name_en' => $attribute->name_en,
                    'slug' => $slug,
                    'status' => $attribute->status,
                    'sort_order' => max(1, (int) $attribute->sort_order),
                    'show_in_filter' => (int) ($attribute->show_in_filter ?? 1),
                ]);

                foreach ($attribute->attributeValues as $value) {
                    $valueSlug = Str::slug($value->name, '-');
                    $originalValueSlug = $valueSlug;
                    $valueCounter = 1;

                    while (AttributeValue::where('slug', $valueSlug)->exists()) {
                        $valueSlug = $originalValueSlug . '-' . $valueCounter;
                        $valueCounter++;
                    }

                    $newAttribute->attributeValues()->create([
                        'name' => $value->name,
                        'name_en' => $value->name_en,
                        'slug' => $valueSlug,
                        'status' => $value->status,
                        'sort_order' => max(1, (int) $value->sort_order),
                        'show_in_filter' => (int) ($value->show_in_filter ?? 1),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Başarıyla eklendi.',
                'data' => [
                    'id' => $newGroup->id,
                    'name' => $newGroup->name,
                    'status' => (bool) $newGroup->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Kopyalama sırasında bir hata oluştu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function validator($request, $modelId = null)
    {
        $validators = [
            'name' => 'required|unique:attribute_groups,name,' . $modelId,
        ];

        $messages = [
            'name.required' => 'Lutfen adini giriniz.',
            'name.unique' => 'Girdiginiz ad sistemde zaten mevcut.',
        ];

        return Validator::make($request, $validators, $messages);
    }

    public function tableData(Request $request)
    {
        $search = $request->input('search', $request->input('q'));
        $status = $request->input('status');
        $perPage = max(1, (int) $request->input('per_page', 50));
        $page = max(1, (int) $request->input('page', 1));

        $items = AttributeGroup::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('status', (int) $status);
            })
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $items->getCollection()->map(function (AttributeGroup $item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'status' => $item->status ? 'Aktif' : 'Pasif',
                'status_value' => (int) $item->status,
                'attributes_url' => route('admin.catalog.product-attributes.attribute-groups.attributes.index', ['attributeGroup' => $item->id]),
                'duplicate_url' => route('admin.catalog.product-attributes.attribute-groups.duplicate', $item->id),
                'edit_url' => url('/admin/api/attribute-groups/' . $item->id),
                'delete_url' => route('admin.catalog.product-attributes.attribute-groups.destroy', [$item->id]),
                'inline_update_url' => url('/admin/api/attribute-groups/' . $item->id . '/inline'),
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
