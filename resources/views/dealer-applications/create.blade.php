@extends('frontend.layouts.checkout')

@section('title', 'Bayilik Başvurusu')

@section('css')
    <style>
        .info-section {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .info-section h6 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 16px;
            font-weight: 500;
            color: var(--theme-color) !important;
        }
    </style>
@endsection

@section('content')
    <section class="section-b-space">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="title">
                        <h2>{{ trans('translations.bayi_basvurusu.bayi_basvuru_formu') }}</h2>
                        <p>{{ trans('translations.bayi_basvurusu.alt_aciklama') }}</p>
                    </div>
                </div>
                <form class="mt-4" action="{{ route('bayilik-basvurusu.store') }}" method="POST" id="dealer-application-form" enctype="multipart/form-data">
                    @csrf
                    <div class="info-section">
                        <h6>{{ trans('translations.bayi_basvurusu.firma_bilgileri') }}</h6>
                        <div class="form-group row">
                            <div class="col-sm-12 col-md-6 mb-3">
                                <label for="company_name">{{ trans('translations.bayi_basvurusu.sirket_adi_firma_unvani') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="company_name" id="company_name">
                            </div>
                            <div class="col-sm-12 col-md-6 mb-3">
                                <label for="tax_office">{{ trans('translations.bayi_basvurusu.vergi_dairesi') }}</label>
                                <input type="text" class="form-control form-control-sm" name="tax_office" id="tax_office">
                            </div>
                            <div class="col-sm-12 col-md-6 mb-3">
                                <label for="tax_number">{{ trans('translations.bayi_basvurusu.vergi_numarasi') }}</label>
                                <input type="text" class="form-control form-control-sm" name="tax_number" id="tax_number">
                            </div>
                        </div>
                    </div>
                    <div class="info-section">
                        <h6>{{ trans('translations.bayi_basvurusu.adres_bilgileri') }}</h6>
                        <div class="form-group row">
                            <div class="col-sm-12 col-md-6 mb-3">
                                <label for="city">{{ trans('translations.bayi_basvurusu.sehir') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="city" id="city">
                            </div>
                            <div class="col-sm-12 col-md-6 mb-3">
                                <label for="district">{{ trans('translations.bayi_basvurusu.ilce') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="district" id="district">
                            </div>
                            <div class="col-12 mb-3">
                                <label for="address">{{ trans('translations.bayi_basvurusu.adres') }} <span class="text-danger">*</span></label>
                                <textarea class="form-control form-control-sm" name="address" id="address" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="info-section">
                        <h6>{{ trans('translations.bayi_basvurusu.kisi_bilgileri') }}</h6>
                        <div class="form-group row">
                            <div class="col-sm-12 col-md-6 mb-3">
                                <label for="authorized_name_surname">{{ trans('translations.bayi_basvurusu.yetkili_ad_soyad') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="authorized_name_surname" id="authorized_name_surname">
                            </div>
                            <div class="col-sm-12 col-md-6 mb-3">
                                <label for="identity_number">{{ trans('translations.bayi_basvurusu.tc_kimlik_numarasi') }}</label>
                                <input type="text" class="form-control form-control-sm" name="identity_number" id="identity_number">
                                <small class="text-danger">{{ trans('translations.bayi_basvurusu.sahis_firmalari_icin') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="info-section">
                        <h6>{{ trans('translations.bayi_basvurusu.iletisim_bilgileri') }}</h6>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-12 col-md-6 mb-3">
                                <label for="phone">{{ trans('translations.bayi_basvurusu.telefon_numarasi') }}</label>
                                <input type="text" class="form-control form-control-sm" name="phone_number" id="phone" data-mask-phone>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-3">
                                <label for="mobile_phone">{{ trans('translations.bayi_basvurusu.cep_telefonu_numarasi') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="mobile_phone_number" id="mobile_phone" data-mask-phone>
                                <small class="text-danger">{{ trans('translations.bayi_basvurusu.sms_bilgilendirmesi_icin') }}</small>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-3">
                                <label for="fax">{{ trans('translations.bayi_basvurusu.faks_numarasi') }}</label>
                                <input type="text" class="form-control form-control-sm" name="fax_number" id="fax" data-mask-phone>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-3">
                                <label for="email_address">{{ trans('translations.bayi_basvurusu.e_posta_adresi') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="email_address" id="email_address">
                            </div>
                            <div class="col-sm-12 col-md-6 mb-3">
                                <label for="web_address">{{ trans('translations.bayi_basvurusu.web_adresi') }}</label>
                                <input type="text" class="form-control form-control-sm" name="web_address" id="web_address">
                            </div>
                        </div>
                    </div>
                    <div class="info-section">
                        <h6>{{ trans('translations.bayi_basvurusu.evraklar') }}</h6>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-12 col-md-6 mb-3">
                                <input type="file" class="form-control form-control-sm" name="documents[]" multiple>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <button type="submit" class="btn theme-bg-color btn-md fw-bold mt-4 text-white float-end">{{ trans('translations.bayi_basvurusu.gonder') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script>
        $('body').on('submit', '#dealer-application-form', function(e) {
            e.preventDefault();
            var that = $(this);
            var button = that.find('button');
            var htmlButton = button.html();
            button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>&nbsp;{{ trans('translations.bayi_basvurusu.gonderiliyor_lutfen_bekleyin') }}');

            var formData = new FormData(this);

            $.ajax({
                url: that.attr('action'),
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(data) {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Başarılı',
                            text: '{{ trans('translations.bayi_basvurusu.basvurunuz_basariyla_alindi') }}',
                            timer: 5000,
                            timerProgressBar: true
                        });
                        button.html(htmlButton).prop('disabled', false);
                        that[0].reset();
                    } else {
                        button.html(htmlButton).prop('disabled', false);
                        var message = data.warning ? data.warning : data.error;
                        notify((data.warning ? 'warning' : 'error'), message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    button.html(htmlButton).prop('disabled', false);
                    notify('error', '{{ trans('translations.istek_sirasinda_bir_hata_olustu_lutfen_daha_sonra_tekrar_deneyiniz') }}');
                }
            });
        });
    </script>
@endsection
