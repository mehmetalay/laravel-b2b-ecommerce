<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function suggestions(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([
                'status' => 'success',
                'data' => [],
            ]);
        }

        $terms = explode(' ', $q);

        $products = Product::query()
            ->where('status', 1)
            ->where(function ($query) use ($terms) {
                foreach ($terms as $term) {
                    $query->where(function ($subQuery) use ($term) {
                        $subQuery->where('name', 'like', "%{$term}%")
                                ->orWhere('code', 'like', "%{$term}%");
                    });
                }
            })
            ->select('id', 'name', 'code', 'slug', 'image_1')
            ->limit(8)
            ->get()
            ->map(function ($product) {
                return [
                    'name' => $product->name,
                    'code' => $product->code,
                    'url' => route('product.detail', $product->slug),
                    'image' => $product->image_small_url_1,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $products,
        ]);
    }
}
