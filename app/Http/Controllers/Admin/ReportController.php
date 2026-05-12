<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function noImageProducts()
    {
        $items = Product::where('image_1', 'urun-gorseli-hazirlaniyor.jpg')
            ->when($name = request()->get('name'), function ($query) use ($name) {
                $query->where('name', 'LIKE', "%{$name}%")
                    ->orWhere('code', 'LIKE', "%{$name}%");
            })
            ->when($status = request()->get('status'), function ($query) use ($status) {
                $query->where('status', $status === 'active' ? 1 : 0);
            })
            ->paginate(50);

        return view('backend.pages.dashboard.reports.no-image-products', compact('items'));
    }

    public function productsWithoutQuantity()
    {
        $items = Product::where('stock', '<', 0)
            ->when($name = request()->get('name'), function ($query) use ($name) {
                $query->where('name', 'LIKE', "%{$name}%")
                    ->orWhere('code', 'LIKE', "%{$name}%");
            })
            ->whereNotNull('category_id')
            ->paginate(50);

        return view('backend.pages.dashboard.reports.products-without-quantity', compact('items'));
    }

    public function inactiveProducts()
    {
        $items = Product::where('status', 0)
            ->when($name = request()->get('name'), function ($query) use ($name) {
                $query->where('name', 'LIKE', "%{$name}%")
                    ->orWhere('code', 'LIKE', "%{$name}%");
            })
            ->whereNotNull('category_id')
            ->paginate(50);

        return view('backend.pages.dashboard.reports.inactive-products', compact('items'));
    }

    public function nonSplittablePackages()
    {
        $items = Product::where('box_quantity_must_be_exact', 1)
            ->when($name = request()->get('name'), function ($query) use ($name) {
                $query->where('name', 'LIKE', "%{$name}%")
                    ->orWhere('code', 'LIKE', "%{$name}%");
            })
            ->when($status = request()->get('status'), function ($query) use ($status) {
                $query->where('status', $status === 'active' ? 1 : 0);
            })
            ->when($stockQuantity = request()->get('stock_quantity'), function ($query) use ($stockQuantity) {
                if ($stockQuantity === 'zero_or_less') {
                    $query->where('stock', '<=', 0);
                } elseif ($stockQuantity === 'positive') {
                    $query->where('stock', '>', 0);
                }
            })
            ->when($packageQuantity = request()->get('package_quantity'), function ($query) use ($packageQuantity) {
                if ($packageQuantity === 'zero_or_less') {
                    $query->where('box_quantity', '<=', 0);
                } elseif ($packageQuantity === 'positive') {
                    $query->where('box_quantity', '>', 0);
                }
            })
            ->paginate(50);

        return view('backend.pages.dashboard.reports.non-splittable-packages', compact('items'));
    }
}