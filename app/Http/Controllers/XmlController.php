<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Spatie\ArrayToXml\ArrayToXml;
use Illuminate\Support\Facades\Cache;

class XmlController extends Controller
{
    public function __construct()
    {
        //
    }

    public function product()
    {
        // Cache::forget('productXml');

        $xml = Cache::remember('productXml', 7200, function () {

            $products = Product::with(['category.parent', 'brand'])
                ->where('status', 1)
                ->get()
                ->map(function ($product) {

                    $base = $this->baseProductFields($product);

                    return array_merge($base, [
                        'marka_adi' => $product->brand?->name ?? null,
                        'marka_resim' => $product->brand?->image_url ?? null,
                        'ana_kategori_adi' => $product->category?->parent?->name ?? null,
                        'alt_kategori_adi' => $product->category?->name ?? null,
                        'kategori_resim' => $product->category?->parent->image_url ?? null,
                    ]);
                })
                ->toArray();

            return ArrayToXml::convert(['urun' => $products], 'urunler', true, 'UTF-8');
        });

        return response($xml)->header('Content-Type', 'application/xml');
    }

    public function category()
    {
        // Cache::forget('categoryXml');

        $xml = Cache::remember('categoryXml', 7200, function () {

            $products = Product::with(['category.parent'])
                ->where('status', 1)
                ->get()
                ->map(function ($product) {

                    $base = $this->baseProductFields($product);

                    return array_merge($base, [
                        'ana_kategori_adi' => $product->category?->parent?->name ?? null,
                        'alt_kategori_adi' => $product->category?->name ?? null,
                        'kategori_resim' => $product->category?->parent->image_url ?? null,
                    ]);
                })
                ->toArray();

            return ArrayToXml::convert(['urun' => $products], 'urunler', true, 'UTF-8');
        });

        return response($xml)->header('Content-Type', 'application/xml');
    }

    public function brand()
    {
        // Cache::forget('brandXml');

        $xml = Cache::remember('brandXml', 7200, function () {

            $products = Product::with('brand')
                ->where('status', 1)
                ->get()
                ->map(function ($product) {

                    $base = $this->baseProductFields($product);

                    return array_merge($base, [
                        'marka_adi' => $product->brand?->name ?? null,
                        'marka_resim' => $product->brand?->image_url ?? null,
                    ]);
                })
                ->toArray();

            return ArrayToXml::convert(['urun' => $products], 'urunler', true, 'UTF-8');
        });

        return response($xml)->header('Content-Type', 'application/xml');
    }

    private function baseProductFields($product)
    {
        return [
            'id' => $product->id,
            'urun_adi' => $product->name,
            'urun_kodu' => $product->code,
            'barkod' => $product->barcode,
            'stok' => $product->stock,
            'durum' => $product->status,
            'resim_1' => $product->image_large_url_1_raw,
            'resim_2' => $product->image_large_url_2_raw,
            'resim_3' => $product->image_large_url_3_raw,
            'olusturulma_tarihi' => optional($product->created_at)->toDateTimeString(),
            'guncellenme_tarihi' => optional($product->updated_at)->toDateTimeString(),
        ];
    }
}
