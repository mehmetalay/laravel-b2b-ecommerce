<?php

namespace App\Services;

use ZipArchive;

class FileZipService
{
    /**
     * @param iterable $products  // içinde product modeli olan koleksiyon
     * @param string   $prefix    // dosya adı prefix (örn: 'sepet', 'siparis')
     * @return string             // zip dosyasının path'i
     */
    public function createImageZip(iterable $products, string $prefix = 'urunler'): string
    {
        $uniqid = uniqid();
        $zipFileName = $prefix === 'urunler' ? "urun-resimleri-{$uniqid}.zip" : "{$prefix}-urun-resimleri-{$uniqid}.zip";
        $zipFilePath = storage_path("app/public/{$zipFileName}");

        $zip = new ZipArchive;

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($products as $item) {
                $product = $item->product ?? $item;

                if (!$product || $product->image_1 === 'urun-gorseli-hazirlaniyor.jpg') {
                    continue;
                }

                $imagePath = public_path($product->image_large_url_1_raw);

                if (file_exists($imagePath)) {
                    $zip->addFile($imagePath, $product->code . '-' . basename($imagePath));
                }
            }

            $zip->close();
        }

        return $zipFilePath;
    }
}
