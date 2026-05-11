<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Models\Installment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BankIntegration;

class PosManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.settings.pos-managements.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $bankNames = $request->input('bank_name', []);
        $erpBankCodes = $request->input('erp_bank_code', []);
        $bankStatuses = $request->input('bank_status', []);

        foreach ($bankNames as $bankIntegrationId => $name) {
            BankIntegration::where('id', $bankIntegrationId)->update([
                'name' => $name,
                'erp_bank_code' => $erpBankCodes[$bankIntegrationId] ?? null,
                'status' => isset($bankStatuses[$bankIntegrationId]) ? 1 : 0,
            ]);
        }

        $commissionRates = $request->input('commission_rate', []);
        $statuses = $request->input('status', []);

        foreach ($commissionRates as $installmentId => $rate) {
            Installment::where('id', $installmentId)->update([
                'commission_rate' => $rate,
                'status' => isset($statuses[$installmentId]) ? 1 : 0,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Başarıyla güncellendi!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
