<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $from = '';
        $to = '';
        if (request()->get('zaman')) {
            switch (request()->get('zaman')) {
                case 'bugunluk':
                    $from = date('Y-m-d') . ' 00:00:00';
                    $to = date('Y-m-d') . ' 23:59:59';
                    break;
                case 'haftalik':
                    $from = date('Y-m-d', strtotime('monday this week')) . ' 00:00:00';
                    $to = date('Y-m-d', strtotime('sunday this week')) . ' 23:59:59';
                    break;
                case 'aylik':
                    $from = date('Y-m-d', strtotime('first day of this month')) . ' 00:00:00';
                    $to = date('Y-m-d', strtotime('last day of this month')) . ' 23:59:59';
                    break;
            }
        }

        $orders = Order::with(['plasiyer', 'user', 'orderStatus'])
            ->where('status', 'approved')
            ->orderBy('id', 'DESC')
            ->limit(10)
            ->get();

        $total_sales_amount_tl = Order::where('currency', 'TL')
            ->where('order_status_id', '!=', 1)
            ->where('status', 'approved')
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->sum('total_price');

        $total_sales_amount_usd = Order::where('currency', 'USD')
            ->where('order_status_id', '!=', 1)
            ->where('status', 'approved')
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->sum('total_price');

        $total_number_of_orders = Order::where('status', 'approved')
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->count();

        $numberOfOrdersAwaitingApproval = Order::where('order_status_id', 1)
            ->where('status', 'approved')
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->count();

        $total_number_of_sold_products = Order::where('order_status_id', '!=', 1)
            ->where('status', 'approved')
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->sum('total_quantity');

        $total_number_of_plasiyer_orders = Order::whereNotNull('plasiyer_id')
            ->where('status', 'approved')
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->count();

        $total_number_of_user_orders = Order::whereNull('plasiyer_id')
            ->where('status', 'approved')
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->count();

        return view('backend.pages.dashboard.index', compact('orders', 'total_sales_amount_tl', 'total_sales_amount_usd', 'total_number_of_orders', 'numberOfOrdersAwaitingApproval', 'total_number_of_sold_products', 'total_number_of_plasiyer_orders', 'total_number_of_user_orders'));
    }
}
