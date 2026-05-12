<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\HomepageBlockRequest;
use App\Models\HomepageBlock;
use App\Services\HomepageBlockService;
use Illuminate\Http\Request;

class HomepageBlockController extends Controller
{
    protected $service;

    public function __construct(HomepageBlockService $service)
    {
        $this->middleware('auth:admin');
        $this->service = $service;
    }

    public function index()
    {
        $items = HomepageBlock::when($name = request()->get('name'), function ($query) use ($name) {
                $query->where('title_tr', 'like', "%$name%");
            })
            ->orderBy('sort_order')
            ->paginate(50);

        return view('backend.pages.catalog.homepage-blocks.index', compact('items'));
    }

    public function create()
    {
        return view('backend.pages.catalog.homepage-blocks.create');
    }

    public function store(HomepageBlockRequest $request)
    {
        $model = $this->service->create($request);

        $rowHtml = view('backend.pages.catalog.homepage-blocks._row', compact('model'))->render();

        return response()->json([
            'status' => 'success',
            'message' => 'Başarıyla Eklendi.',
            'type' => 'add',
            'row' => $rowHtml
        ]);
    }

    public function edit(HomepageBlock $homepageBlock)
    {
        return view('backend.pages.catalog.homepage-blocks.edit', ['model' => $homepageBlock]);
    }

    public function update(HomepageBlockRequest $request, HomepageBlock $homepageBlock)
    {
        $this->service->update($request, $homepageBlock);

        $rowHtml = view('backend.pages.catalog.homepage-blocks._row', ['model' => $homepageBlock])->render();

        return response()->json([
            'status' => 'success',
            'message' => 'Başarıyla Güncellendi.',
            'type' => 'edit',
            'row' => $rowHtml,
            'id' => $homepageBlock->id
        ]);
    }

    public function destroy(HomepageBlock $homepageBlock)
    {
        $this->service->delete($homepageBlock);

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function products(HomepageBlock $homepageBlock)
    {
        $items = $homepageBlock->products()->get();

        return view('backend.pages.catalog.homepage-blocks.products', compact('homepageBlock', 'items'));
    }

    public function addProducts(Request $request, HomepageBlock $homepageBlock)
    {
        $ids = $request->input('product_ids', []);
        $homepageBlock->products()->sync($ids);
        $this->service->clearCache();

        return response()->json([
            'status' => 'success',
            'message' => 'Ürün listesi başarıyla güncellendi.',
        ]);
    }
}

