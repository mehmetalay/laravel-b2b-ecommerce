@extends('frontend.layouts.login')

@section('content')
    <section class="b2b-login">

        <div class="b2b-login__wrap">
            <div class="b2b-login__panel">
                <div class="b2b-login__brand">
                    <img src="{{ image_url(config('images.default.logo'), 'images') }}" alt="Logo">
                </div>

                <h1 class="b2b-login__title">B2B GİRİŞ PANELİ</h1>

                <div
                    data-js="auth-login-config"
                    data-request-error="{{ trans('translations.istek_sirasinda_bir_hata_olustu_lutfen_daha_sonra_tekrar_deneyiniz') }}"
                    data-contract-title="{{ trans('translations.login_controller.sozlesme_onayi') }}"
                    data-change-password-title="{{ trans('translations.login_controller.sifre_degistirme_zorunlulugu') }}"
                    data-confirm-button-text="{{ trans('translations.auth.tamam') }}"
                    data-password-change-success="{{ trans('translations.auth.sifreniz_basariyla_degistirildi_yeni_sifrenizi_kullanarak_giris_yapabilirsiniz') }}"
                    hidden
                ></div>

                <form class="b2b-login__form" id="login-form" method="POST" action="{{ route('login') }}" data-js="auth-login-form">
                    @csrf

                    <div class="b2b-field">
                        <label for="username" class="b2b-field__label">{{ trans('translations.auth.kullanici_kodu') }}</label>
                        <input type="text" class="b2b-field__input" name="username" id="username" placeholder="{{ trans('translations.auth.kullanici_kodu') }}" autocomplete="username">
                    </div>

                    <div class="b2b-field">
                        <label for="password" class="b2b-field__label">{{ trans('translations.auth.sifre') }}</label>

                        <div class="b2b-field__password">
                            <input type="password" class="b2b-field__input" name="password" id="password"
                                   placeholder="{{ trans('translations.auth.sifre') }}" autocomplete="current-password">

                            <button type="button"
                                    class="b2b-field__toggle"
                                    data-toggle="password"
                                    data-action="toggle-password"
                                    data-label-show="Şifreyi göster"
                                    data-label-hide="Şifreyi gizle"
                                    aria-label="Şifreyi göster"
                                    aria-pressed="false">

                                <svg class="icon icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                     xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>

                                <svg class="icon icon-eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                     xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M10.58 10.58a2 2 0 0 0 2.83 2.83" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9.88 5.09A10.94 10.94 0 0 1 12 5c6.5 0 10 7 10 7a18.3 18.3 0 0 1-2.16 3.19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M6.61 6.61A18.12 18.12 0 0 0 2 12s3.5 7 10 7c1.12 0 2.16-.19 3.12-.51" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M2 2l20 20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="b2b-login__row">
                        <label class="b2b-check">
                            <input type="checkbox" name="remember" id="flexCheckDefault">
                            <span>{{ trans('translations.auth.beni_hatirla') }}</span>
                        </label>

                        <a href="{{ route('password.forgot.form') }}" class="b2b-login__link">
                            {{ trans('translations.auth.sifremi_unuttum') }}
                        </a>
                    </div>

                    <button class="b2b-btn b2b-btn--primary" type="submit">
                        {{ trans('translations.auth.giris_yap') }}
                    </button>

                    <a href="{{ route('sub-dealer.login') }}" class="b2b-btn b2b-btn--danger">
                        {{ trans('translations.auth.alt_bayi_girisi') }}
                    </a>

                    <a href="{{ route('bayilik-basvurusu.index') }}" class="b2b-btn b2b-btn--ghost">
                        {{ trans('translations.auth.bayimiz_olun') }}
                    </a>

                    <div class="b2b-login__langs">
                        <a href="{{ route('language', ['tr']) }}" aria-label="Türkçe">
                            <img src="{{ image_url(config('images.default.country.tr'), 'country') }}" alt="Türkçe">
                        </a>
                        <a href="{{ route('language', ['en']) }}" aria-label="English">
                            <img src="{{ image_url(config('images.default.country.en'), 'country') }}" alt="English">
                        </a>
                    </div>

                    <div class="b2b-login__footer">
                        <x-company-name />
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ mix('js/frontend/modules/auth/login.js') }}"></script>
@endpush

