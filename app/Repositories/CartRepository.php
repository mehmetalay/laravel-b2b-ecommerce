<?php

namespace App\Repositories;

use App\Models\Cart;
use Illuminate\Support\Facades\Cache;

class CartRepository
{
    protected $model;

    public function __construct(Cart $model)
    {
        $this->model = $model;
    }

    protected $cacheKeyFirst = 'cart:first:';

    public function create(array $data): Cart
    {
        $cart = Cart::create($data);

        $this->clearCache();

        return $cart;
    }

    public function update(Cart $cart, array $data): Cart
    {
        $cart->update($data);

        $this->clearCache();

        return $cart;
    }

    public function delete(Cart $cart)
    {
        $cart->delete();

        $this->clearCache();

        return true;
    }

    public function getFirst($id)
    {
        $cacheKey = $this->cacheKeyFirst . $id;

        // $cart = Cache::rememberForever($cacheKey, function () use ($id) {
            return Cart::find($id);
        // });

        return $cart;
    }

    public function clearCache()
    {
        forget_cache_keys([]);
    }

    //
    public function findCart(array $filters)
    {
        // return Cache::rememberForever($this->cacheKey('findCart', $filters), function () use ($filters) {
            return $this->model
                ->where('product_id', $filters['product_id'])
                ->where('plasiyer_id', $filters['plasiyer_id'] ?? null)
                ->where('user_id', $filters['user_id'])
                ->where('sub_dealer_id', $filters['sub_dealer_id'] ?? null)
                ->where('payment_type', session()->get('cart_payment_type'))
                ->active()
                ->first();
        // });
    }

    public function getUserCart(array $filters)
    {
        // return Cache::rememberForever($this->cacheKey('getUserCart', $filters), function () use ($filters) {
            return $this->model
                ->where('plasiyer_id', $filters['plasiyer_id'] ?? null)
                ->where('user_id', $filters['user_id'])
                ->where('sub_dealer_id', $filters['sub_dealer_id'] ?? null)
                ->where('payment_type', session()->get('cart_payment_type'))
                ->active()
                ->get();
        // });
    }

    public function clearUserCart(array $filters)
    {
        // Cache::forget($this->cacheKeyPrefix());

        return $this->model
            ->where('plasiyer_id', $filters['plasiyer_id'] ?? null)
            ->where('user_id', $filters['user_id'])
            ->where('sub_dealer_id', $filters['sub_dealer_id'] ?? null)
            ->where('payment_type', session()->get('cart_payment_type'))
            ->active()
            ->delete();
    }

    public function restoreBackup($backedUpCartId)
    {
        // Cache::forget($this->cacheKeyPrefix());

        return $this->model
            ->where('backed_up_cart_id', $backedUpCartId)
            ->update([
                'backed_up' => 0,
                'backed_up_cart_id' => null,
            ]);
    }

    public function markAsOrdered(array $filters, $currency)
    {
        // Cache::forget($this->cacheKeyPrefix());

        return $this->model
            ->where('plasiyer_id', $filters['plasiyer_id'] ?? null)
            ->where('user_id', $filters['user_id'])
            ->where('sub_dealer_id', $filters['sub_dealer_id'] ?? null)
            ->where('payment_type', session()->get('cart_payment_type'))
            ->where('currency', $currency)
            ->active()
            ->update(['ordered' => 1]);
    }

    private function cacheKey(string $method, array $filters = []): string
    {
        return $this->cacheKeyPrefix() . $method . ':' . md5(json_encode($filters));
    }

    private function cacheKeyPrefix(): string
    {
        return "cart:";
    }
}