<?php

namespace App\Http\Controllers;

use App\Models\{Order, Payment};
use Illuminate\Http\RedirectResponse;
use App\Services\{CurrentAccountService, StatementReportService};

class ReportController extends Controller
{
    private CurrentAccountService $currentAccountService;
    private StatementReportService $statementReportService;

    public function __construct(CurrentAccountService $currentAccountService, StatementReportService $statementReportService)
    {
        $this->middleware('auth:web,subdealer');
        $this->currentAccountService = $currentAccountService;
        $this->statementReportService = $statementReportService;
    }

    public function customerStatement()
    {
        $currentAccount = $this->resolveCurrentAccountForReport();
        if ($currentAccount instanceof RedirectResponse) {
            return $currentAccount;
        }

        $startDate = request()->get('startDate');
        $endDate = request()->get('endDate');

        $statement = $this->statementReportService->buildForCurrentAccount(
            $currentAccount,
            $this->currentAccountService->userQuery(),
            $startDate,
            $endDate
        );

        return view('reports.customer-statement', [
            'items' => $statement['items'],
            'currentAccount' => $currentAccount,
            'balance' => $statement['balance'],
            'debtTotal' => $statement['debtTotal'],
            'receivableTotal' => $statement['receivableTotal'],
        ]);
    }

    public function orderList()
    {
        $currentAccount = $this->resolveCurrentAccountForReport();
        if ($currentAccount instanceof RedirectResponse) {
            return $currentAccount;
        }

        $startDate = request()->get('startDate');
        $endDate = request()->get('endDate');
        $status = request()->get('status');
        $userQuery = $this->currentAccountService->userQuery();

        $orders = Order::query()
            ->whereNull('deleted_at')
            ->where('status', 'approved')
            ->when(!empty($userQuery['user_id']), function ($query) use ($userQuery) {
                $query->where('user_id', $userQuery['user_id']);
            })
            ->when(!empty($userQuery['sub_dealer_id']), function ($query) use ($userQuery) {
                $query->where('sub_dealer_id', $userQuery['sub_dealer_id']);
            })
            ->when(!empty($userQuery['plasiyer_id']), function ($query) use ($userQuery) {
                $query->where('plasiyer_id', $userQuery['plasiyer_id']);
            })
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('order_status_id', $status);
            })
            ->orderByDesc('id')
            ->paginate(50)
            ->appends(request()->query());

        return view('reports.order-list', compact('orders', 'currentAccount', 'startDate', 'endDate', 'status'));
    }

    public function paymentList()
    {
        $currentAccount = $this->resolveCurrentAccountForReport();
        if ($currentAccount instanceof RedirectResponse) {
            return $currentAccount;
        }

        $startDate = request()->get('startDate');
        $endDate = request()->get('endDate');
        $status = request()->get('status');
        $userQuery = $this->currentAccountService->userQuery();

        $payments = Payment::query()
            ->whereIn('status', ['SUCCESS', 'FAILED'])
            ->when(!empty($userQuery['user_id']), function ($query) use ($userQuery) {
                $query->where('user_id', $userQuery['user_id']);
            })
            ->when(!empty($userQuery['sub_dealer_id']), function ($query) use ($userQuery) {
                $query->where('sub_dealer_id', $userQuery['sub_dealer_id']);
            })
            ->when(!empty($userQuery['plasiyer_id']), function ($query) use ($userQuery) {
                $query->where('plasiyer_id', $userQuery['plasiyer_id']);
            })
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderByDesc('id')
            ->paginate(50)
            ->appends(request()->query());

        return view('reports.payment-list', compact('payments', 'currentAccount', 'startDate', 'endDate', 'status'));
    }

    private function resolveCurrentAccountForReport()
    {
        $currentAccount = $this->currentAccountService->currentAccount();

        if (auth('web')->check() && auth('web')->user()->role === 'salesman') {
            abort_if(auth('web')->user()->report_access == 0, 404);

            if ($currentAccount == null) {
                session()->flash('warning', trans('translations.report_controller.lutfen_bayi_seciniz'));
                return redirect()->back();
            }
        } else {
            abort_if(!$currentAccount || $currentAccount->report_access == 0, 404);
        }

        return $currentAccount;
    }
}
