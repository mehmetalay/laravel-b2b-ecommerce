<?php

namespace App\Http\Controllers\Collection;

use App\Models\Collection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\CurrentAccountService;
use Illuminate\Support\Facades\Validator;

class ChequeCollectionController extends Controller
{
    public $currentAccountService;

    public function __construct(CurrentAccountService $currentAccountService)
    {
        $this->middleware('auth:web,subdealer');
        $this->currentAccountService = $currentAccountService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currentAccount = $this->currentAccountService->currentAccount();

        if (auth('web')->check() && auth('web')->user()->role === 'salesman' && $currentAccount == null) {
            return redirect()->back()->with('warning', trans('translations.cart_controller.lutfen_bayi_seciniz'));
        }

        if (!$currentAccount->can_collect_payments) {
            return redirect()->back()->with('warning', 'Bu cari hesap tahsilat yapılmasına izin verilmemektedir.');
        }

        if (auth('subdealer')->check() && !auth('subdealer')->user()->can_record_payment) {
            return redirect()->back()->with('warning', 'Tahsilat kaydı yapma yetkiniz bulunmamaktadır.');
        }

        $cheques = Collection::where('type', 'Çek')
            ->where('plasiyer_id', auth('web')->user()->current_account_id)
            ->orderBy('id', 'DESC')
            ->paginate(50);

        return view('collections.cheques.index', compact('cheques'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('collections.cheques.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $userQuery = $this->currentAccountService->userQuery();

        $validator = $this->validator(request()->all());

        if ($validator->fails()) {
            return response()->json(['warning' => $validator->errors()->first()]);
        }

        $collection_date = request('collection_date');
        $notes = request('notes');
        $cheques = request('cheques');

        $type = 'Çek';

        $collection = Collection::create([
            'plasiyer_id' => $userQuery['plasiyer_id'] ?? null,
            'user_id' => $userQuery['user_id'],
            'sub_dealer_id' => $userQuery['sub_dealer_id'] ?? null,
            'creator_type' => $userQuery['creator_type'],
            'type' => $type,
            'sequence_number' => Collection::max('sequence_number') + 1,
            'maturity_number' => count($cheques),
            'collection_date' => $collection_date,
            'notes' => $notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($cheques as $cheque) {
            $cheque['amount'] = str_replace(',', '', $cheque['amount']);
            $collection->collectionCheques()->create($cheque);
        }

        session()->flash('success', 'Başarıyla çek oluşturuldu.');

        return response()->json(['success' => true, 'href' => route('collections.cheques.index')]);
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

    public function validator($request)
    {
        $validators = [
            'collection_date' => 'required'
        ];

        $messages = [
            'collection_date.required' => 'Lütfen tarih seçiniz.'
        ];

        $validator = Validator::make($request, $validators, $messages);

        return $validator;
    }
}
