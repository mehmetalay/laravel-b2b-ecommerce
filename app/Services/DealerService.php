<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use App\Repositories\DealerRepository;

class DealerService
{
    protected $repository;

    public function __construct(DealerRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(Request $request)
    {
        return $this->repository->create($request->all());
    }

    public function update(Request $request, $dealer)
    {
        $data = $request->all();

        $data['increase_and_decrease_rate'] = request('increase_and_decrease_rate') ?? 0;
        $data['hide_category_ids'] = implode_json_column(request('hide_category_ids'), 'id');
        $data['hidden_product_prefixes'] = implode_json_column(request('hidden_product_prefixes'), 'value');
        $data['status'] = request()->has('status');
        $data['block_entry'] = request()->has('block_entry');
        $data['hide_all_prices'] = request()->has('hide_all_prices');
        $data['hide_all_stock_quantities'] = request()->has('hide_all_stock_quantities');
        $data['group_by_product_code'] = request()->has('group_by_product_code');
        $data['report_access'] = request()->has('report_access');
        $data['price_type'] = request('price_type') ?? 1;
        $data['is_order_closed'] = request()->has('is_order_closed');
        $data['password'] = bcrypt(request('password'));
        $data['password_must_change'] = request()->has('password_must_change');
        $data['receipt_enabled'] = request()->has('receipt_enabled');
        $data['show_all_installments'] = request()->has('show_all_installments');
        $data['is_installment_allowed'] = request()->has('is_installment_allowed');
        $data['can_collect_payments'] = request()->has('can_collect_payments');
        $data['allowed_payment_methods'] = request()->input('allowed_payment_methods') ? implode(',', request()->input('allowed_payment_methods')) : null;

        return $this->repository->update($dealer, $data);
    }

    public function delete(User $dealer)
    {
        return $this->repository->delete($dealer);
    }

    public function getFirst($id)
    {
        return $this->repository->getFirst($id);
    }

    public function getAllDealersQuery()
    {
        return $this->repository->getAllDealersQuery();
    }

    public function getAllDealers()
    {
        return $this->repository->getAllDealers();
    }

    public function getActiveDealers()
    {
        return $this->repository->getActiveDealers();
    }
}
