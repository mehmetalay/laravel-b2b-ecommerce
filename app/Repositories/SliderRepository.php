<?php

namespace App\Repositories;

use App\Models\Slider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class SliderRepository
{
    protected function cacheKey(string $type)
    {
        $keys = [];

        foreach (['tr', 'en'] as $locale) {
            foreach (['desktop', 'tablet', 'mobile'] as $device) {
                $keys[] = "sliders:html:{$locale}:{$device}:{$type}";
            }
        }

        $keys[] = "sliders:{$type}";

        return $keys;
    }

    public function getActiveByType(string $type)
    {
        return Cache::rememberForever(
            "sliders:{$type}",
            fn () => Slider::where('type', $type)
                ->where('status', true)
                ->orderBy('sort_order')
                ->get()
        );
    }

    public function create(array $data): Slider
    {
        $slider = Slider::create($data);
        $this->clearCache($data['type']);
        return $slider;
    }

    public function update(Slider $slider, array $data): Slider
    {
        $slider->update($data);
        $this->clearCache($slider->type);
        return $slider;
    }

    public function updateSortOrders($sliders, $type)
    {
        foreach ($sliders as $item) {
            Slider::where('id', $item['id'])
                ->where('type', $type)
                ->update(['sort_order' => $item['sort_order']]);

            $this->clearCache($type);
        }
    }

    public function delete(Slider $slider): void
    {
        $this->deleteImages($slider);
        $type = $slider->type;
        $slider->delete();
        $this->clearCache($type);
    }

    protected function clearCache(string $type)
    {
        forget_cache_keys($this->cacheKey($type));
    }

    protected function deleteImages(Slider $slider): void
    {
        $imageFields = [
            'image_desktop_tr',
            'image_desktop_en',
            'image_tablet_tr',
            'image_tablet_en',
            'image_mobile_tr',
            'image_mobile_en',
        ];

        foreach ($imageFields as $field) {
            $fileName = $slider->{$field};

            if (!$fileName) {
                continue;
            }

            foreach (['desktop', 'tablet', 'mobile'] as $device) {

                $pathKey = "{$slider->type}.{$device}";
                $path = data_get(config('images.paths'), $pathKey);

                if (!$path) {
                    continue;
                }

                $fullPath = public_path($path . $fileName);

                if (File::exists($fullPath)) {
                    File::delete($fullPath);
                }
            }
        }
    }
}