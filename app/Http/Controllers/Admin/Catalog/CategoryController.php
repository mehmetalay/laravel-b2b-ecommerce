<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Application\Category\Queries\AdminCategoryTableDataQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Application\Category\Repositories\CategoryRepository;
use App\Application\Category\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    protected $service;
    protected $repository;
    protected $adminCategoryTableDataQuery;

    public function __construct(
        CategoryService $service,
        CategoryRepository $repository,
        AdminCategoryTableDataQuery $adminCategoryTableDataQuery
    ) {
        $this->middleware('auth:admin');
        $this->service = $service;
        $this->repository = $repository;
        $this->adminCategoryTableDataQuery = $adminCategoryTableDataQuery;
    }

    public function index()
    {
        return view('backend.pages.catalog.categories.index');
    }

    public function create()
    {
        $categories = $this->service->buildTree($this->service->getAllCategories());

        return view('backend.pages.catalog.categories.create', compact('categories'));
    }

    public function store(CategoryRequest $request)
    {
        $category = $this->service->create($request);

        return response()->json([
            'status' => 'success',
            'message' => 'Başarıyla eklendi.',
            'redirect' => route('admin.catalog.categories.edit', [$category->id]),
        ]);
    }

    public function show(string $id)
    {
        //
    }

    public function edit(Category $category)
    {
        $categories = $this->service->buildTree($this->service->getAllCategories()->where('id', '!=', $category->id));

        return view('backend.pages.catalog.categories.edit', compact('category', 'categories'));
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $this->service->update($request, $category);

        return response()->json([
            'status' => 'success',
            'message' => 'Başarıyla güncellendi.',
        ]);
    }

    public function destroy(Category $category)
    {
        $this->service->delete($category);

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function sort(Request $request)
    {
        $value = $request->input('value');

        $this->service->updateSortOrders(
            $request->order,
            $value
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Sıralama güncellendi.',
        ]);
    }

    public function updateSortOrder(Request $request, Category $category)
    {
        $validated = $request->validate([
            'sort_order' => ['required', 'integer', 'min:1'],
        ]);

        $this->service->updateSortOrder($category, (int) $validated['sort_order']);

        return response()->json([
            'status' => 'success',
            'message' => 'Sıralama güncellendi.',
        ]);
    }

    public function inlineUpdate(Request $request, Category $category)
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
                    Rule::unique('categories', 'name')->ignore($category->id),
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

            $name = str_upper(trim((string) $request->input('value')));
            $slug = Str::slug($name, '-');

            if ($category->parent_id) {
                $slug = $this->service->buildFullSlug((int) $category->parent_id, $slug);
            }

            $originalSlug = $slug;
            $counter = 1;

            while (Category::query()->where('id', '!=', $category->id)->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $oldSlug = (string) $category->slug;

            $category->update([
                'name' => $name,
                'slug' => $slug,
            ]);

            $this->repository->clearCache($oldSlug);
            if ($oldSlug !== $slug) {
                $this->repository->clearCache($slug);
            }
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
            $oldSlug = (string) $category->slug;

            $category->update([
                'status' => $statusValue,
            ]);

            $this->repository->clearCache($oldSlug);
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

            $this->service->updateSortOrder($category, (int) $request->input('value'));
            $category->refresh();
        }

        return response()->json([
            'success' => true,
            'message' => 'Güncellendi.',
            'data' => [
                'id' => $category->id,
                'name' => $this->normalizeUtf8($category->name),
                'status' => $category->status ? 'Aktif' : 'Pasif',
                'status_value' => (int) $category->status,
                'sort_order' => (int) $category->sort_order,
            ],
        ]);
    }

    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:activate,deactivate,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
        ]);

        $ids = collect($validated['ids'])->map(fn ($id) => (int) $id)->values();
        $action = (string) $validated['action'];

        if ($action === 'activate' || $action === 'deactivate') {
            $statusValue = $action === 'activate' ? 1 : 0;

            $categories = Category::query()
                ->notDeleted()
                ->whereIn('id', $ids)
                ->get();

            $categories->each(function (Category $category) use ($statusValue): void {
                $oldSlug = (string) $category->slug;

                if ((int) $category->status !== $statusValue) {
                    $category->update(['status' => $statusValue]);
                }

                $this->repository->clearCache($oldSlug);
            });
        }

        if ($action === 'delete') {
            $categories = Category::query()
                ->notDeleted()
                ->whereIn('id', $ids)
                ->get();

            $categories->each(function (Category $category): void {
                $this->service->delete($category);
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'İşlem başarıyla tamamlandı.',
        ]);
    }

    public function tableData(Request $request)
    {
        return response()->json($this->adminCategoryTableDataQuery->handle($request));
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

