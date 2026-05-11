<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\{Payment, PaymentLink, BankIntegration};
use Illuminate\Http\Request;
use App\Filters\PaymentFilters;
use App\Http\Requests\Payments\{ListInstallmentRequest, PaymentLinkListInstallmentRequest};
use App\Services\CurrentAccountService;

class PaymentController extends Controller
{
    protected $currentAccountService;

    public function __construct(CurrentAccountService $currentAccountService)
    {
        parent::__construct();
        $this->middleware('auth:web,subdealer', ['except' => ['paymentLink', 'paymentLinklistInstallment']]);
        $this->currentAccountService = $currentAccountService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, PaymentFilters $filters)
    {
        $startDate = request()->get('date_from', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = request()->get('date_to', Carbon::now()->format('Y-m-d'));

        $userQuery = $this->currentAccountService->userQuery();

        $payments = Payment::when(auth('web')->check() && auth('web')->user()->role === 'salesman', function ($query) use ($userQuery) {
                $query->where(function ($query) use ($userQuery) {
                    $query->where('plasiyer_id', $userQuery['plasiyer_id'])
                        ->orWhere(function ($query) use ($userQuery) {
                            $query->whereRelation('user', 'plasiyer1', $userQuery['plasiyer_id'])
                                ->orWhereRelation('user', 'plasiyer2', $userQuery['plasiyer_id'])
                                ->orWhereRelation('user', 'plasiyer3', $userQuery['plasiyer_id'])
                                ->orWhereRelation('user', 'plasiyer4', $userQuery['plasiyer_id'])
                                ->orWhereRelation('user', 'plasiyer5', $userQuery['plasiyer_id']);
                        });
                });
            })
            ->when(auth('web')->check() && auth('web')->user()->role === 'dealer', function ($query) use ($userQuery) {
                $query->where('user_id', $userQuery['user_id']);
            })
            ->when(auth('subdealer')->check(), function ($query) use ($userQuery) {
                $query->where('sub_dealer_id', $userQuery['sub_dealer_id']);
            })
            ->filter($filters)
            ->whereIn('status', ['SUCCESS', 'FAILED'])
            ->orderBy('id', 'DESC')
            ->paginate(50)
            ->appends($request->query());

        return view('payments.index', compact('payments', 'startDate', 'endDate'));
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
        //
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

    public function page()
    {
        $currentAccount = $this->currentAccountService->currentAccount();

        if (auth('web')->check() && auth('web')->user()->role === 'salesman' && $currentAccount == null) {
            session()->flash('warning', trans('translations.payment_controller.lutfen_bayi_seciniz'));
            return redirect()->back();
        }

        if (!$currentAccount) {
            return redirect()->back()->with('warning', 'Cari hesap bilgisi bulunamadi.');
        }

        if (!$currentAccount->can_collect_payments) {
            return redirect()->back()->with('warning', 'Bu cari hesap tahsilat yapılmasına izin verilmemektedir.');
        }

        if (auth('subdealer')->check() && !auth('subdealer')->user()->can_record_payment) {
            return redirect()->back()->with('warning', 'Tahsilat kaydı yapma yetkiniz bulunmamaktadır.');
        }

        logSession('Ödeme sayfasına erişildi.', null, 'info', 'payment_logs');

        return view('payments.page');
    }

    public function listInstallment(ListInstallmentRequest $request)
    {
        $amount = (float) $request->input('amount_numeric');
        if ($amount <= 0) {
            return response()->json([
                'result' => 'error',
                'message' => 'Gecersiz odeme tutari.',
            ], 422);
        }

        $bankIntegration = BankIntegration::with('installments')->find((int) $request->input('bank_integration_id'));
        if (!$bankIntegration) {
            return response()->json([
                'result' => 'error',
                'message' => 'Banka bilgisi bulunamadi.',
            ], 404);
        }

        $currentAccount = $this->currentAccountService->currentAccount();
        $companyId = $currentAccount?->company_id ?: additional_setting('default_company_id');
        if ((int) $bankIntegration->company_id !== (int) $companyId) {
            return response()->json([
                'result' => 'error',
                'message' => 'Secilen banka bu hesap icin kullanilamaz.',
            ], 403);
        }

        return response()->json([
            'result' => 'success',
            'view' => view('payments.installment-list', compact('amount', 'bankIntegration'))->render()
        ]);
    }

    // PaymentLink
    public function paymentLink($token = null)
    {
        logSession('Ödeme link sayfasına erişildi.', null, 'info', 'payment_logs');

        $paymentLink = PaymentLink::query()
            ->where('token', $token)
            ->active()
            ->where('is_paid', 0)
            ->first() ?? abort(404);

        $automaticSinglePaymentId = BankIntegration::where('automatic_single_payment', 1)->value('id');

        return view('payments.payment-link.page', compact('paymentLink', 'automaticSinglePaymentId'));
    }

    public function paymentLinklistInstallment(PaymentLinkListInstallmentRequest $request)
    {
        $amount = (float) $request->input('amount_numeric');
        if ($amount <= 0) {
            return response()->json([
                'result' => 'error',
                'message' => 'Gecersiz odeme tutari.',
            ], 422);
        }

        $paymentLink = PaymentLink::query()
            ->where('token', (string) $request->input('token'))
            ->active()
            ->where('is_paid', 0)
            ->first();
        if (!$paymentLink) {
            return response()->json([
                'result' => 'error',
                'message' => 'Odeme link kaydi bulunamadi.',
            ], 404);
        }

        $bankIntegration = BankIntegration::with('installments')->find((int) $request->input('bank_integration_id'));
        if (!$bankIntegration) {
            return response()->json([
                'result' => 'error',
                'message' => 'Banka bilgisi bulunamadi.',
            ], 404);
        }

        if ((int) $paymentLink->transaction_type === 2) {
            $automaticSinglePaymentId = (int) BankIntegration::where('automatic_single_payment', 1)->value('id');
            if ($automaticSinglePaymentId <= 0 || (int) $bankIntegration->id !== $automaticSinglePaymentId) {
                return response()->json([
                    'result' => 'error',
                    'message' => 'Bu odeme linki icin secilen banka kullanilamaz.',
                ], 403);
            }
        }

        if ((int) $paymentLink->transaction_type === 3 && (int) $paymentLink->manual_lock_bank_installment === 1) {
            if ((int) $paymentLink->manual_bank_integration_id !== (int) $bankIntegration->id) {
                return response()->json([
                    'result' => 'error',
                    'message' => 'Bu odeme linkinde banka secimi degistirilemez.',
                ], 403);
            }
        }

        return response()->json([
            'result' => 'success',
            'view' => view('payments.payment-link.installment-list', compact('amount', 'bankIntegration', 'paymentLink'))->render()
        ]);
    }

    public function filter()
    {

    }

}
