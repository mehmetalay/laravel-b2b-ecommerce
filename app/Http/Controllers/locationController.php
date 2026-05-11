<?php

namespace App\Http\Controllers;

use App\Models\{City, District, Neighborhood};

class locationController extends Controller
{
    public function __construct() {
        $this->middleware('auth:web,subdealer');
    }

    public function cities()
    {
        return response()->json([
            'status' => 'success',
            'data' => City::orderBy('name')->get()
        ]);
    }

    public function districts(int $cityId)
    {
        return response()->json([
            'status' => 'success',
            'data' => District::where('city_id', $cityId)->orderBy('name')->get()
        ]);
    }

    public function neighborhoods(int $districtId)
    {
        return response()->json([
            'status' => 'success',
            'data' => Neighborhood::where('district_id', $districtId)->orderBy('name')->get()
        ]);
    }
}