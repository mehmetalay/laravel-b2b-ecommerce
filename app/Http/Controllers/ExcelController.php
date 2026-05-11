<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Exports\Excel\CartExport;
use App\Exports\Excel\OrderExport;
use App\Exports\Excel\PaymentExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Excel\NoImageProductsExport;
use App\Exports\Excel\InactiveProductsExport;
use App\Exports\Excel\ProductsWithoutQuantityExport;

class ExcelController extends Controller
{
    public function cartExport()
    {
        return Excel::download(new CartExport, 'sepet-' . uniqid() . '.xlsx');
    }

    public function orderExport(Order $order)
    {
        return Excel::download(new OrderExport($order), 'siparis-' . $order->id . '.xlsx');
    }

    public function paymentExport(Request $request)
    {
        return Excel::download(new PaymentExport(
            $request->name,
            $request->status,
            $request->bankIntegrationId,
            $request->salesmanId,
            $request->date_from,
            $request->date_to,
            $request->source
        ), 'odeme-raporu-' . uniqid() . '.xlsx');
    }

    public function noImageProductsExport()
    {
        return Excel::download(new NoImageProductsExport, 'resimsiz-urunler-' . uniqid() . '.xlsx');
    }

    public function productsWithoutQuantityExport()
    {
        return Excel::download(new ProductsWithoutQuantityExport, 'stokda-olmayan-urunler-' . uniqid() . '.xlsx');
    }

    public function inactiveProducts()
    {
        return Excel::download(new InactiveProductsExport, 'pasif-urunler-' . uniqid() . '.xlsx');
    }
}
