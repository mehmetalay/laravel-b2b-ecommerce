@extends('layouts.app')

@section('content')
    <section class="breadscrumb-section pt-0">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="breadscrumb-contain">
                        <h2>{{ trans('translations.menu.iletisim') }}</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="/">
                                        <i class="fa-solid fa-house"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">{{ trans('translations.menu.iletisim') }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="contact-box-section">
        <div class="container-fluid-lg">
            <div class="row g-lg-5 g-3">
                <div class="col-lg-6">
                    <div class="left-sidebar-box">
                        <div class="row">
                            <div class="col-xl-12 mb-5">
                                <div class="contact-title">
                                    <h3>{{ trans('translations.iletisim.istanbul_genel_merkez') }}</h3>
                                </div>
                                <div class="contact-detail">
                                    <div class="row g-4">
                                        <div class="col-xxl-6 col-lg-12 col-sm-6">
                                            <div class="contact-detail-box">
                                                <div class="contact-icon">
                                                    <i class="fa-solid fa-phone"></i>
                                                </div>
                                                <div class="contact-detail-title">
                                                    <h4>{{ trans('translations.iletisim.telefon') }}</h4>
                                                </div>
                                                <div class="contact-detail-contain">
                                                    <p><a href="tel:{{ general_info('company_phone_number') }}">{{ general_info('company_phone_number') }}</a></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-6 col-lg-12 col-sm-6">
                                            <div class="contact-detail-box">
                                                <div class="contact-icon">
                                                    <i class="fa-solid fa-envelope"></i>
                                                </div>
                                                <div class="contact-detail-title">
                                                    <h4>{{ trans('translations.iletisim.e_posta_adresi') }}</h4>
                                                </div>
                                                <div class="contact-detail-contain">
                                                    <p><a href="mailto:{{ general_info('email_address') }}">{{ general_info('email_address') }}</a></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-sm-6">
                                            <div class="contact-detail-box">
                                                <div class="contact-icon">
                                                    <i class="fa-solid fa-location-dot"></i>
                                                </div>
                                                <div class="contact-detail-title">
                                                    <h4>{{ trans('translations.iletisim.adres') }}</h4>
                                                </div>
                                                <div class="contact-detail-contain">
                                                    <a href="{{ general_info('google_maps_link') }}" target="_blank">
                                                        {{ general_info('company_full_address') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="title d-block">
                        <h2>{{ trans('translations.iletisim.bize_yazin') }}</h2>
                    </div>
                    <div class="right-sidebar-box">
                        <div class="row">
                            <div class="col-xxl-6 col-lg-12 col-sm-6">
                                <div class="mb-md-4 mb-3 custom-form">
                                    <label for="exampleFormControlInput" class="form-label">{{ trans('translations.iletisim.isim_soyisim') }}</label>
                                    <div class="custom-input">
                                        <input type="text" class="form-control" id="exampleFormControlInput">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xxl-6 col-lg-12 col-sm-6">
                                <div class="mb-md-4 mb-3 custom-form">
                                    <label for="exampleFormControlInput1" class="form-label">{{ trans('translations.iletisim.sirket_adi_firma_unvani') }}</label>
                                    <div class="custom-input">
                                        <input type="text" class="form-control" id="exampleFormControlInput1">
                                        <i class="fa-solid fa-building"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xxl-6 col-lg-12 col-sm-6">
                                <div class="mb-md-4 mb-3 custom-form">
                                    <label for="exampleFormControlInput2" class="form-label">{{ trans('translations.iletisim.e_posta_adresi') }}</label>
                                    <div class="custom-input">
                                        <input type="email" class="form-control" id="exampleFormControlInput2">
                                        <i class="fa-solid fa-envelope"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xxl-6 col-lg-12 col-sm-6">
                                <div class="mb-md-4 mb-3 custom-form">
                                    <label for="exampleFormControlInput3" class="form-label">{{ trans('translations.iletisim.telefon') }}</label>
                                    <div class="custom-input">
                                        <input type="tel" class="form-control" id="exampleFormControlInput3" maxlength="10" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);">
                                        <i class="fa-solid fa-mobile-screen-button"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-md-4 mb-3 custom-form">
                                    <label for="exampleFormControlTextarea" class="form-label">{{ trans('translations.iletisim.mesaj') }}</label>
                                    <div class="custom-textarea">
                                        <textarea class="form-control" id="exampleFormControlTextarea" rows="6"></textarea>
                                        <i class="fa-solid fa-message"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-animation btn-md fw-bold ms-auto">{{ trans('translations.iletisim.gonder') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="map-section">
        <div class="container-fluid p-0">
            <div class="map-box">
                {!! general_info('google_maps_embed') !!}
            </div>
        </div>
    </section>
@endsection
