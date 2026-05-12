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
                            <h3>{{ trans('translations.auth.yeni_sifre') }}</h3>
                        </div>
                        <div class="input-box">
<div
                                data-js="subdealer-reset-password-config"
                                data-request-error="{{ trans('translations.istek_sirasinda_bir_hata_olustu_lutfen_daha_sonra_tekrar_deneyiniz') }}"
                                data-success-message="{{ trans('translations.auth.sifreniz_basariyla_degistirildi_yeni_sifrenizi_kullanarak_giris_yapabilirsiniz') }}"
                                data-confirm-button-text="{{ trans('translations.auth.tamam') }}"
                                data-loading-submit-text="{{ trans('translations.auth.degistiriliyor_lutfen_bekleyin') }}"
                                hidden
                            ></div>
                            <form class="row g-4" id="reset-form" action="{{ route('sub-dealer.password.reset', ['code' => $resetCode]) }}" method="POST" data-js="subdealer-reset-password-form">
                                @csrf
                                <div class="col-12">
                                    <div class="form-floating theme-form-floating log-in-form">
                                        <input type="password" class="form-control" name="new_password" id="new_password" placeholder="{{ trans('translations.auth.yeni_sifre') }}">
                                        <label for="new_password">{{ trans('translations.auth.yeni_sifre') }}</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating theme-form-floating log-in-form">
                                        <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="{{ trans('translations.auth.yeni_sifre_tekrar') }}">
                                        <label for="confirm_password">{{ trans('translations.auth.yeni_sifre_tekrar') }}</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-animation w-100 justify-content-center login-button" type="submit">{{ trans('translations.auth.degistir') }}</button>
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
    <script src="{{ mix('js/frontend/modules/auth/subdealer-reset-password.js') }}"></script>
@endpush

