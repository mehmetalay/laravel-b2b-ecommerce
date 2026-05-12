<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Services\AddressService;

class AddressController extends Controller
{
    public function __construct(
        protected AddressService $service
    ) {
        $this->middleware('auth:web,subdealer');
    }

    public function index()
    {
        $addresses = $this->service->listForUser();
        return view('frontend.pages.account.addresses.index', compact('addresses'));
    }

    public function list()
    {
        $addresses = $this->service->listForUser();

        return response()->json([
            'status' => 'success',
            'addresses' => $addresses->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'company_name' => $a->company_name,
                'address' => $a->address,
                'city' => $a->city?->name,
                'district' => $a->district?->name,
                'neighborhood' => $a->neighborhood?->name,
                'is_default' => $a->is_default,
            ])
        ]);
    }

    public function show(int $id)
    {
        $a = $this->service->getForEdit($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $a->id,
                'title' => $a->title,
                'company_name' => $a->company_name,
                'tax_office' => $a->tax_office,
                'tax_number' => $a->tax_number,
                'phone' => $a->phone,
                'city_id' => $a->city_id,
                'city' => $a->city?->name,
                'district_id' => $a->district_id,
                'district' => $a->district?->name,
                'neighborhood_id' => $a->neighborhood_id,
                'neighborhood' => $a->neighborhood?->name,
                'address' => $a->address,
                'is_default' => $a->is_default,
            ]
        ]);
    }

    public function store(AddressRequest $request)
    {
        $address = $this->service->store($request->validated());

        return response()->json([
            'status' => 'success',
            'address' => [
                'id' => $address->id,
                'title' => $address->title,
                'city' => $address->city?->name,
                'district' => $address->district?->name,
            ]
        ]);
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);
        return response()->json(['status' => 'success']);
    }
}
