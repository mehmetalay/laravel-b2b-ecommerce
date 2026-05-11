@extends('layouts.login')

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
                            <h3>{{ trans('translations.auth.sifremi_unuttum') }}</h3>
                        </div>
                        <div class="input-box">
                            <div
                                data-js="auth-forgot-password-config"
                                data-request-error="{{ trans('translations.istek_sirasinda_bir_hata_olustu_lutfen_daha_sonra_tekrar_deneyiniz') }}"
                                data-success-message="{{ trans('translations.auth.sifre_degistirme_baglantisi_e_posta_adresinize_gonderildi') }}"
                                data-confirm-button-text="{{ trans('translations.auth.tamam') }}"
                                hidden
                            ></div>
                            <form class="row g-4" id="forgot-form" method="POST" action="{{ route('password.forgot') }}" data-js="auth-forgot-password-form">
                                @csrf
                                <div class="col-12">
                                    <div class="form-floating theme-form-floating log-in-form">
                                        <input type="text" class="form-control" name="username" id="username" placeholder="{{ trans('translations.auth.e_posta_adresi') }}" autofocus>
                                        <label for="username">{{ trans('translations.auth.e_posta_adresi') }}</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-animation w-100 justify-content-center login-button" type="submit">{{ trans('translations.auth.gonder') }}</button>
                                </div>
                                <div class="col-12">
                                    <div class="forgot-box justify-content-center">
                                        <a href="{{ route('login.form') }}" class="forgot-password"><small>{{ trans('translations.auth.giris_sayfasina_don') }}</small></a>
                                    </div>
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
    <script src="{{ mix('js/frontend/modules/auth/forgot-password.js') }}"></script>
@endpush

