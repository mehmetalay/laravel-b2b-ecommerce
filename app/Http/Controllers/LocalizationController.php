<?php

namespace App\Http\Controllers;

use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\Facades\Session;

class LocalizationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web,subdealer');
    }

    public function switch(Request $request, string $languageCode): RedirectResponse
    {
        switch ($languageCode) {
            case 'en':
                Session::put('language', 'en');
                break;
            default:
                Session::pull('language');
                break;
        }

        return redirect()->back();
    }
}