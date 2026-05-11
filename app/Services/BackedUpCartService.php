<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Repositories\BackedUpCartRepository;

class BackedUpCartService
{
    protected $repository;
    protected $currentAccountService;

    public function __construct(BackedUpCartRepository $repository, CurrentAccountService $currentAccountService)
    {
        $this->repository = $repository;
        $this->currentAccountService = $currentAccountService;
    }

    public function create(Request $request)
    {
        $userQuery = $this->currentAccountService->userQuery();

        $data = $request->all();

        $data['name'] = $data['cart_name'];
        $data['plasiyer_id'] = $userQuery['plasiyer_id'] ?? null;
        $data['user_id'] = $userQuery['user_id'];
        $data['sub_dealer_id'] = $userQuery['sub_dealer_id'] ?? null;
        $data['cart_discount_rate_tl_1'] = session()->get('cart_discount_rate_tl_1', 0);
        $data['cart_discount_rate_tl_2'] = session()->get('cart_discount_rate_tl_2', 0);
        $data['cart_discount_rate_usd_1'] = session()->get('cart_discount_rate_usd_1', 0);
        $data['cart_discount_rate_usd_2'] = session()->get('cart_discount_rate_usd_2', 0);
        $data['cart_discount_rate_eur_1'] = session()->get('cart_discount_rate_eur_1', 0);
        $data['cart_discount_rate_eur_2'] = session()->get('cart_discount_rate_eur_2', 0);
        $data['cart_discount_rate_gbp_1'] = session()->get('cart_discount_rate_gbp_1', 0);
        $data['cart_discount_rate_gbp_2'] = session()->get('cart_discount_rate_gbp_2', 0);

        return $this->repository->create($data);
    }

    public function update(Request $request, $backedUpCart)
    {
        return $this->repository->update($backedUpCart, $request->all());
    }

    public function delete($backedUpCart)
    {
        return $this->repository->delete($backedUpCart);
    }

    public function getAllBackedUpCarts()
    {
        $userQuery = $this->currentAccountService->userQuery();

        return $this->repository->getAllBackedUpCarts($userQuery);
    }

    public function getFirst($id)
    {
        return $this->repository->getFirst($id);
    }
}