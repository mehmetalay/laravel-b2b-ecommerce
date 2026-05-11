<?php

namespace App\Repositories;

use App\Models\GeneralInfo;
use Illuminate\Support\Facades\Cache;

class GeneralInfoRepository
{
    protected $cacheKey = 'general_infos:first';

    public function getFirst()
    {
        return Cache::rememberForever($this->cacheKey, function () {
            return GeneralInfo::find(1);
        });
    }

    public function update(GeneralInfo $generalInfo, array $data): GeneralInfo
    {
        $generalInfo->update($data);

        $this->clearCache();

        return $generalInfo;
    }

    public function clearCache()
    {
        forget_cache_keys($this->cacheKey);
    }
}