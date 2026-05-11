<?php

namespace App\Http\Controllers;

class IndexController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web,subdealer');
    }

    public function index()
    {
        return view('homepage.index');
    }
}
