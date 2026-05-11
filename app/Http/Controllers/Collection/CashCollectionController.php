<?php

namespace App\Http\Controllers\Collection;

use App\Models\Collection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\CurrentAccountService;
use Illuminate\Support\Facades\Validator;

class CashCollectionController extends Controller
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

        $cashes = Collection::where('type', 'Nakit')
            ->where('plasiyer_id', auth('web')->user()->current_account_id)
            ->orderBy('id', 'DESC')
            ->paginate(50);

        return view('collections.cashes.index', compact('cashes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('collections.cashes.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validator(request()->all());

        if ($validator->fails()) {
            return response()->json(['warning' => $validator->errors()->first()]);
        }

        $userQuery = $this->currentAccountService->userQuery();

        $type = 'Nakit';

        $model = Collection::create([
            'plasiyer_id' => $userQuery['plasiyer_id'] ?? null,
            'user_id' => $userQuery['user_id'],
            'sub_dealer_id' => $userQuery['sub_dealer_id'] ?? null,
            'creator_type' => $userQuery['creator_type'],
            'type' => $type,
            'collection_date' => request('collection_date'),
            'sequence_number' => Collection::max('sequence_number') + 1,
            'amount' => str_replace(',', '', request('amount')),
            'currency_type' => request('currency_type'),
            'notes' => request('notes'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $rowHtml = view('collections.cashes.row', compact('model'))->render();

        return response()->json([
            'success' => true,
            'message' => 'Başarıyla eklendi.',
            'type' => 'add',
            'row' => $rowHtml
        ]);
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
        $collection = Collection::where('type', 'Nakit')->findOrFail($id);
        return view('collections.cashes.edit', ['model' => $collection]);
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
        $validator = $this->validator(request()->all());

        if ($validator->fails()) {
            return response()->json(['warning' => $validator->errors()->first()]);
        }

        $collection = Collection::where('type', 'Nakit')->findOrFail($id);

        $collection->update([
            'collection_date' => request('collection_date'),
            'amount' => str_replace(',', '', request('amount')),
            'currency_type' => request('currency_type'),
            'notes' => request('notes'),
            'updated_at' => now()
        ]);

        $rowHtml = view('collections.cashes.row', ['model' => $collection])->render();

        return response()->json([
            'success' => true,
            'message' => 'Başarıyla güncellendi.',
            'type' => 'edit',
            'row' => $rowHtml,
            'id' => $collection->id
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

    public function validator($request, $modelId = null)
    {
        $validators = [
            'collection_date' => 'required',
            'amount' => 'required',
            'currency_type' => 'required'
        ];

        $messages = [
            'collection_date.required' => 'Lütfen tarih seçiniz.',
            'amount.required' => 'Lütfen tutar giriniz.',
            'currency_type.required' => 'Lütfen döviz türü seçiniz.'
        ];

        $validator = Validator::make($request, $validators, $messages);

        return $validator;
    }
}
