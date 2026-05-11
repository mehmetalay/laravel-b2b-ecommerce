<?php

namespace App\Services;

use App\Models\ThemeSetting;
use App\Repositories\ThemeSettingRepository;
use Illuminate\Http\Request;

class ThemeSettingService
{
    protected $repository;

    public function __construct(ThemeSettingRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getFirst()
    {
        return $this->repository->getFirst();
    }

    public function update(Request $request, ThemeSetting $themeSetting): ThemeSetting
    {
        $imageService = app(ImageService::class);

        $data = $request->all();

        if ($request->hasFile('logo_picture')) {
            $imageService->logo($request->file('logo_picture'), 'logo', true);
        }

        if ($request->hasFile('favicon_picture')) {
            $imageService->favicon($request->file('favicon_picture'));
        }

        if ($request->hasFile('footer_logo_picture')) {
            $imageService->logo($request->file('footer_logo_picture'), 'footer_logo');
        }

        if ($request->hasFile('footer_ssl_logo_picture')) {
            $imageService->logo($request->file('footer_ssl_logo_picture'), 'footer_ssl_image');
        }

        if ($request->hasFile('nopic_image')) {
            $imageService->nopic_image($request->file('nopic_image'));
        }

        return $this->repository->update($themeSetting, $data);
    }
}
