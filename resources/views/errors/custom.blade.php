@extends('layouts.app')

@section('content')
    <section class="section-404 section-lg-space">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="contain-404">
                        @if (app()->getLocale() == 'tr')
                            <h3 class="text-content">Sunucu hatası nedeniyle sayfa görüntülenemiyor. Lütfen daha sonra tekrar deneyiniz.</h3>
                            <a href="/" class="btn btn-md text-white theme-bg-color mt-4 mx-auto">Anasayfa'ya Git</a>
                        @endif
                        @if (app()->getLocale() == 'en')
                            <h3 class="text-content">The page cannot be displayed due to server error. Please try again later.</h3>
                            <a href="/" class="btn btn-md text-white theme-bg-color mt-4 mx-auto">Go to Home Page</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
