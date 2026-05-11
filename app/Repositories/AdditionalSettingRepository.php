<?php

namespace App\Repositories;

use App\Models\AdditionalSetting;
use Illuminate\Support\Facades\Cache;

class AdditionalSettingRepository
{
    protected $cacheKey = 'additional_settings:first';

    public function getFirst()
    {
        return Cache::rememberForever($this->cacheKey, function () {
            return AdditionalSetting::find(1);
        });
    }

    public function update(AdditionalSetting $additionalSetting, array $data): AdditionalSetting
    {
        $additionalSetting->update($data);

        $this->clearCache();

        return $additionalSetting;
    }

    public function clearCache()
    {
        forget_cache_keys([
            $this->cacheKey,
            'productXml'
        ]);
    }
}