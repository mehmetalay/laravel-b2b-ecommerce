<?php

namespace App\Repositories;

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;

class CurrencyRepository
{
    protected $cacheKeyPrefix = 'currency:code:first:';
    protected $cacheKeyAll = 'currency:all';

    public function getFirstByCode($code)
    {
        $cacheKey = $this->cacheKeyPrefix . $code;

        return Cache::rememberForever($cacheKey, function () use ($code) {
            return Currency::where('code', $code)->first();
        });
    }

    public function getAllCurrencies()
    {
        return Cache::rememberForever($this->cacheKeyAll, function () {
            return Currency::all();
        });
    }

    public function update(Currency $currency, array $data): Currency
    {
        $currency->update($data);

        $this->clearCache($currency->code);

        return $currency;
    }

    public function clearCache($code)
    {
        $cacheKey = $this->cacheKeyPrefix . $code;

        forget_cache_keys([
            $cacheKey,
            $this->cacheKeyAll
        ]);
    }
}