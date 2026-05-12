@extends('frontend.layouts.login')

@section('content')
    <section class="log-in-section background-image-2 section-b-space">
        <div class="container-fluid-lg w-100">
            <div class="row justify-content-center">
                <div class="col-xxl-4 col-xl-5 col-lg-6 col-sm-8 mx-auto">
                    <div class="log-in-box">
                        <div class="log-in-img text-center">
                            <img src="{{ image_url(config('images.default.logo'), 'images') }}" class="img-fluid">
                        </div>
                        <div class="log-in-title text-center">
                            <h3>{{ trans('translations.auth.alt_bayi_girisi') }}</h3>
                        </div>
                        <div class="input-box">
<div
                                data-js="subdealer-login-config"
                                data-request-error="{{ trans('translations.istek_sirasinda_bir_hata_olustu_lutfen_daha_sonra_tekrar_deneyiniz') }}"
                                data-loading-login-text="{{ trans('translations.auth.giris_yapiliyor_lutfen_bekleyin') }}"
                                data-loading-change-password-text="{{ trans('translations.auth.degistiriliyor_lutfen_bekleyin') }}"
                                data-contract-title="{{ trans('translations.login_controller.sozlesme_onayi') }}"
                                data-change-password-title="{{ trans('translations.login_controller.sifre_degistirme_zorunlulugu') }}"
                                data-confirm-button-text="{{ trans('translations.auth.tamam') }}"
                                data-password-change-success="{{ trans('translations.auth.sifreniz_basariyla_degistirildi_yeni_sifrenizi_kullanarak_giris_yapabilirsiniz') }}"
                                hidden
                            ></div>
                            <form class="row g-4" id="login-form" method="POST" action="{{ route('sub-dealer.login') }}" data-js="subdealer-login-form">
                                @csrf
                                <div class="col-12">
                                    <div class="form-floating theme-form-floating log-in-form">
                                        <input type="text" class="form-control" name="dealer_code" id="dealer_code" placeholder="{{ trans('translations.auth.bayi_kodu') }}" autofocus>
                                        <label for="dealer_code">{{ trans('translations.auth.bayi_kodu') }}</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating theme-form-floating log-in-form">
                                        <input type="text" class="form-control" name="username" id="username" placeholder="{{ trans('translations.auth.kullanici_adi') }}">
                                        <label for="username">{{ trans('translations.auth.kullanici_adi') }}</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating theme-form-floating log-in-form">
                                        <input type="password" class="form-control" name="password" id="password" placeholder="{{ trans('translations.auth.sifre') }}">
                                        <label for="password">{{ trans('translations.auth.sifre') }}</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="forgot-box">
                                        <div class="form-check ps-0 m-0 remember-box">
                                            <input class="checkbox_animated check-box" type="checkbox" name="remember" id="flexCheckDefault">
                                            <label class="form-check-label" for="flexCheckDefault">{{ trans('translations.auth.beni_hatirla') }}</label>
                                        </div>
                                        <a href="{{ route('sub-dealer.password.forgot.form') }}" class="forgot-password">{{ trans('translations.auth.sifremi_unuttum') }}</a>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-animation w-100 justify-content-center login-button" type="submit">{{ trans('translations.auth.giris_yap') }}</button>
                                </div>
                                <div class="other-log-in">
                                    <h6></h6>
                                </div>
                                <div class="log-in-button">
                                    <ul>
                                        <li>
                                            <a href="{{ route('bayilik-basvurusu.index') }}" class="btn w-100">
                                                {{ trans('translations.auth.bayimiz_olun') }}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </form>
                            <div class="mt-4 text-center">
                                <a href="{{ route('language', ['tr']) }}">
                                    <img src="{{ image_url(config('images.default.country.tr'), 'country') }}" alt="Türkçe" class="img-fluid me-2" width="50">
                                </a>
                                <a href="{{ route('language', ['en']) }}">
                                    <img src="{{ image_url(config('images.default.country.en'), 'country') }}" alt="English" class="img-fluid" width="50">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="section-small-space">
                    <x-company-name />
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ mix('js/frontend/modules/auth/subdealer-login.js') }}"></script>
@endpush

