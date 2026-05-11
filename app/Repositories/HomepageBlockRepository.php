<?php

namespace App\Repositories;

use App\Models\HomepageBlock;
use Illuminate\Support\Facades\Cache;

class HomepageBlockRepository
{
    protected $cacheKeyAll = 'homepageblock:all';
    protected $cacheKeyActive = 'homepageblock:active';
    protected $cacheKeyHtml = 'homepageblock:html';

    public function create(array $data): HomepageBlock
    {
        $homepageBlock = HomepageBlock::create($data);

        $this->clearCache();

        return $homepageBlock;
    }

    public function update(HomepageBlock $homepageBlock, array $data): HomepageBlock
    {
        $homepageBlock->update($data);

        $this->clearCache();

        return $homepageBlock;
    }

    public function delete(HomepageBlock $homepageBlock)
    {
        $homepageBlock->delete();

        $this->clearCache();

        return true;
    }

    public function getAllHomepageBlocks()
    {
        return Cache::rememberForever($this->cacheKeyAll, function () {
            return HomepageBlock::all();
        });
    }

    public function getActiveHomepageBlocks()
    {
        return Cache::rememberForever($this->cacheKeyActive, function () {
            return HomepageBlock::active()
                ->orderBy('sort_order')
                ->get([
                    'id',
                    'slug',
                    'title_tr',
                    'subtitle_tr',
                    'title_en',
                    'subtitle_en'
                ]);
        });
    }

    public function clearCache()
    {
        forget_cache_keys([
            $this->cacheKeyAll,
            $this->cacheKeyActive,
            $this->cacheKeyHtml . ':tr',
            $this->cacheKeyHtml . ':en',
        ]);
    }
}