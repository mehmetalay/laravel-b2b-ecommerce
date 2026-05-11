@extends('layouts.app')

@section('content')
    <section class="breadscrumb-section pt-0">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="breadscrumb-contain">
                        <h2>{{ trans('translations.menu.hakkimizda') }}</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="/">
                                        <i class="fa-solid fa-house"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">{{ trans('translations.menu.hakkimizda') }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="fresh-vegetable-section section-lg-space">
        <div class="container-fluid-lg">
            <div class="row gx-xl-5 gy-xl-0 g-3">
                <div class="col-12">
                    <div class="fresh-contain p-center-left">
                        <div>
                            @if (app()->getLocale() == 'tr')
                                <div class="review-title">
                                    <h4>TEST FIRMA</h4>
                                    <h2>Hakkımızda</h2>
                                </div>
                            @endif
                            @if (app()->getLocale() == 'en')
                                <div class="review-title">
                                    <h4>TEST COMPANY</h4>
                                    <h2>About Us</h2>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
