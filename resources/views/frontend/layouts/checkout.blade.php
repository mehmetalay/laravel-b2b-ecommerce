<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @include('frontend.layouts.head')
    <style>
        header .top-nav .navbar-top {
            justify-content: center !important;
        }
    </style>
    <body class="bg-effect">
        <div class="fullpage-loader">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>
        <header>
            @include('frontend.partials.header-top')
            <div class="top-nav top-header sticky-header">
                <div class="container-fluid-lg">
                    <div class="row">
                        <div class="col-12">
                            <div class="navbar-top">
                                <a href="/" class="web-logo nav-logo">
                                    <img src="{{ image_url(config('images.default.logo'), 'images') }}" class="img-fluid blur-up lazyload" alt="">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        @yield('content')
        @include('frontend.layouts.modal')
        <x-company-name />
        <div class="bg-overlay"></div>
        @include('frontend.layouts.js')
    </body>
</html>
