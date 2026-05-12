@extends('backend.layouts.app')

@section('title', 'Düzenle')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Ayarlar'],
            ['url' => route('admin.settings.currencies.index'), 'label' => 'Döviz Ayarları'],
            ['url' => route('admin.settings.currencies.edit', $currency->id), 'label' => 'Düzenle']
        ]">
        <li class="nav-item">
            <button type="submit" class="btn btn-success dash-btn mr-2" form="currency-form" data-ajax-submit>
                <i class="las la-save"></i> Kaydet
            </button>
            <a href="{{ route('admin.settings.currencies.index') }}" class="btn btn-info dash-btn">
                <i class="las la-list"></i> Listeye Dön
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing switch-outer-container">
            <div class="col-12 col-xl-9 layout-spacing">
                <form action="{{ route('admin.settings.currencies.update', [$currency->id]) }}" method="POST" id="currency-form" data-ajax-form>
                    @csrf
                    @method('PATCH')
                    <div class="statbox widget box box-shadow mb-4">
                        <div class="widget-header">
                            <h4>Düzenle: "{{ $currency->code }}"</h4>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label>Alış Fiyatı <span class="text-danger">*</span></label>
                                        <p>{{ $currency->buy }}</p>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label>Satış Fiyatı <span class="text-danger">*</span></label>
                                        <p>{{ $currency->sell }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="symbol" label="Sembol" type="text" :value="$currency->symbol" :required="true"/>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="manual_override">Manuel Kur Kullan <span class="text-danger">*</span></label>
                                        <div>
                                            <span class="switch">
                                                <label>
                                                    <input type="checkbox" id="manual_override" name="manual_override" {{ $currency->manual_override ? 'checked' : '' }}>
                                                    <span></span>
                                                </label>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="manualRates" style="{{ $currency->manual_override ? '' : 'display:none;' }}">
                                <div class="row">
                                    <div class="col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <x-backend.input id="manual_buy" type="text" label="Alış Fiyatı (Manuel)" :required="true" :value="$currency->manual_buy" data-format="price" />
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <x-backend.input id="manual_sell" type="text" label="Satış Fiyatı (Manuel)" :required="true" :value="$currency->manual_sell" data-format="price"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-12 col-xl-3 layout-spacing">
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-header">
                        <h4>Durumu</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="w-100">
                            <div class="form-group row">
                                <div class="col-3">
                                    <span class="switch align-items-start">
                                        <label>
                                            <input type="checkbox" name="status" form="currency-form" {{ $currency->status == 1 ? 'checked' : '' }}>
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
    </div>
@endsection

@section('js')
    <script>
        document.getElementById('manual_override').addEventListener('change', function() {
            const manualRates = document.getElementById('manualRates');
            manualRates.style.display = this.checked ? '' : 'none';
        });
    </script>
@endsection
