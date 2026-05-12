@extends('backend.layouts.app')

@section('title', 'Yeni')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => route('admin.payment-links.index'), 'label' => 'Ödeme Linkleri'],
            ['url' => 'javascript:;', 'label' => 'Yeni'],
        ]">
        <li class="nav-item">
            <div class="btn-group mr-2">
                <button type="submit" class="btn btn-success dash-btn data-form-button" name="save_and_close" form="payment-link-form" data-ajax-submit><i class="las la-save"></i> Kaydet</button>
                <button type="button" class="btn btn-success dash-btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="las la-angle-down"></i></button>
                <div class="dropdown-menu">
                    <button type="submit" class="dropdown-item" name="save_and_pay" form="payment-link-form" data-ajax-submit>Kaydet ve Öde</button>
                    <button type="submit" class="dropdown-item" name="save_and_new" form="payment-link-form" data-ajax-submit>Kaydet ve Yeni</button>
                </div>
            </div>
            <a href="{{ route('admin.payment-links.index') }}" class="btn btn-info dash-btn">
                <i class="las la-list"></i> Listeye Dön
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="container layout-px-spacing">
        <div class="row layout-top-spacing switch-outer-container">
            <div class="col-12 col-xl-8 layout-spacing">
                <form action="{{ route('admin.payment-links.store') }}" method="POST" id="payment-link-form" data-ajax-form>
                    @csrf
                    <div class="statbox widget box box-shadow mb-4">
                        <div class="widget-header">
                            <h4>Yeni</h4>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="form-group">
                                <label for="user_id">Müşteri Adı <span class="text-danger">*</span></label>
                                <select name="user_id" id="user_id" class="selectpicker w-100" data-live-search="true" title="Ara.."></select>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="email" label="E-posta Adresi" type="text"/>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="phone" label="Telefon Numarası" type="text"/>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="amount" label="Ödeme Tutarı" type="text" :required="true" data-format="price"/>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="transaction_type">İşlem Tipi <span class="text-danger">*</span></label>
                                        <select class="form-control" name="transaction_type" id="transaction_type" data-js="transaction-type">
                                            <option value="1" selected>Serbest</option>
                                            <option value="2">Taksitsiz İşlem</option>
                                            <option value="3">Manuel</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div id="manual-options" data-js="manual-options" style="display: none;">
                                <div class="row">
                                    <div class="col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="manual_bank_integration_id">Banka <span class="text-danger">*</span></label>
                                            <select class="form-control" name="manual_bank_integration_id" id="manual_bank_integration_id" data-js="manual-bank-integration">
                                                <option value="" selected hidden>SEÇ</option>
                                                @foreach ($bankIntegrations as $bankIntegration)
                                                    <option value="{{ $bankIntegration->id }}">{{ $bankIntegration->full_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="manual_installment">Taksit Sayısı <span class="text-danger">*</span></label>
                                            <select class="form-control" name="manual_installment" id="manual_installment" data-js="manual-installment" data-selected-manual-installment="{{ old('manual_installment', '') }}" disabled>
                                                <option value="" selected hidden>SEÇ</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label for="manual_lock_bank_installment">Banka ve Taksit Bilgilerini Değiştirilemez</label>
                                            <span class="switch align-items-start">
                                                <label>
                                                    <input type="checkbox" name="manual_lock_bank_installment" checked>
                                                    <span></span>
                                                </label>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-12 col-xl-4 layout-spacing">
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-header">
                        <h4>Durumu</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="form-group row">
                            <div class="col-3">
                                <span class="switch align-items-start">
                                    <label>
                                        <input type="checkbox" name="status" checked form="payment-link-form">
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" id="status-label-text">Aktif</label>
                        </div>
                    </div>
                </div>
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-header">
                        <h4>Tutar Değiştirilemez</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="form-group row">
                            <div class="col-3">
                                <span class="switch align-items-start">
                                    <label>
                                        <input type="checkbox" name="amount_locked" checked form="payment-link-form">
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" id="status-label-text">Aktif</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $(document).on('keyup', '.bs-searchbox input', debounce(function() {
                var query = $(this).val().toLocaleUpperCase('tr-TR');

                $(this).val(query);

                if (query.length >= 2) {
                    $.ajax({
                        url: '/ajax/customers',
                        type: 'GET',
                        data: { search: query },
                        dataType: 'json',
                        success: function(data) {
                            var options = data.map(function(user) {
                                return `<option value="${user.id}" data-email="${user.email}" data-phone="${user.phone}">${user.name}</option>`;
                            }).join('');
                            $('#user_id').html(options);
                            $('.selectpicker').selectpicker('refresh');
                        }
                    });
                }
            }, 500));
            $('#user_id').on('changed.bs.select', function() {
                var selectedOption = $(this).find('option:selected');
                var userEmail = selectedOption.data('email');
                var userPhone = selectedOption.data('phone');

                $('#email').val(userEmail);
                $('#phone').val(userPhone);
            });
        });
    </script>
@endsection

