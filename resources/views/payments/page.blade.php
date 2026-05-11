@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="/assets/css/payment.css">
    <style>
        .select2.select2-container .selection {
            width: 100%;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            top: auto !important;
        }

        .select2-container--default .select2-selection--single {
            border: 1px solid #ced4da !important;
        }

        .select2-container--default .select2-selection--single {
            height: calc(51px + (54 - 51) * ((100vw - 320px) / (1920 - 320))) !important;
            line-height: normal !important;
            display: flex !important;
            align-items: center !important;
            padding: 0 12px !important;
            font-size: calc(14px + (15 - 14) * ((100vw - 320px) / (1920 - 320))) !important;
        }

        .select2-selection__rendered {
            display: flex !important;
            align-items: center !important;
            gap: 5px;
        }

        .select2-results__option {
            display: flex !important;
            align-items: center !important;
            gap: 8px;
            padding: 4px 12px !important;
        }

        .select2-selection__rendered img,
        .select2-results__option img {
            width: 100px;
            height: auto;
        }
    </style>
    <style>
        .cc-wrap {
            position: relative;
            width: 100%;
            aspect-ratio: 420 / 260;
            perspective: 1200px;
        }

        .cc {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            border-radius: 18px;
            backface-visibility: hidden;
            transform-style: preserve-3d;
            transition: transform .55s ease;
        }

        .cc-front {
            transform: rotateY(0deg);
        }

        .cc-back {
            transform: rotateY(180deg);
        }

        .cc-wrap[data-side="back"] .cc-front {
            transform: rotateY(-180deg);
        }

        .cc-wrap[data-side="back"] .cc-back {
            transform: rotateY(0deg);
        }
    </style>
@endsection

