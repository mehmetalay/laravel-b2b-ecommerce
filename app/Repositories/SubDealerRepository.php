<?php

namespace App\Repositories;

use App\Models\SubDealer;
use Illuminate\Support\Facades\Cache;

class SubDealerRepository
{
    protected $cacheKeyFirst = 'sub:dealer:first:';
    protected $cacheKeyAll = 'sub:dealer:all';
    protected $cacheKeyActive = 'sub:dealer:active';

    public function create(array $data): SubDealer
    {
        $subDealer = SubDealer::create($data);

        $this->clearCache($subDealer);

        return $subDealer;
    }

    public function update(SubDealer $subDealer, array $data): SubDealer
    {
        $subDealer->update($data);

        $this->clearCache($subDealer);

        return $subDealer;
    }

    public function delete(SubDealer $subDealer)
    {
        $subDealer->delete();

        $this->clearCache($subDealer);

        return true;
    }

    public function getFirst($id)
    {
        $cacheKey = $this->cacheKeyFirst . $id;

        $subDealer = Cache::rememberForever($cacheKey, function () use ($id) {
            return SubDealer::find($id);
        });

        return $subDealer;
    }

    public function getAllSubDealers()
    {
        return Cache::rememberForever($this->cacheKeyAll, function () {
            return SubDealer::all();
        });
    }

    public function getActiveSubDealers()
    {
        return Cache::rememberForever($this->cacheKeyActive, function () {
            return SubDealer::active()->get();
        });
    }

    public function getByDealer($dealerId)
    {
        $ids = $this->getAllSubDealers()->where('dealer_id', $dealerId)->pluck('id')->toArray();
        return SubDealer::whereIn('id', $ids);
    }

    public function clearCache($subDealer)
    {
        forget_cache_keys([
            $this->cacheKeyFirst . $subDealer->id,
            $this->cacheKeyAll,
            $this->cacheKeyActive
        ]);
    }
}