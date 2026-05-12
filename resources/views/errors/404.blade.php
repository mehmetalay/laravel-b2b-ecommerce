@extends('frontend.layouts.app')

@section('content')
    <section class="section-404 section-lg-space">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="image-404">
                        <img src="{{ image_url(config('images.default.404'), 'inner_page.main') }}" class="img-fluid blur-up lazyload" alt="404">
                    </div>
                </div>
                <div class="col-12">
                    <div class="contain-404">
                        @if (app()->getLocale() == 'tr')
                            <h3 class="text-content">Aradığınız sayfa bulunamadı. Bu adresin bağlantısı eski olabilir veya en son yer işareti koyduğunuzdan bu yana adresi değiştirmiş olabiliriz.</h3>
                            <a href="/" class="btn btn-md text-white theme-bg-color mt-4 mx-auto">Anasayfa'ya Git</a>
                        @endif
                        @if (app()->getLocale() == 'en')
                            <h3 class="text-content">The page you were looking for was not found. The link to this address may be outdated, or we may have changed it since you last bookmarked it.</h3>
                            <a href="/" class="btn btn-md text-white theme-bg-color mt-4 mx-auto">Go to Home Page</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
