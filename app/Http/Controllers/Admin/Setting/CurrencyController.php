<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Models\Currency;
use Illuminate\Http\Request;
use App\Services\CurrencyService;
use App\Http\Controllers\Controller;
use App\Http\Requests\CurrencyRequest;

class CurrencyController extends Controller
{
    protected $service;

    public function __construct(CurrencyService $service)
    {
        $this->middleware('auth:admin', ['except' => ['exchangeRateUpdate']]);
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = $this->service->getAllCurrencies();

        return view('admin.settings.currencies.index', compact('items'));
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Currency $currency)
    {
        return view('admin.settings.currencies.edit', compact('currency'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CurrencyRequest $request, Currency $currency)
    {
        $this->service->update($request, $currency);

        return response()->json([
            'status' => 'success',
            'message' => 'Başarıyla güncellendi!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
