<?php

namespace App\Http\Controllers\Dealer;

use App\Models\SubDealer;
use Illuminate\Http\Request;
use App\Services\SubDealerService;
use App\Http\Controllers\Controller;
use App\Rules\UniqueEmailAcrossTables;
use Illuminate\Support\Facades\Validator;

class SubDealerController extends Controller
{
    protected $service;

    public function __construct(SubDealerService $service)
    {
        $this->middleware('auth:web');
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        abort_if(!(auth('web')->check() && auth('web')->user()->role === 'dealer'), 404);

        $items = $this->service->getByDealer(auth()->user()->current_account_id)->orderBy('id', 'DESC')->paginate(50);

        return view('dealer.subdealers.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('dealer.subdealers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validator($request);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'warning' => $validator->errors()->first()
            ]);
        }

        $data = $validator->validated();

        $subDealer = $this->service->create(auth('web')->user()->current_account_id, $data);

        $rowHtml = view('dealer.subdealers.row', ['model' => $subDealer])->render();

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
    public function edit(SubDealer $subDealer)
    {
        return view('dealer.subdealers.edit', compact('subDealer'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SubDealer $subDealer)
    {
        $validator = $this->validator($request, $subDealer->id);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'warning' => $validator->errors()->first()
            ]);
        }

        $data = $validator->validated();

        $subDealer = $this->service->update($data, $subDealer);

        $rowHtml = view('dealer.subdealers.row', ['model' => $subDealer])->render();

        return response()->json([
            'success' => true,
            'message' => 'Başarıyla güncellendi.',
            'type' => 'edit',
            'row' => $rowHtml,
            'id' => $subDealer->id
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(SubDealer $subDealer)
    {
        $this->service->delete($subDealer);

        return response()->json(['status' => 'success']);
    }

    private function validator($request, $id = null)
    {
        return Validator::make($request->all(), [
            'name' => 'required',
            'email' => [
                'required',
                'email'
            ],
            'username' => [
                'required',
                new UniqueEmailAcrossTables(auth('web')->user()->current_account_id, $id)
            ],
            'phone' => 'required|not_regex:/_/',
            'password' => $id ? 'nullable' : 'required',
            'status' => 'nullable',
            'can_place_order' => 'nullable',
            'can_approve_order' => 'nullable',
            'can_record_payment' => 'nullable',
            'can_view_prices' => 'nullable',
        ], [
            'name.required' => 'Lütfen ünvan adını giriniz.',
            'email.required' => 'Lütfen e-posta adresini giriniz.',
            'email.email' => 'Lütfen geçerli bir e-posta adresini giriniz.',
            'username.required' => 'Lütfen kullanıcı adını giriniz.',
            'phone.required' => 'Lütfen telefon numarasını giriniz.',
            'phone.not_regex' => 'Lütfen telefon numarasını giriniz.',
            'password.required' => 'Lütfen şifre giriniz.',
        ]);
    }

    public function cancelSelection()
    {
        session()->forget('acting_subdealer_id');

        return response()->json([
            'success' => true,
            'message' => 'Bayi seçimi iptal edildi.'
        ]);
    }
}
