<?php

namespace App\Http\Controllers\Admin;

use App\Models\Payment;
use App\Models\Product;
use App\Models\Category;
use App\Services\PaymentService;
use App\Application\Category\Services\CategoryService;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;

class PDFController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web,subdealer,admin');
    }

    public function payments()
    {
        $dateFrom = request('date_from');
        $dateTo = request('date_to');

        $payments = Payment::whereBetween('completed_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->where('status', 'SUCCESS')
            ->get();

        return app(PaymentService::class)->generateBulkPaymentReceiptPdf($payments);
    }
}


