<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DealerRepository
{
    protected $cacheKeyFirst = 'dealer:first:';
    protected $cacheKeyAllQuery = 'dealer:all:query';
    protected $cacheKeyAll = 'dealer:all';
    protected $cacheKeyActive = 'dealer:active';

    public function create(array $data): User
    {
        $dealer = User::create($data);

        $this->clearCache($dealer->id);

        return $dealer;
    }

    public function update(User $dealer, array $data): User
    {
        $dealer->update($data);

        $this->clearCache($dealer->id);

        return $dealer;
    }

    public function delete(User $dealer)
    {
        $dealer->delete();

        $this->clearCache($dealer->id);

        return true;
    }

    public function getFirst($id)
    {
        $cacheKey = $this->cacheKeyFirst . $id;

        $dealer = Cache::rememberForever($cacheKey, function () use ($id) {
            return User::find($id);
        });

        return $dealer;
    }

    public function getAllDealersQuery()
    {
        $ids = $this->getActiveDealers()->pluck('id')->toArray();
        return User::whereIn('id', $ids);
    }

    public function getAllDealers()
    {
        return Cache::rememberForever($this->cacheKeyAll, function () {
            return User::customer()
                ->orderBy('name', 'asc')
                ->get();
        });
    }

    public function getActiveDealers()
    {
        return Cache::rememberForever($this->cacheKeyActive, function () {
            return User::customer()
                ->active()
                ->orderBy('name', 'asc')
                ->get();
        });
    }

    public function clearCache($id)
    {
        $cacheKey = $this->cacheKeyFirst . $id;

        forget_cache_keys([
            $cacheKey,
            $this->cacheKeyAllQuery,
            $this->cacheKeyAll,
            $this->cacheKeyActive
        ]);
    }
}