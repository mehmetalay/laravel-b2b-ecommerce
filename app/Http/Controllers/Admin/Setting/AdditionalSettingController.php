<?php

namespace App\Http\Controllers\Admin\Setting;

use Illuminate\Http\Request;
use App\Models\AdditionalSetting;
use App\Http\Controllers\Controller;
use App\Services\AdditionalSettingService;

class AdditionalSettingController extends Controller
{
    protected $service;

    public function __construct(AdditionalSettingService $service)
    {
        $this->middleware('auth:admin');
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $additionalSetting = $this->service->getFirst();

        return view('admin.settings.additional-settings.index', compact('additionalSetting'));
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AdditionalSetting $additionalSetting)
    {
        $this->service->update($request, $additionalSetting);

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
