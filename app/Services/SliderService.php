<?php

namespace App\Services;

use App\Models\Slider;
use Illuminate\Http\Request;
use App\Repositories\SliderRepository;

class SliderService
{
    public function __construct(
        protected SliderRepository $repository
    ) {}

    public function store(Request $request): Slider
    {
        return $this->save(new Slider(), $request);
    }

    public function update(Slider $slider, Request $request): Slider
    {
        return $this->save($slider, $request);
    }

    protected function save(Slider $slider, Request $request): Slider
    {
        $imageService = app(ImageService::class);

        $data = $request->all();

        $data['status'] = $request->boolean('status');
        $data['target_blank'] = $request->boolean('target_blank');

        foreach (['tr', 'en'] as $locale) {
            foreach (['desktop', 'mobile', 'tablet'] as $device) {

                $field = "image_{$device}_{$locale}";

                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $ext = $file->getClientOriginalExtension() === 'gif' ? '.gif' : '.jpg';
                    $fileName = uniqid("slider_{$device}_{$locale}_") . $ext;

                    $imageService->sliderV2(
                        $file,
                        $fileName,
                        $device,
                        $data['type']
                    );

                    $data[$field] = $fileName;
                }
            }
        }

        return $slider->exists
            ? $this->repository->update($slider, $data)
            : $this->repository->create($data);
    }

    public function updateSortOrders($sliders, $type)
    {
        $this->repository->updateSortOrders($sliders, $type);
    }

    public function delete(Slider $slider): void
    {
        $this->repository->delete($slider);
    }

    public function getActiveByType($type)
    {
        return $this->repository->getActiveByType($type);
    }
}
