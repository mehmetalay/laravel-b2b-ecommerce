<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Jobs\RunExportJob;
use App\Models\ExportJob;
use App\Models\Attribute;
use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Services\Exports\ProductExportQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    protected ProductExportQueryService $productExportQueryService;

    public function __construct(ProductExportQueryService $productExportQueryService)
    {
        $this->middleware('auth:admin');
        $this->productExportQueryService = $productExportQueryService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.pages.catalog.products.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.pages.catalog.products.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        return view('backend.pages.catalog.products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $product->update([
            'is_special_currency' => $request->has('is_special_currency'),
            'special_currency_rate' => str_replace(',', '', request('special_currency_rate')),
        ]);

        $this->syncProductFiles($product, $request->file('files', []), $request->input('files', []));

        return response()->json([
            'status' => 'success',
            'message' => 'Ürün başarıyla güncellendi.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function search(Request $request)
    {
        $search = $request->input('q');
        $categoryId = $request->input('category_id');
        $brandId = $request->input('brand_id');

        $products = Product::select('id', 'name', 'code', 'code_group', 'barcode', 'price_1', 'price_1_currency', 'price_3', 'price_3_currency', 'category_id' , 'brand_id')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('code', 'LIKE', "%{$search}%")
                        ->orWhere('code_group', 'LIKE', "%{$search}%")
                        ->orWhere('barcode', 'LIKE', "%{$search}%")
                        ->orWhereHas('brand', function ($q2) use ($search) {
                            $q2->where('name', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($brandId, function ($query) use ($brandId) {
                $query->where('brand_id', $brandId);
            })
            ->orderBy('name', 'asc')
            ->get();

        return response()->json($products);
    }

    public function listAttributes(Request $request)
    {
        $validated = $request->validate([
            'attribute_group_id' => ['required', 'integer', 'exists:attribute_groups,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
        ]);

        $attributes = Attribute::query()
            ->where('attribute_group_id', (int) $validated['attribute_group_id'])
            ->with('attributeValues')
            ->orderBy('name')
            ->get();

        $selectedAttributeValueIds = [];
        if (!empty($validated['product_id'])) {
            $product = Product::query()->with('attributeValues:id')->find((int) $validated['product_id']);
            $selectedAttributeValueIds = $product
                ? $product->attributeValues->pluck('id')->map(fn ($id) => (int) $id)->all()
                : [];
        }

        return view('backend.pages.catalog.products.partials._attributes', [
            'attributes' => $attributes,
            'productId' => (int) ($validated['product_id'] ?? 0),
            'selectedAttributeValueIds' => $selectedAttributeValueIds,
        ]);
    }

    public function tableData(Request $request)
    {
        $filters = $this->resolveTableFilters($request);
        $perPage = max(1, (int) $request->input('per_page', 50));
        $page = max(1, (int) $request->input('page', 1));

        $items = $this->productExportQueryService
            ->build($filters)
            ->orderBy('name', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $items->getCollection()->map(function (Product $item) {
            return [
                'id' => $item->id,
                'image_url' => $item->image_small_url_1,
                'name' => $item->product_name,
                'category' => $item->category_name,
                'brand' => $item->brand_name,
                'price' => $this->formatPrice($item),
                'stock' => $item->stock,
                'status' => $item->status ? 'Aktif' : 'Pasif',
                'status_value' => (int) $item->status,
                'inline_update_url' => url('/admin/api/products/' . $item->id . '/inline'),
                'edit_url' => route('admin.catalog.products.edit', [$item->id]),
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
            'filters' => (object) [],
        ]);
    }

    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:activate,deactivate,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:products,id'],
        ]);

        $ids = collect($validated['ids'])->map(fn ($id) => (int) $id)->values();
        $action = (string) $validated['action'];

        if ($action === 'activate') {
            Product::query()->whereIn('id', $ids)->update(['status' => 1]);
        }

        if ($action === 'deactivate') {
            Product::query()->whereIn('id', $ids)->update(['status' => 0]);
        }

        if ($action === 'delete') {
            Product::query()->whereIn('id', $ids)->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'İşlem başarıyla tamamlandı.',
        ]);
    }

    public function inlineUpdate(Request $request, Product $product)
    {
        $field = (string) $request->input('field', '');

        if ($field !== 'status') {
            return response()->json([
                'message' => 'Lütfen formdaki hataları kontrol edin.',
                'errors' => [
                    'field' => ['Geçersiz alan.'],
                ],
            ], 422);
        }

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
        $product->update(['status' => $statusValue]);

        return response()->json([
            'status' => 'success',
            'message' => 'Güncellendi.',
            'data' => [
                'id' => $product->id,
                'status' => $product->status ? 'Aktif' : 'Pasif',
                'status_value' => (int) $product->status,
            ],
        ]);
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'scope' => ['required', 'in:filtered,selected'],
            'ids' => ['required_if:scope,selected', 'array'],
            'ids.*' => ['required_if:scope,selected', 'integer', 'exists:products,id'],
        ]);

        $scope = (string) $validated['scope'];
        $filters = $scope === 'selected' ? [] : $this->resolveTableFilters($request);
        $filename = 'products-' . $scope . '-' . now()->format('Ymd-His') . '.csv';
        $selectedIds = $scope === 'selected'
            ? collect($validated['ids'] ?? [])->map(fn ($id) => (int) $id)->values()->all()
            : null;
        $query = $this->productExportQueryService->build($filters, $selectedIds);

        $query->orderBy('id', 'asc');

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'wb');
            if ($output === false) {
                return;
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['ID', 'URUN ADI', 'KATEGORI', 'MARKA', 'FIYAT', 'STOK', 'DURUM']);

            $query->chunkById(500, function ($products) use ($output) {
                foreach ($products as $item) {
                    fputcsv($output, [
                        $item->id,
                        $item->product_name,
                        $item->category_name,
                        $item->brand_name,
                        $this->parsePriceForExport($item->product_price_show),
                        (int) $item->stock,
                        $item->status ? 'Aktif' : 'Pasif',
                    ]);
                }
            });

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function createExport(Request $request)
    {
        $validated = $request->validate([
            'format' => ['required', 'in:xlsx,csv'],
            'scope' => ['required', 'in:filtered,selected'],
            'filters' => ['nullable', 'array'],
            'ids' => ['required_if:scope,selected', 'array'],
            'ids.*' => ['required_if:scope,selected', 'integer', 'exists:products,id'],
        ]);

        $scope = (string) $validated['scope'];
        $selectedIds = $scope === 'selected'
            ? collect($validated['ids'] ?? [])->map(fn ($id) => (int) $id)->values()->all()
            : null;
        $filters = $scope === 'selected'
            ? []
            : (is_array($validated['filters'] ?? null) ? $validated['filters'] : []);
        $adminUser = $request->user('admin');

        $exportJob = ExportJob::query()->create([
            'user_type' => $adminUser ? 'admin' : null,
            'user_id' => $adminUser?->id,
            'type' => 'products',
            'format' => (string) $validated['format'],
            'scope' => $scope,
            'filters' => $filters,
            'selected_ids' => $selectedIds,
            'status' => 'pending',
        ]);

        RunExportJob::dispatch((int) $exportJob->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Dışa aktarma kuyruğa alındı',
            'export_job_id' => $exportJob->id,
        ]);
    }

    private function formatPrice(Product $product): string
    {
        return trim(strip_tags((string) $product->product_price_show));
    }

    private function parsePriceForExport($value): float
    {
        $parsed = strip_tags((string) $value);
        $parsed = str_replace(['₺', 'TL', ' ', "\xc2\xa0"], '', $parsed);
        $parsed = str_replace('.', '', $parsed);
        $parsed = str_replace(',', '.', $parsed);

        return (float) $parsed;
    }

    private function resolveTableFilters(Request $request): array
    {
        return [
            'search' => $request->input('search', $request->input('q')),
            'category_id' => $request->input('category_id'),
            'brand_id' => $request->input('brand_id'),
            'status' => $request->input('status'),
            'stock_status' => $request->input('stock_status'),
        ];
    }

    protected function syncProductFiles(Product $product, $uploadedFiles, $inputFiles)
    {
        $existingIds = collect($inputFiles)
            ->pluck('id')
            ->filter()
            ->toArray();

        $deletedFiles = $product->files()
            ->whereNotIn('id', $existingIds)
            ->get();

        foreach ($deletedFiles as $df) {
            if ($df->type === 'file' && File::exists(public_path("product-files/{$df->value}"))) {
                File::delete(public_path("product-files/{$df->value}"));
            }
        }

        $product->files()->whereNotIn('id', $existingIds)->delete();

        foreach ($inputFiles as $index => $fileData) {
            $id = $fileData['id'] ?? null;
            $type = $fileData['type'] ?? 'file';
            $name = $fileData['name'] ?? null;

            $value = $fileData['old_value'] ?? null;

            if ($type === 'file' && isset($uploadedFiles[$index]['value'])) {
                $file = $uploadedFiles[$index]['value'];

                $ext = $file->getClientOriginalExtension();
                $fileName = str_slug("{$name}-{$product->code}-" . uniqid()) . ".{$ext}";

                $file->move(public_path('product-files'), $fileName);
                $value = "{$fileName}";

            } elseif ($type === 'link') {
                $value = $fileData['value'] ?? null;
            }

            if ($id) {
                $product->files()->where('id', $id)->update([
                    'name' => $name,
                    'type' => $type,
                    'value' => $value,
                ]);
            } else {
                $product->files()->create([
                    'name' => $name,
                    'type' => $type,
                    'value' => $value,
                ]);
            }
        }
    }
}

