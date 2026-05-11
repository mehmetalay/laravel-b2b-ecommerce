<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web,subdealer');
    }

    public function aboutUs()
    {
        return view('pages.about-us');
    }

    public function contactUs()
    {
        return view('pages.contact-us');
    }

    public function privacyCommitment()
    {
        return view('pages.privacy-commitment');
    }

    public function ourBankInformation()
    {
        return view('pages.our-bank-information');
    }
}