@section('content')
    <section class="section-small-space pt-0">
        <div class="container-fluid" id="hp-payments" data-hp="payments"></div>
    </div>

    <section class="payment-box-section section-b-space">
        <div class="container">
            <div class="mb-4 text-center">
                <h2>{{ trans('translations.payment.page.odeme_sayfasi') }}</h2>
            </div>
            <form class="payment-box" id="payment-form" method="POST" action="{{ route('payment.request') }}">
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-body d-flex flex-column flex-md-row align-items-center justify-content-between text-center text-md-start">
                        <div class="mb-2 mb-md-0">
                            <small class="text-muted d-block">
                                Cari Bakiye
                            </small>
                            <h4 class="mb-0 fw-bold text-success">
                                {{ number_format($account_balance, 2) }} ₺
                            </h4>
                        </div>
                        <div class="badge bg-success-subtle text-success px-3 py-2 text-center">
                            <div class="fw-semibold">
                                {{ $account_name }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-12 col-lg-5 order-1 order-lg-2">
                        <div class="cc-wrap" data-side="front">
                            <svg class="cc cc-front" viewBox="0 0 420 260" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Kredi kartı">
                                <defs>
                                    <linearGradient id="cc-gradient" x1="0" y1="0" x2="1" y2="1">
                                        <stop offset="0" stop-color="#111827"/>
                                        <stop offset="1" stop-color="#374151"/>
                                    </linearGradient>
                                </defs>

                                <rect x="0" y="0" width="420" height="260" rx="18" fill="url(#cc-gradient)"/>

                                <image id="cc-brand-logo" href="" x="300" y="30" width="90" height="50" preserveAspectRatio="xMidYMid meet" style="display:none"/>

                                <rect x="40" y="70" width="64" height="58" rx="8" fill="#fbbf24" opacity="0.9"/>
                                <rect x="48" y="78" width="48" height="10" rx="5" fill="#111827" opacity="0.25"/>
                                <rect x="48" y="94" width="48" height="10" rx="5" fill="#111827" opacity="0.18"/>
                                <rect x="48" y="110" width="48" height="10" rx="5" fill="#111827" opacity="0.12"/>

                                <text id="cc-number" x="40" y="165" fill="#ffffff" font-size="22" letter-spacing="2" font-family="ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace">
                                    •••• •••• •••• ••••
                                </text>

                                <text x="40" y="205" fill="#e5e7eb" font-size="12" font-family="system-ui, -apple-system, Segoe UI, Roboto">
                                    KART SAHİBİ
                                </text>
                                <text id="cc-name" x="40" y="230" fill="#ffffff" font-size="16" font-family="system-ui, -apple-system, Segoe UI, Roboto">
                                    AD SOYAD
                                </text>
                                <text x="300" y="205" fill="#e5e7eb" font-size="12" font-family="system-ui, -apple-system, Segoe UI, Roboto">
                                    SON KULLANMA
                                </text>
                                <text id="cc-exp" x="300" y="230" fill="#ffffff" font-size="16" font-family="ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Courier New', monospace">
                                    MM/YY
                                </text>
                            </svg>

                            <svg class="cc cc-back" viewBox="0 0 420 260" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Kredi kartı arka yüz">

                                <rect x="0" y="0" width="420" height="260" rx="18" fill="#111827"/>
                                <rect x="0" y="30" width="420" height="52" fill="#020617"/>
                                <rect x="40" y="120" width="340" height="40" rx="6" fill="#e5e7eb"/>
                                <text id="cc-cvc" x="360" y="147" text-anchor="end" fill="#111827" font-size="16" font-family="ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Courier New', monospace">
                                    •••
                                </text>
                                <text x="40" y="190" fill="#9ca3af" font-size="12" font-family="system-ui, -apple-system, Segoe UI, Roboto">
                                    CVC
                                </text>
                            </svg>
                        </div>
                    </div>
                    <div class="col-12 col-lg-7 order-2 order-lg-1">
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <div class="form-floating mb-lg-4 mb-4 theme-form-floating">
                                    <select class="form-select theme-form-select" name="bank_integration_id" id="bank_integration_id" placeholder="{{ trans('translations.payment.page.banka') }}">
                                        <option value="" hidden selected>{{ trans('translations.payment.page.sec') }}</option>
                                        @foreach ($bankIntegrations as $bankIntegration)
                                            <option value="{{ $bankIntegration->id }}" data-logo="{{ $bankIntegration->final_logo_path }}">{{ $bankIntegration->name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="bank_integration_id">{{ trans('translations.payment.page.banka') }}</label>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6">
                                <div class="form-floating mb-lg-4 mb-4 theme-form-floating">
                                    <input type="text" class="form-control" name="amount" id="amount" data-format="price">
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
                        <div class="row">
                            <div class="col-sm-12 col-md-6">
                                <div class="form-check ps-0 mb-4 remember-box">
                                    <input class="checkbox_animated check-box" type="checkbox" name="option_3d_payment" id="option_3d_payment" checked disabled>
                                    <label class="form-check-label" for="option_3d_payment">{{ trans('translations.payment.page.3d_odeme') }}</label>
                                    <input type="hidden" name="option_3d_payment_hidden" id="option_3d_payment_hidden" value="1">
                                </div>
                            </div>
                        </div>
                        <div class="installment-box">
                            <div class="installment-title">
                                <h4>
                                    {{ trans('translations.payment.page.taksit_secenekleri') }}
                                    <a href="javascript:;" class="ms-auto" onclick="$('.installment-table').modal('show');"><span class="badge alert-success"><small>{{ trans('translations.payment.page.taksit_tablosu_goruntule') }}</small></span></a>
                                </h4>
                            </div>
                            <div class="installment-detail">
                                <div class="row g-4" data-js="list-installment">
                                    <small>{{ trans('translations.payment.page.kart_bilgilerini_girdiginizde_taksit_seceneklerini_listelenecektir') }}</small>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-animation w-100 mt-3" id="form-button">{{ trans('translations.payment.page.ode') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('js')
    <script src="{{ mix('js/frontend/modules/homepage/index.js') }}"></script>
    <script src="/assets/js/jquery.validate.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Homepage.init();

            function autoTab(el, nextEl, maxDigits) {
                el.addEventListener('input', () => {
                    let value = el.inputmask
                        ? el.inputmask.unmaskedvalue()
                        : el.value;

                    if (value.length >= maxDigits) {
                        nextEl.focus();
                    }
                });
            }

            const ccNumber = document.getElementById('credit_card_number');
            const expDate = document.getElementById('credit_card_exp_date');
            const cvc = document.getElementById('cvc');
            const phone = document.getElementById('phone_number');
            const explanation = document.getElementById('explanation');

            autoTab(ccNumber, expDate, 19);
            autoTab(expDate, cvc, 5);
            cvc.addEventListener('input', () => {
                if (cvc.value.length >= 3) {
                    setTimeout(() => phone.focus(), 0);
                }
            });
            autoTab(phone, explanation, 10);
        });

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
                        phone_number: {
                            required: false,
                            phoneWithMaskFormat: true
                        },
                        installment_id: {
                            required: true
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
                        phone_number: {
                            required: '{{ trans('translations.payment.page.bu_alani_bos_birakmayiniz') }}'
                        },
                        installment_id: {
                            required: '{{ trans('translations.payment.page.taksit_seciniz') }}'
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

        $('input[name="amount"], select[name="bank_integration_id"], input[name="credit_card_number"]').on('input', debounce(function () {
            var formButton = $('#form-button');
            formButton.prop('disabled', true).addClass('btn-loading');

            var amount = $('input[name="amount"]').val();
            var bank_integration_id = $('select[name="bank_integration_id"]').val();
            var credit_card_number = $('input[name="credit_card_number"]').val().replace(' ', '');

            if (amount != '' && bank_integration_id != '' && credit_card_number != '' && credit_card_number.length >= 8) {
                $.post('{{ route('payments.list-installment') }}', {
                    amount: amount,
                    bank_integration_id: bank_integration_id,
                    credit_card_number: credit_card_number
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

        $('select[name="bank_integration_id"]').change(function() {
            $('#option_3d_payment').prop('checked', true).prop('disabled', true);
            $('#option_3d_payment_hidden').val('1');
            // if($(this).val() !== '1'){
            //     $('#option_3d_payment').prop('checked', true).prop('disabled', true);
            //     $('#option_3d_payment_hidden').val('1');
            // } else {
            //     $('#option_3d_payment').prop('checked', true).prop('disabled', false);
            //     $('#option_3d_payment_hidden').val('1');
            // }
        });

        $('#option_3d_payment').change(function() {
            if (!$(this).prop('checked')) {
                $('#option_3d_payment_hidden').val('0');
            } else {
                $('#option_3d_payment_hidden').val('1');
            }
        });

        $('#bank_integration_id').select2({
            placeholder: '{{ trans('translations.payment.page.sec') }}',
            allowClear: true,
            templateResult: function (bank) {
                if (!bank.id) return bank.text;
                return $(
                    `<span><img src="${$(bank.element).data('logo')}" width="24" style="margin-right:5px;">${bank.text}</span>`
                );
            },
            templateSelection: function (bank) {
                if (!bank.id) return bank.text;
                return $(
                    `<span><img src="${$(bank.element).data('logo')}" width="20" style="margin-right:5px;">${bank.text}</span>`
                );
            }
        });
    </script>
    <script>
        (function () {
          const elNumber = document.getElementById('credit_card_number');
          const elName = document.getElementById('credit_card_name');
          const elExp = document.getElementById('credit_card_exp_date');
          const elCvc = document.getElementById('cvc');

          const ccWrap = document.querySelector('.cc-wrap');
          const ccNumber = document.getElementById('cc-number');
          const ccName = document.getElementById('cc-name');
          const ccExp = document.getElementById('cc-exp');
          const ccCvc = document.getElementById('cc-cvc');
          const ccLogo = document.getElementById('cc-brand-logo');

          function digits(v) {
            return (v || '').replace(/\D/g, '');
          }

          function detectBrand(num) {
            if (/^4/.test(num)) return 'visa';
            if (/^(5[1-5]|2(2[2-9][1-9]|2[3-9]\d|[3-6]\d{2}|7[01]\d|720))/.test(num)) return 'mastercard';
            if (/^9792/.test(num)) return 'troy';
            if (/^3[47]/.test(num)) return 'amex';
            if (/^(5[06789]|6\d)/.test(num)) return 'maestro';
            if (/^62/.test(num)) return 'unionpay';
            return null;
          }

          function setBrandLogo(num) {
            const brand = detectBrand(num);
            if (!brand) {
              ccLogo.style.display = 'none';
              ccLogo.setAttribute('href', '');
              return;
            }

            ccLogo.setAttribute('href', `/asey/images/card-brands/${brand}.svg`);
            ccLogo.style.display = 'block';
          }

          function formatCardNumber(v) {
            const d = digits(v).slice(0, 16);
            return d
              ? d.replace(/(.{4})/g, '$1 ').trim()
              : '•••• •••• •••• ••••';
          }

          function formatExp(v) {
            const d = digits(v).slice(0, 4);
            if (d.length <= 2) return d;
            return d.slice(0, 2) + '/' + d.slice(2);
          }

          function bind() {
            const num = digits(elNumber.value);
            ccNumber.textContent = formatCardNumber(elNumber.value);
            setBrandLogo(num);

            ccName.textContent = elName.value
              ? elName.value.toUpperCase()
              : 'AD SOYAD';

            ccExp.textContent = formatExp(elExp.value) || 'MM/YY';
            ccCvc.textContent = digits(elCvc.value) || '•••';
          }

          // EVENTS
          elNumber.addEventListener('input', e => {
            e.target.value = formatCardNumber(e.target.value);
            bind();
          });

          elExp.addEventListener('input', e => {
            e.target.value = formatExp(e.target.value);
            bind();
          });

          elCvc.addEventListener('input', e => {
            e.target.value = digits(e.target.value).slice(0, 4);
            bind();
          });

          elName.addEventListener('input', bind);

          // CVC focus → kartı çevir
          elCvc.addEventListener('focus', () => ccWrap.dataset.side = 'back');
          elCvc.addEventListener('blur',  () => ccWrap.dataset.side = 'front');

          bind();
        })();
    </script>
@endsection
