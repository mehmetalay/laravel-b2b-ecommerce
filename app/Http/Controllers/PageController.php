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
        return view('frontend.pages.pages.about-us');
    }

    public function contactUs()
    {
        return view('frontend.pages.pages.contact-us');
    }

    public function privacyCommitment()
    {
        return view('frontend.pages.pages.privacy-commitment');
    }

    public function ourBankInformation()
    {
        return view('frontend.pages.pages.our-bank-information');
    }
}
