<?php

namespace App\Http\Controllers\Admin\Setting\DesignSetting;

use App\Models\ThemeSetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ThemeSettingService;

class ThemeSettingController extends Controller
{
    protected $service;

    public function __construct(ThemeSettingService $service)
    {
        $this->middleware('auth:admin');
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $themeSetting = $this->service->getFirst();

        return view('backend.pages.settings.design-settings.theme-settings.index', compact('themeSetting'));
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
    public function update(Request $request, ThemeSetting $themeSetting)
    {
        $this->service->update($request, $themeSetting);

        return response()->json([
            'status' => 'success',
            'message' => 'Başarıyla güncellendi!'
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
}
