@extends('layouts.checkout')

@section('css')
    <link rel="stylesheet" href="/assets/css/payment.css">
@endsection

@section('content')
    <section class="payment-box-section section-b-space">
        <div class="container">
            <div class="row">
                <div class="col-xl-7 col-lg-8 col-sm-9 mx-auto">
                    <div class="mb-4 text-center">
                        <h2>{{ trans('translations.payment.page.odeme_sayfasi') }}</h2>
                    </div>
                    <form class="payment-box" id="payment-form" method="POST" action="{{ route('payment-link.request', [$paymentLink->token]) }}">
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <div class="form-floating mb-lg-4 mb-4 theme-form-floating">
                                    @if ($paymentLink->transaction_type == 3 && $paymentLink->manual_lock_bank_installment)
                                        <input type="hidden" name="bank_integration_id" value="{{ $paymentLink->manual_bank_integration_id }}">
                                    @endif
                                    @if ($paymentLink->transaction_type == 2)
                                        <input type="hidden" name="bank_integration_id" value="{{ $automaticSinglePaymentId }}">
                                    @endif
                                    <select class="form-control" {{ ($paymentLink->transaction_type == 3 && $paymentLink->manual_lock_bank_installment) || $paymentLink->transaction_type == 2 ? '' : 'name=bank_integration_id' }} id="bank_integration_id" placeholder="{{ trans('translations.payment.page.banka') }}" {{ ($paymentLink->transaction_type == 3 && $paymentLink->manual_lock_bank_installment) || $paymentLink->transaction_type == 2 ? 'disabled' : '' }}>
                                        <option value="" hidden selected>{{ trans('translations.payment.page.sec') }}</option>
                                        @foreach ($bankIntegrations as $bankIntegration)
                                            <option value="{{ $bankIntegration->id }}" {{ $paymentLink->transaction_type == 3 ? ($paymentLink->manual_bank_integration_id == $bankIntegration->id ? 'selected' : '') : ($paymentLink->transaction_type == 2 ? ($automaticSinglePaymentId == $bankIntegration->id ? 'selected' : '') : '') }}>{{ $bankIntegration->name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="bank_integration_id">{{ trans('translations.payment.page.banka') }}</label>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <div class="form-floating mb-lg-4 mb-4 theme-form-floating">
                                    <input type="text" class="form-control" name="amount" id="amount" data-format="price" value="{{ number_format($paymentLink->amount, 2) }}" {{ $paymentLink->amount_locked ? 'readonly' : '' }}>
                                    <label for="amount">{{ trans('translations.payment.page.cekilecek_tutar') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <div class="form-floating mb-lg-4 mb-4 theme-form-floating">
                                    <input type="text" class="form-control" name="credit_card_name" id="credit_card_name">
                                    <label for="credit_card_name">{{ trans('translations.payment.page.kredi_karti_adi_soyad') }}</label>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <div class="form-floating mb-lg-4 mb-4 theme-form-floating">
                                    <input type="text" class="form-control" name="credit_card_number" id="credit_card_number">
                                    <label for="credit_card_number">{{ trans('translations.payment.page.kredi_karti_no') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <div class="form-floating mb-lg-4 mb-4 theme-form-floating">
                                    <input type="text" class="form-control" name="credit_card_exp_date" id="credit_card_exp_date">
                                    <label for="credit_card_exp_date">{{ trans('translations.payment.page.ay_yil') }}</label>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <div class="form-floating mb-lg-4 mb-4 theme-form-floating">
                                    <input type="number" class="form-control" name="cvc" id="cvc">
                                    <label for="cvc">{{ trans('translations.payment.page.cvc') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <div class="form-floating mb-lg-4 mb-4 theme-form-floating">
                                    <input type="text" class="form-control" name="phone_number" id="phone_number" data-mask-phone>
                                    <label for="phone_number">{{ trans('translations.payment.page.telefon_numarasi') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating mb-lg-4 mb-4 theme-form-floating">
                            <textarea class="form-control" name="explanation" id="explanation" rows="4"></textarea>
                            <label for="explanation">{{ trans('translations.payment.page.aciklama') }}</label>
                        </div>
                        <div class="installment-box">
                            <div class="installment-title">
                                <h4>{{ trans('translations.payment.page.taksit_secenekleri') }}</h4>
                            </div>
                            <div class="installment-detail">
                                <div class="row g-4" data-js="list-installment">
                                    <small>{{ trans('translations.payment.page.kart_bilgilerini_girdiginizde_taksit_seceneklerini_listelenecektir') }}</small>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-animation w-100 mt-3" id="form-button">{{ trans('translations.payment.page.ode') }}</button>
                        <input type="hidden" name="token" value="{{ $paymentLink->token }}">
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script src="/assets/js/jquery.validate.min.js"></script>
    <script>
        var paymentModalName = $('#payment-modal');
        var paymentModalBody = $('[data-js=payment-modal-body]');

        $(document).ready(function() {
            var paymentModal = new bootstrap.Modal(paymentModalName, {
                backdrop: 'static',
                keyboard: false
            });
            var form = $('#payment-form');

            $.validator.addMethod("fullNameValidation", function (value, element) {
                if (value) {
                    value = value.trim().replace(/\s+/g, ' ');
                }
                return this.optional(element) || /^[a-zA-ZğüşıöçĞÜŞİÖÇ]+([-']?[a-zA-ZğüşıöçĞÜŞİÖÇ]+)*( [a-zA-ZğüşıöçĞÜŞİÖÇ]+([-']?[a-zA-ZğüşıöçĞÜŞİÖÇ]+)*)+$/.test(value);
            }, '{{ trans('translations.payment.page.lutfen_tam_adinizi_adsoyad_olacak_sekilde_giriniz') }}');

            $.validator.addMethod("phoneWithMaskFormat", function (value, element) {
                value = value.replace(/\D/g, '');

                const mobileRegex = /^(501|505|506|507|510|516|530|531|532|533|534|535|536|537|538|539|540|541|542|543|544|545|546|547|548|549|551|552|553|554|555|559|561)\d{7}$/;
                const landlineRegex = /^(212|216|322|416|272|472|382|358|312|242|478|466|256|266|378|488|458|228|426|434|374|248|224|286|376|364|258|412|380|284|424|446|442|222|342|454|456|438|326|476|246|232|344|370|338|474|366|352|318|288|386|348|262|332|274|422|236|482|324|252|436|384|388|452|328|464|264|362|484|368|346|414|486|282|356|462|428|276|432|226|354|372)\d{7}$/;

                return this.optional(element) || mobileRegex.test(value) || landlineRegex.test(value);
            }, '{{ trans('translations.payment.page.lutfen_gecerli_bir_telefon_numarasi_giriniz') }}');

            if (form.length > 0) {
                form.validate({
                    rules: {
                        bank_integration_id: {
                            required: true
                        },
                        amount: {
                            required: true
                        },
                        credit_card_name: {
                            required: true,
                            maxlength: 100,
                            fullNameValidation: true
                        },
                        credit_card_number: {
                            required: true,
                            minlength: 18,
                            maxlength: 19
                        },
                        credit_card_exp_date: {
                            required: true,
                            minlength: 5,
                            maxlength: 5
                        },
                        cvc: {
                            required: true,
                            minlength: 3,
                            maxlength: 4
                        },
                        installment_id: {
                            required: true
                        },
                        phone_number: {
                            required: true,
                            phoneWithMaskFormat: true
                        }
                    },
                    messages: {
                        bank_integration_id: {
                            required: '{{ trans('translations.payment.page.bu_alani_bos_birakmayiniz') }}',
                        },
                        amount: {
                            required: '{{ trans('translations.payment.page.bu_alani_bos_birakmayiniz') }}',
                        },
                        credit_card_name: {
                            required: '{{ trans('translations.payment.page.bu_alani_bos_birakmayiniz') }}',
                            maxlength: '{{ trans('translations.payment.page.max_100_karakterden_fazla_olmamalidir') }}'
                        },
                        credit_card_number: {
                            required: '{{ trans('translations.payment.page.bu_alani_bos_birakmayiniz') }}',
                            minlength: '{{ trans('translations.payment.page.lutfen_gecerli_bir_kredi_karti_numarasi_giriniz') }}',
                            maxlength: '{{ trans('translations.payment.page.lutfen_gecerli_bir_kredi_karti_numarasi_giriniz') }}'
                        },
                        credit_card_exp_date: {
                            required: '{{ trans('translations.payment.page.bu_alani_bos_birakmayiniz') }}',
                            minlength: '{{ trans('translations.payment.page.lutfen_kartin_son_kullanma_tarihi_giriniz') }}',
                            maxlength: '{{ trans('translations.payment.page.lutfen_kartin_son_kullanma_tarihi_giriniz') }}'
                        },
                        cvc: {
                            required: '{{ trans('translations.payment.page.bu_alani_bos_birakmayiniz') }}',
                            minlength: '{{ trans('translations.payment.page.lutfen_gecerli_bir_kart_dogrulama_kodu_giriniz') }}',
                            maxlength: '{{ trans('translations.payment.page.lutfen_gecerli_bir_kart_dogrulama_kodu_giriniz') }}'
                        },
                        installment_id: {
                            required: '{{ trans('translations.payment.page.taksit_seciniz') }}'
                        },
                        phone_number: {
                            required: '{{ trans('translations.payment.page.bu_alani_bos_birakmayiniz') }}'
                        }
                    },
                    submitHandler: function () {
                        var loader = showLoader();
                        var formAction = $(form).attr('action');
                        var formData = $(form).serialize();

                        $.post(formAction, formData, function(data) {
                            if (data.error) {
                                notify('error', data.error);
                                loader.hide();
                            } else if (data.without_3d) {
                                notify((data.error ? 'error' : 'success'), (data.error ? data.error : data.success));
                                loader.hide();
                            } else {
                                if (data.url) {
                                    var iframeContent = '<iframe style="width:100%;height:100%" src="' + data.url + '"></iframe>';
                                } else {
                                    var iframeContent = '<iframe style="width:100%;height:100%"></iframe>';
                                }
                                paymentModal.show();
                                paymentModalBody.html(iframeContent);
                                if (!data.url) {
                                    $('body').find('iframe').contents().find('body').append(data).find('form').submit();
                                }
                                loader.hide();
                            }
                        });
                    }
                });
            }
        });

        $(document).ready(function() {
            var bankSelector = {!! json_encode(($paymentLink->transaction_type == 3 && $paymentLink->manual_lock_bank_installment) || $paymentLink->transaction_type == 2 ? 'input[name="bank_integration_id"]' : 'select[name="bank_integration_id"]') !!};

            $('input[name="amount"], ' + bankSelector + ', input[name="credit_card_number"]').on('input', debounce(function () {
                var formButton = $('#form-button');
                formButton.prop('disabled', true).addClass('btn-loading');

                var amount = $('input[name="amount"]').val();
                var bank_integration_id = $(bankSelector).val();
                var credit_card_number = $('input[name="credit_card_number"]').val().replace(' ', '');
                var token = $('input[name="token"]').val();

                if (amount != '' && bank_integration_id != '' && credit_card_number != '' && credit_card_number.length >= 8 && token != '') {
                    $.post('{{ route('payments.payment-link.list-installment') }}', {
                        amount: amount,
                        bank_integration_id: bank_integration_id,
                        credit_card_number: credit_card_number,
                        token: token
                    }, function (data) {
                        if (data.result == 'success') {
                            $('[data-js=list-installment]').html(data.view);
                            formButton.prop('disabled', false).removeClass('btn-loading');
                        } else {
                            $('[data-js=list-installment]').html('{{ trans('translations.payment.page.lutfen_kart_bilgileri_kontrol_ediniz') }}');
                        }
                    }, 'json');
                } else {
                    $('[data-js=list-installment]').html('<small>{{ trans('translations.payment.page.kart_bilgilerini_girdiginizde_taksit_seceneklerini_listelenecektir') }}</small>');
                }
            }, 500));
        });
    </script>
@endsection
