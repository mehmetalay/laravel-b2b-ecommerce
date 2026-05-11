@extends('layouts.checkout')

@section('content')
    <section class="section-b-space">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="title">
                        <h2>{{ trans('translations.contract.sozlesme_onay') }}</h2>
                        @if (request()->get('approve'))
                            <p>
                                {{ trans('translations.contract.lutfen_asagi_kaydirarak_tamamini_okuyun') }}
                            </p>
                        @else
                            <p>
                                {{ trans('translations.contract.lutfen_asagidaki_bilgileri_doldurunuz_veya_guncelleyiniz') }}
                            </p>
                            <small class="text-danger">( * ) olanlar zorunlu alanlardır.</small>
                        @endif
                    </div>
                </div>

                @if (request()->get('approve'))
                    <div class="row">
                        <div id="terms">
                            {!! $content !!}
                        </div>
                        <div id="acceptButton"></div>
                    </div>
                @else
                    <form class="row mt-4"
                        action="{{ route('contract.store', ['actor_type' => $actor_type, 'actor_id' => $actor_id, 'template' => $template->id]) }}"
                        method="POST" id="contract-form">
                        @csrf
                        <div class="col-sm-12 col-md-6 mb-3">
                            <label for="customer_invoice_title">{{ trans('translations.contract.musteri_fatura_unvani') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-sm" name="customer_invoice_title"
                                id="customer_invoice_title" value="{{ $data['customer_invoice_title'] }}">
                        </div>
                        <div class="col-sm-12 col-md-6 mb-3">
                            <label for="customer_invoice_address">{{ trans('translations.contract.musteri_fatura_adresi') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-sm" name="customer_invoice_address"
                                id="customer_invoice_address" value="{{ $data['customer_invoice_address'] }}">
                        </div>
                        <div class="col-sm-12 col-md-6 mb-3">
                            <label for="phone">{{ trans('translations.contract.telefon') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-sm" name="phone" id="phone"
                                value="{{ $data['phone'] }}" data-mask-phone>
                        </div>
                        <div class="col-sm-12 col-md-6 mb-3">
                            <label for="fax">{{ trans('translations.contract.faks') }}</label>
                            <input type="text" class="form-control form-control-sm" name="fax" id="fax"
                                value="{{ $data['fax'] }}" data-mask-phone>
                        </div>
                        <div class="col-sm-12 col-md-6 mb-3">
                            <label for="trade_registry_no">{{ trans('translations.contract.ticaret_sicil_no') }}</label>
                            <input type="text" class="form-control form-control-sm" name="trade_registry_no"
                                id="trade_registry_no" value="{{ $data['trade_registry_no'] }}">
                        </div>
                        <div class="col-sm-12 col-md-6 mb-3">
                            <label for="tax_office">{{ trans('translations.contract.vergi_dairesi') }}</label>
                            <input type="text" class="form-control form-control-sm" name="tax_office" id="tax_office"
                                value="{{ $data['tax_office'] }}">
                        </div>
                        <div class="col-sm-12 col-md-6 mb-3">
                            <label for="tax_number">{{ trans('translations.contract.vergi_no') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-sm" name="tax_number" id="tax_number"
                                value="{{ $data['tax_number'] }}">
                        </div>
                        <div class="col-sm-12 col-md-6 mb-3">
                            <label for="company_official">{{ trans('translations.contract.firma_yetkilisi') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-sm" name="company_official"
                                id="company_official" value="{{ $data['company_official'] }}">
                        </div>
                        <div class="col-sm-12 col-md-6 mb-3">
                            <label for="mobile_phone">{{ trans('translations.contract.gsm_no') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-sm" name="mobile_phone" id="mobile_phone"
                                value="{{ $data['mobile_phone'] }}" data-mask-phone>
                            <small
                                class="text-danger">{{ trans('translations.contract.not_bu_numaraya_sms_ile_onay_kodu_gonderilecektir') }}</small>
                        </div>
                        <div class="col-sm-12 col-md-6 mb-3">
                            <label for="email_address">{{ trans('translations.contract.mail_adresi') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-sm" name="email_address"
                                id="email_address" value="{{ $data['email_address'] }}">
                            <small
                                class="text-danger">{{ trans('translations.contract.not_sozlesme_bu_e_posta_adresine_gonderilecektir') }}</small>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-sm-12 col-md-6 mb-3">
                                <label
                                    for="purchasing_officer">{{ trans('translations.contract.satinalma_yetkilisi') }}</label>
                                <input type="text" class="form-control form-control-sm" name="purchasing_officer"
                                    id="purchasing_officer" value="{{ $data['purchasing_officer'] }}">
                            </div>
                            <div class="col-sm-12 col-md-6 mb-3">
                                <label
                                    for="purchase_mobile_phone">{{ trans('translations.contract.satinalma_gsm_no') }}</label>
                                <input type="text" class="form-control form-control-sm" name="purchase_mobile_phone"
                                    id="purchase_mobile_phone" value="{{ $data['purchase_mobile_phone'] }}"
                                    data-mask-phone>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-3">
                                <label
                                    for="purchase_email_address">{{ trans('translations.contract.satinalma_mail_adresi') }}</label>
                                <input type="text" class="form-control form-control-sm" name="purchase_email_address"
                                    id="purchase_email_address" value="{{ $data['purchase_email_address'] }}">
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-sm-12 col-md-6 mb-3">
                                <label
                                    for="payment_authority">{{ trans('translations.contract.odeme_yetkilisi') }}</label>
                                <input type="text" class="form-control form-control-sm" name="payment_authority"
                                    id="payment_authority" value="{{ $data['payment_authority'] }}">
                            </div>
                            <div class="col-sm-12 col-md-6 mb-3">
                                <label
                                    for="payment_authority_mobile_phone">{{ trans('translations.contract.odeme_yetkilisi_gsm_no') }}</label>
                                <input type="text" class="form-control form-control-sm"
                                    name="payment_authority_mobile_phone" id="payment_authority_mobile_phone"
                                    value="{{ $data['payment_authority_mobile_phone'] }}" data-mask-phone>
                            </div>
                            <div class="col-sm-12 col-md-6 mb-3">
                                <label
                                    for="payment_authority_email_address">{{ trans('translations.contract.odeme_yetkilisi_mail_adresi') }}</label>
                                <input type="text" class="form-control form-control-sm"
                                    name="payment_authority_email_address" id="payment_authority_email_address"
                                    value="{{ $data['payment_authority_email_address'] }}">
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-sm-12 col-md-6 mb-3">
                                <label
                                    for="accounting_contact_name">{{ trans('translations.contract.muhasebe_yetkilisi') }}</label>
                                <input type="text" class="form-control form-control-sm" name="accounting_contact_name"
                                    id="accounting_contact_name" value="{{ $data['accounting_contact_name'] }}">
                            </div>

                            <div class="col-sm-12 col-md-6 mb-3">
                                <label
                                    for="accounting_gsm">{{ trans('translations.contract.muhasebe_yetkilisi_gsm_no') }}</label>
                                <input type="text" class="form-control form-control-sm" name="accounting_gsm"
                                    id="accounting_gsm" value="{{ $data['accounting_gsm'] }}" data-mask-phone>
                            </div>

                            <div class="col-sm-12 col-md-6 mb-3">
                                <label
                                    for="accounting_email">{{ trans('translations.contract.muhasebe_yetkilisi_e_posta_adresi') }}</label>
                                <input type="text" class="form-control form-control-sm" name="accounting_email"
                                    id="accounting_email" value="{{ $data['accounting_email'] }}">
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-sm-12 col-md-6 mb-3">
                                <label
                                    for="monthly_payment_days">{{ trans('translations.contract.aylik_odeme_gunleri') }}</label>
                                <input type="text" class="form-control form-control-sm" name="monthly_payment_days"
                                    id="monthly_payment_days" value="{{ $data['monthly_payment_days'] }}">
                            </div>
                        </div>

                        <hr>

                        <h4 class="mt-3 mb-2 theme-color">BANKA HESAP BİLGİLERİNİZ</h4>

                        @for ($i = 0; $i < 5; $i++)
                            @php
                                $row = $bankAccounts[$i] ?? null;

                                $bankName = old("bank_accounts.$i.bank_name", $row->bank_name ?? '');
                                $branch = old("bank_accounts.$i.branch", $row->branch ?? '');
                                $accountNo = old("bank_accounts.$i.account_no", $row->account_no ?? '');
                                $holder = old("bank_accounts.$i.account_holder", $row->account_holder ?? '');
                            @endphp

                            <div class="row p-2 mb-2">
                                <div class="col-md-3 mb-2">
                                    <label>Banka</label>
                                    <input class="form-control form-control-sm"
                                        name="bank_accounts[{{ $i }}][bank_name]"
                                        value="{{ $bankName }}">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label>Şube</label>
                                    <input class="form-control form-control-sm"
                                        name="bank_accounts[{{ $i }}][branch]" value="{{ $branch }}">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label>Hesap No</label>
                                    <input class="form-control form-control-sm"
                                        name="bank_accounts[{{ $i }}][account_no]"
                                        value="{{ $accountNo }}">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label>Hesap Sahibi</label>
                                    <input class="form-control form-control-sm"
                                        name="bank_accounts[{{ $i }}][account_holder]"
                                        value="{{ $holder }}">
                                </div>
                            </div>

                            @if ($i < 4)
                                <hr class="theme-bg-color">
                            @endif
                        @endfor

                        <hr>

                        <h4 class="mt-3 mb-2 theme-color">MÜŞTERİ İLETİŞİM BİLGİLERİ KAYIT FORMU</h4>

                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mt-2 mb-2">Elektronik Posta Adresleri</h6>
                                @for ($i = 0; $i < 5; $i++)
                                    @php
                                        $row = $emails[$i] ?? null;
                                        $val = old("emails.$i.email", $row->email ?? '');
                                    @endphp
                                    <div class="mb-2">
                                        <input class="form-control form-control-sm"
                                            name="emails[{{ $i }}][email]" value="{{ $val }}"
                                            placeholder="E-Posta Adresi {{ $i + 1 }}">
                                    </div>
                                @endfor
                            </div>

                            <div class="col-md-6">
                                <h6 class="mt-2 mb-2">GSM Numaraları</h6>
                                @for ($i = 0; $i < 5; $i++)
                                    @php
                                        $row = $gsms[$i] ?? null;
                                        $val = old("gsms.$i.gsm", $row->gsm ?? '');
                                    @endphp
                                    <div class="mb-2">
                                        <input class="form-control form-control-sm" name="gsms[{{ $i }}][gsm]"
                                            value="{{ $val }}" placeholder="GSM No {{ $i + 1 }}"
                                            data-mask-phone>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <hr>

                        @if ($actor_type == 'dealer')
                            <h4 class="mt-3 mb-2 theme-color">MÜŞTERİ SEVK DEPO/MAĞAZA BİLDİRİM FORMU</h4>

                            @for ($i = 0; $i < 5; $i++)
                                @php
                                    $row = $shipLocations[$i] ?? null;

                                    $name = old("ship_locations.$i.name", $row->name ?? '');
                                    $addr = old("ship_locations.$i.address", $row->address ?? '');
                                    $city = old("ship_locations.$i.city", $row->city ?? '');
                                    $dist = old("ship_locations.$i.district", $row->district ?? '');
                                    $phone = old("ship_locations.$i.phone", $row->phone ?? '');
                                    $fax = old("ship_locations.$i.fax", $row->fax ?? '');
                                    $auth = old("ship_locations.$i.authorized_name", $row->authorized_name ?? '');
                                @endphp

                                <div class="row p-2 mb-2">
                                    <div class="col-md-6 mb-2">
                                        <label>Depo / Mağaza Adı</label>
                                        <input class="form-control form-control-sm"
                                            name="ship_locations[{{ $i }}][name]" value="{{ $name }}">
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <label>Yetkili Ad Soyadı</label>
                                        <input class="form-control form-control-sm"
                                            name="ship_locations[{{ $i }}][authorized_name]"
                                            value="{{ $auth }}">
                                    </div>

                                    <div class="col-md-3 mb-2">
                                        <label>Tel</label>
                                        <input class="form-control form-control-sm"
                                            name="ship_locations[{{ $i }}][phone]" value="{{ $phone }}"
                                            data-mask-phone>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label>Fax</label>
                                        <input class="form-control form-control-sm"
                                            name="ship_locations[{{ $i }}][fax]" value="{{ $fax }}"
                                            data-mask-phone>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label>İl</label>
                                        <input class="form-control form-control-sm"
                                            name="ship_locations[{{ $i }}][city]" value="{{ $city }}">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label>İlçe</label>
                                        <input class="form-control form-control-sm"
                                            name="ship_locations[{{ $i }}][district]"
                                            value="{{ $dist }}">
                                    </div>

                                    <div class="col-12 mb-2">
                                        <label>Adres</label>
                                        <input class="form-control form-control-sm"
                                            name="ship_locations[{{ $i }}][address]" value="{{ $addr }}">
                                    </div>
                                </div>

                                @if ($i < 4)
                                    <hr class="theme-bg-color">
                                @endif
                            @endfor

                            <hr>
                        @endif

                        <div class="row">
                            <div class="col-12 mb-3">
                                <button type="submit"
                                    class="btn theme-bg-color btn-md fw-bold mt-4 text-white float-end">{{ trans('translations.contract.kaydet') }}</button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection

@section('css')
    <style>
        #terms {
            height: 400px;
            width: 100%;
            overflow-y: scroll;
            border: 1px solid #ccc;
            margin-bottom: 10px;
            padding: 10px;
        }

        button:disabled {
            background-color: #ccc;
        }
    </style>
@endsection

@if (false)
    <script>
        $('body').on('submit', '#contract-form', function(e) {
            e.preventDefault();
            var el = $(this).find('button');
            var htmlButton = el.html();
            el.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>&nbsp;{{ trans('translations.contract.isleminiz_yapiliyor_lutfen_bekleyin') }}'
            );
            $.post($(this).attr('action'), $(this).serialize(), function(data) {
                    if (data.success) {
                        location.href = data.success;
                    } else {
                        el.html(htmlButton).prop('disabled', false);
                        var message = data.warning ? data.warning : data.error;
                        toastr_n(message, (data.warning ? 'warning' : 'error'));
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    el.html(htmlButton).prop('disabled', false);
                    toastr_n(
                        '{{ trans('translations.contract.istek_sirasinda_bir_hata_olustu_lutfen_daha_sonra_tekrar_deneyiniz') }}',
                        'error');
                });
        });
        document.addEventListener("DOMContentLoaded", function() {
            const terms = document.getElementById('terms');
            const acceptButton = document.getElementById('acceptButton');
            const url =
                '{{ route('contract.accept-button', ['actor_type' => $actor_type, 'actor_id' => $actor_id, 'template' => $template->id]) }}';

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        $.post(url)
                            .done(function(data) {
                                acceptButton.innerHTML = data;
                            })
                            .fail(function(xhr, status, error) {
                                console.error("Hata oluştu:", status, error);
                            });
                    }
                });
            }, {
                threshold: 1.0
            });

            const endMarker = document.createElement('span');
            terms.appendChild(endMarker);

            observer.observe(endMarker);
        });
        $(document).on('click', '[data-js=approve-contract]', function(e) {
            e.preventDefault();
            var $this = $(this);
            var htmlButton = $this.html();
            $this.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> {{ trans('translations.contract.onaylaniyor_lutfen_bekleyiniz') }}'
            );
            var url = $(this).data('url');
            $.post(url, function(data) {
                    if (data.success) {
                        $('#smsCode').modal('show');
                        $this.prop('disabled', false).html(htmlButton);
                    } else {
                        toastr.error('{{ trans('translations.contract.sms_gonderilirken_bir_hata_olustu') }}');
                        $this.prop('disabled', false).html(htmlButton);
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    toastr_n(
                        '{{ trans('translations.contract.istek_sirasinda_bir_hata_olustu_lutfen_daha_sonra_tekrar_deneyiniz') }}',
                        'error');
                    $this.prop('disabled', false).html(htmlButton);
                });
        });
        $(document).on('click', '[data-js=verify-sms-code]', function(e) {
            e.preventDefault();
            var $this = $(this);
            var htmlButton = $this.html();
            $this.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> {{ trans('translations.contract.kontrol_ediliyor_lutfen_bekleyiniz') }}'
            );
            var url = $(this).data('url');
            $.post(url, $('#smsCodeForm').serialize(), function(data) {
                    if (data.success) {
                        $('#smsCode').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: '{{ trans('translations.login_controller.sozlesme_onayi') }}',
                            text: data.success,
                            timer: 5000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                            willClose: () => {
                                window.location.href = data.route;
                            }
                        });
                    } else {
                        toastr.error(data.error);
                        $this.prop('disabled', false).html(htmlButton);
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    toastr_n(
                        '{{ trans('translations.contract.istek_sirasinda_bir_hata_olustu_lutfen_daha_sonra_tekrar_deneyiniz') }}',
                        'error');
                    $this.prop('disabled', false).html(htmlButton);
                });
        });
    </script>
@endif

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const qs = (sel, root = document) => root.querySelector(sel);
            const toUrlEncodedBody = (formElement) => {
                const body = new URLSearchParams();
                const formData = new FormData(formElement);

                for (const [k, v] of formData.entries()) {
                    body.append(k, v);
                }

                return body;
            };
            const notifyErrorWithFallback = (res, fallbackMessage) => {
                notify('error', res?.message || fallbackMessage);
            };
            const getBootstrapModalInstance = (modalElement) => {
                if (!modalElement || !window.bootstrap?.Modal) {
                    return null;
                }

                return bootstrap.Modal.getOrCreateInstance(modalElement);
            };

            const contractForm = qs('#contract-form');
            if (contractForm) {
                contractForm.addEventListener('submit', (e) => {
                    e.preventDefault();

                    clearFormErrors(contractForm);

                    let button = contractForm.querySelector('button[type="submit"]');
                    const body = toUrlEncodedBody(contractForm);

                    setLoading(button, true);

                    axiosRequest.post(contractForm.action, body, {
                        onSuccess: (res) => {
                            const redirect = res?.redirect;
                            if (redirect) window.location.href = redirect;
                        },
                        onValidationError: (errors) => {
                            handleFormValidationErrors(errors, contractForm);
                        },
                        onError: (res) => {
                            notifyErrorWithFallback(res, 'İşlem başarısız');
                        },
                        onComplete: () => {
                            setLoading(button, false);
                        }
                    });
                });
            }

            const terms = qs('#terms');
            const acceptButton = qs('#acceptButton');

            if (terms && acceptButton) {
                const url =
                    "{{ route('contract.accept-button', ['actor_type' => $actor_type, 'actor_id' => $actor_id, 'template' => $template->id]) }}";

                const endMarker = document.createElement('span');
                terms.appendChild(endMarker);

                const observer = new IntersectionObserver((entries) => {
                    for (const entry of entries) {
                        if (!entry.isIntersecting) continue;

                        observer.disconnect();

                        axiosRequest.post(url, {}, {
                            onSuccess: (res) => {
                                acceptButton.innerHTML = res?.html || '';
                            },
                            onError: (res) => {
                                console.error('acceptButton load error:', res);
                                notifyErrorWithFallback(res, 'Buton yüklenemedi');
                            }
                        });

                        break;
                    }
                }, {
                    threshold: 1.0
                });

                observer.observe(endMarker);
            }

            document.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-js="approve-contract"]');
                if (!btn) return;

                e.preventDefault();

                const url = btn.getAttribute('data-url');
                if (!url) return;

                setLoading(btn, true);

                axiosRequest.post(url, {}, {
                    onSuccess: (res) => {
                        notify('success', res?.message || 'Onay kodu gönderildi');

                        const modalEl = document.getElementById('smsCode');
                        const modal = getBootstrapModalInstance(modalEl);
                        if (modal) {
                            modal.show();
                        } else {
                            modalEl?.classList.add('show');
                        }
                    },
                    onError: (res) => {
                        notifyErrorWithFallback(res, 'SMS gönderilirken hata oluştu');
                    },
                    onComplete: () => {
                        setLoading(btn, false);
                    }
                });
            });

            document.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-js="verify-sms-code"]');
                if (!btn) return;

                e.preventDefault();

                const url = btn.getAttribute('data-url');
                if (!url) return;

                const form = document.getElementById('smsCodeForm');
                if (!form) return;

                clearFormErrors(form);

                setLoading(btn, true);
                const body = toUrlEncodedBody(form);

                axiosRequest.post(url, body, {
                    onSuccess: (res) => {
                        const msg = res?.message || 'Onaylandı';
                        const redirect = res?.redirect;

                        const modalEl = document.getElementById('smsCode');
                        const modal = getBootstrapModalInstance(modalEl);
                        if (modal) {
                            modal.hide();
                        }

                        if (window.Swal?.fire) {
                            Swal.fire({
                                icon: 'success',
                                title: "{{ trans('translations.login_controller.sozlesme_onayi') }}",
                                text: msg,
                                timer: 3500,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                willClose: () => {
                                    if (redirect) window.location.href = redirect;
                                }
                            });
                        } else {
                            notify('success', msg);
                            if (redirect) setTimeout(() => window.location.href = redirect,
                            800);
                        }
                    },
                    onValidationError: (errors) => {
                        handleFormValidationErrors(errors, form);
                    },
                    onError: (res) => {
                        notifyErrorWithFallback(res, 'Kod doğrulanamadı');
                    },
                    onComplete: () => {
                        setLoading(btn, false);
                    }
                });
            });
        });
    </script>
@endsection
