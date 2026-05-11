<?php

namespace App\Services;

use App\Repositories\AdditionalSettingRepository;
use App\Models\AdditionalSetting;
use Illuminate\Http\Request;

class AdditionalSettingService
{
    protected $repository;

    public function __construct(AdditionalSettingRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getFirst()
    {
        return $this->repository->getFirst();
    }

    public function update(Request $request, AdditionalSetting $additionalSetting): AdditionalSetting
    {
        $data = $request->all();

        $data['decimal'] = request('decimal') != '' ? request('decimal') : 2;
        $data['purchase_limit_minimum'] = request('purchase_limit_minimum') ? request('purchase_limit_minimum') : 1;
        $data['display_of_out_of_stock_products'] = request()->has('display_of_out_of_stock_products');
        $data['show_stock'] = request()->has('show_stock');
        $data['min_stock_quantity'] = request('min_stock_quantity') ? request('min_stock_quantity') : 1;
        $data['admin_password'] = request('admin_password') ?: null;
        $data['site_status'] = request()->has('site_status');
        $data['order_emails'] = implode_json_column(request('order_emails'), 'value');
        $data['payment_emails'] = implode_json_column(request('payment_emails'), 'value');
        $data['dealer_application_mails'] = implode_json_column(request('dealer_application_mails'), 'value');
        $data['use_contract_approval'] = request()->has('use_contract_approval');
        $data['cart_item_note_visibility'] = request()->has('cart_item_note_visibility');
        $data['payment_plan_selection'] = request()->has('payment_plan_selection');
        $data['payment_plan_required'] = request()->has('payment_plan_required');
        $data['payment_type_selection'] = request()->has('payment_type_selection');
        $data['payment_type_required'] = request()->has('payment_type_required');
        $data['delivery_type_selection'] = request()->has('delivery_type_selection');
        $data['delivery_type_required'] = request()->has('delivery_type_required');
        $data['allow_over_order'] = request()->has('allow_over_order');
        $data['is_critical_stock_enabled'] = request()->has('is_critical_stock_enabled');
        $data['is_order_confirmation'] = request()->has('is_order_confirmation');

        return $this->repository->update($additionalSetting, $data);
    }
}
