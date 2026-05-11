<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Filters\BrandFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\BrandRequest;
use App\Models\Brand;
use App\Application\Brand\Repositories\BrandRepository;
use App\Application\Brand\Services\BrandService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function __construct(
        protected BrandService $service,
        protected BrandRepository $repository
    ) {
        $this->middleware('auth:admin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, BrandFilters $filters)
    {
        return view('admin.catalog.brands.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.catalog.brands.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BrandRequest $request)
    {
        $brand = $this->service->create(
            $request->validated(),
            $request->file('image')
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Başarıyla eklendi.',
            'redirect' => route('admin.catalog.brands.edit', [$brand->id])
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function show(Brand $brand)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function edit(Brand $brand)
    {
        return view('admin.catalog.brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function update(BrandRequest $request, Brand $brand)
    {
        $this->service->update(
            $brand,
            $request->validated(),
            $request->file('image')
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Başarıyla güncellendi.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function destroy(Brand $brand)
    {
        $this->service->delete($brand);

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function sort(Request $request)
    {
        $orderItems = collect($request->input('order', []))
            ->filter(fn ($item) => isset($item['id'], $item['sort_order']))
            ->values()
            ->all();

        $this->service->updateSortOrders($orderItems);

        return response()->json([
            'status' => 'success',
            'message' => 'Sıralama güncellendi.',
        ]);
    }

    public function updateSortOrder(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'sort_order' => ['required', 'integer', 'min:1'],
        ]);

        $this->service->updateSortOrder($brand, (int) $validated['sort_order']);

        return response()->json([
            'status' => 'success',
            'message' => 'Sıralama güncellendi.',
        ]);
    }

    public function inlineUpdate(Request $request, Brand $brand)
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
                    Rule::unique('brands', 'name')
                        ->ignore($brand->id)
                        ->whereNull('deleted_at'),
                ],
            ], [
                'value.required' => 'Lütfen adını giriniz.',
                'value.unique' => 'Girdiğiniz ad sistemde zaten mevcut.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Lütfen formdaki hataları kontrol edin.',
                    'errors' => [
                        'name' => $validator->errors()->get('value'),
                    ],
                ], 422);
            }

            $name = str_upper(trim((string) $request->input('value')));
            $slug = str_slug($name);

            $this->repository->update($brand, [
                'name' => $name,
                'slug' => $slug,
            ]);
        }

        if ($field === 'status') {
            $validator = Validator::make($request->all(), [
                'value' => 'required|in:0,1,true,false',
            ], [
                'value.required' => 'Durum zorunludur.',
                'value.in' => 'Durum geçersiz.',
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

            $this->repository->update($brand, [
                'status' => $statusValue,
            ]);
        }

        if ($field === 'sort_order') {
            $validator = Validator::make($request->all(), [
                'value' => 'required|integer|min:1',
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

            $this->service->updateSortOrder($brand, (int) $request->input('value'));
        }

        $brand->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Güncellendi.',
            'data' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'status' => $brand->status ? 'Aktif' : 'Pasif',
                'status_value' => (int) $brand->status,
                'sort_order' => (int) $brand->sort_order,
            ],
        ]);
    }

    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:activate,deactivate,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', Rule::exists('brands', 'id')->whereNull('deleted_at')],
        ]);

        $ids = collect($validated['ids'])->map(fn ($id) => (int) $id)->values();
        $action = (string) $validated['action'];

        if ($action === 'activate' || $action === 'deactivate') {
            $statusValue = $action === 'activate' ? 1 : 0;

            $brands = Brand::query()
                ->whereNull('deleted_at')
                ->whereIn('id', $ids)
                ->get();

            $brands->each(function (Brand $brand) use ($statusValue): void {
                $this->repository->update($brand, [
                    'status' => $statusValue,
                ]);
            });
        }

        if ($action === 'delete') {
            $brands = Brand::query()
                ->whereNull('deleted_at')
                ->whereIn('id', $ids)
                ->get();

            $brands->each(function (Brand $brand): void {
                $this->service->delete($brand);
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'İşlem başarıyla tamamlandı.',
        ]);
    }

    public function tableData(Request $request)
    {
        $search = $request->input('search', $request->input('q'));
        $status = $request->input('status');
        $perPage = max(1, (int) $request->input('per_page', 50));
        $page = max(1, (int) $request->input('page', 1));

        $items = Brand::query()
            ->whereNull('deleted_at')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%");
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('status', (int) $status);
            })
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $items->getCollection()->map(function (Brand $item) {
            return [
                'id' => $item->id,
                'image_url' => $item->image_url,
                'name' => $item->name,
                'status' => $item->status ? 'Aktif' : 'Pasif',
                'status_value' => (int) $item->status,
                'sort_order' => $item->sort_order,
                'sort_order_url' => route('admin.catalog.brands.sort-order', [$item->id]),
                'edit_url' => route('admin.catalog.brands.edit', [$item->id]),
                'inline_update_url' => url('/admin/api/brands/' . $item->id . '/inline'),
                'created_at' => $item->created_at ? $item->created_at->format('d.m.Y H:i') : '-',
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
