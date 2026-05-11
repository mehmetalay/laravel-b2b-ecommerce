<?php

namespace App\Repositories;

use App\Models\BackedUpCart;
use Illuminate\Support\Facades\Cache;

class BackedUpCartRepository
{
    protected $cacheKeyAll = 'backed_up_cart:all:';
    protected $cacheKeyFirst = 'backed_up_cart:first:';

    public function create(array $data): BackedUpCart
    {
        $backedUpCart = BackedUpCart::create($data);

        $this->clearCache($backedUpCart->id);

        return $backedUpCart;
    }

    public function update(BackedUpCart $backedUpCart, array $data): BackedUpCart
    {
        $backedUpCart->update($data);

        $this->clearCache($backedUpCart->id);

        return $backedUpCart;
    }

    public function delete(BackedUpCart $backedUpCart)
    {
        $backedUpCart->delete();

        $this->clearCache($backedUpCart->id);

        return true;
    }

    public function getAllBackedUpCarts($userQuery)
    {
        $cacheKey = $this->cacheKeyAll . md5(json_encode($userQuery));

        // Index listesine bu key'i ekle
        $this->addCacheKeyToIndex($cacheKey);

        return Cache::rememberForever($cacheKey, function () use ($userQuery) {
            return BackedUpCart::query()
                ->orderBy('name', 'asc')
                ->when($salesmanId = ($userQuery['plasiyer_id'] ?? null), function ($query) use ($salesmanId, $userQuery) {
                    $query->where('plasiyer_id', $salesmanId)
                          ->when($dealerId = ($userQuery['user_id'] ?? null), function ($query) use ($dealerId) {
                              $query->where('user_id', $dealerId);
                          });
                })
                ->when($dealerId = ($userQuery['user_id'] ?? null), function ($query) use ($dealerId, $userQuery) {
                    $query->where('user_id', $dealerId)
                          ->when($subDealerId = ($userQuery['sub_dealer_id'] ?? null), function ($query) use ($subDealerId) {
                              $query->where('sub_dealer_id', $subDealerId);
                          });
                })
                ->get();
        });
    }

    public function getFirst($id)
    {
        $cacheKey = $this->cacheKeyFirst . $id;

        $backedUpCart = Cache::rememberForever($cacheKey, function () use ($id) {
            return BackedUpCart::find($id);
        });

        return $backedUpCart;
    }

    public function clearCache($id)
    {
        $this->clearAllBackedUpCartsCache();

        forget_cache_keys($this->cacheKeyFirst . $id);
    }

    protected function addCacheKeyToIndex(string $key): void
    {
        $indexKey = $this->cacheKeyAll . 'index';
        $keys = Cache::get($indexKey, []);
        if (!in_array($key, $keys)) {
            $keys[] = $key;
            Cache::forever($indexKey, $keys);
        }
    }

    public function clearAllBackedUpCartsCache(): void
    {
        $indexKey = $this->cacheKeyAll . 'index';
        $keys = Cache::get($indexKey, []);

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Cache::forget($indexKey);
    }
}