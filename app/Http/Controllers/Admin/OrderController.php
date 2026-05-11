<?php

namespace App\Http\Controllers\Admin;

use App\Application\Order\Actions\Admin\CreateOrderExportAction;
use App\Application\Order\Actions\Admin\ExportOrdersAction;
use App\Application\Order\Queries\AdminOrderTableDataQuery;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.orders.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        return view('admin.orders.edit', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        $order->update([
            'order_status_id' => request('order_status_id'),
            'order_note' => request('order_note'),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Başarıyla güncellendi.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $order->delete();

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function filter()
    {
        $s = '';

        if ($name = request('name')) {
            $name = '&name=' . request('name');
            $s = '?';
        }

        if ($firstDate = request('first_date')) {
            $firstDate = '&firstDate=' . request('first_date');
            $s = '?';
        }

        if ($lastDate = request('last_date')) {
            $lastDate = '&lastDate=' . request('last_date');
            $s = '?';
        }

        if ($status = request('status')) {
            $status = '&status=' . request('status');
            $s = '?';
        }

        if ($hasCampaign = request('has_campaign')) {
            $hasCampaign = '&hasCampaign=' . request('has_campaign');
            $s = '?';
        }

        return redirect()->to(route('admin.orders.index') . $s . $name . $firstDate . $lastDate . $status . $hasCampaign);
    }

    public function tableData(Request $request, AdminOrderTableDataQuery $adminOrderTableDataQuery)
    {
        return response()->json($adminOrderTableDataQuery->handle($request));
    }

    public function export(Request $request, ExportOrdersAction $exportOrdersAction)
    {
        return $exportOrdersAction->handle($request);
    }

    public function createExport(Request $request, CreateOrderExportAction $createOrderExportAction)
    {
        return $createOrderExportAction->handle($request);
    }
}
