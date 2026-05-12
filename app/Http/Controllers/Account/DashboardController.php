<?php

namespace App\Http\Controllers\Account;

use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\CurrentAccountService;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web,subdealer');
    }

    public function index()
    {
        $account = app(CurrentAccountService::class)->currentAccount();

        if (auth('web')->check() && auth('web')->user()->role === 'salesman' && $account == null) {
            session()->flash('warning', trans('translations.payment_controller.lutfen_bayi_seciniz'));
            return redirect()->back();
        }

        $balance = $account->balance ?? 0;
        $currency = $account->currency ?? '';

        $orderTotal = Order::query()
            ->where('user_id', $account->current_account_id)
            ->sum('total_price');

        $paymentTotal = Payment::query()
            ->where('status', 'SUCCESS')
            ->where('user_id', $account->current_account_id)
            ->sum('amount_paid');

        return view('frontend.pages.account.dashboard', compact('balance', 'orderTotal', 'paymentTotal', 'currency'));
    }
}