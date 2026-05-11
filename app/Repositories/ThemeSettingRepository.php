<?php

namespace App\Repositories;

use App\Models\ThemeSetting;
use Illuminate\Support\Facades\Cache;

class ThemeSettingRepository
{
    protected $cacheKey = 'theme_settings:first';

    public function getFirst()
    {
        return Cache::rememberForever($this->cacheKey, function () {
            return ThemeSetting::find(1);
        });
    }

    public function update(ThemeSetting $themeSetting, array $data): ThemeSetting
    {
        $themeSetting->update($data);

        $this->clearCache();

        return $themeSetting;
    }

    public function clearCache()
    {
        forget_cache_keys($this->cacheKey);
    }
}