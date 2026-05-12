@extends('frontend.layouts.app')

@section('css')
    <style>
        .editing-row {
            background-color: #0f5132;
            color: #fff;
        }
    </style>
@endsection

@section('content')
    <section class="section-b-space">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-sm-12 col-md-6 mb-3">
                    <div class="title">
                        <h2>{{ trans('translations.collections.promissories.yeni_senet') }}</h2>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-3">
                    <div class="text-md-end">
                        <a href="{{ route('collections.promissories.index') }}" class="text-danger">{{ trans('translations.collections.promissories.iptal') }}</a>
                    </div>
                </div>
            </div>
            <div
                data-js="promissories-create-config"
                data-create-title="{{ trans('translations.collections.promissories.senet_olustur') }}"
                data-edit-title="{{ trans('translations.collections.promissories.senet_duzenle') }}"
                data-required-fields-message="{{ trans('translations.collections.promissories.lutfen_gerekli_alanlari_doldurunuz') }}"
                data-processing-text="{{ trans('translations.collections.promissories.isleminiz_yapiliyor_lutfen_bekleyin') }}"
                data-request-error="{{ trans('translations.istek_sirasinda_bir_hata_olustu_lutfen_daha_sonra_tekrar_deneyiniz') }}"
                data-save-url="{{ route('collections.promissories.store') }}"
                data-current-account-name="{{ $currentAccountService->currentAccount()->name }}"
                data-note-prefix="Senet"
                hidden
            ></div>
            <div class="row">
                <div class="col-12 col-sm-6 col-xl-3 mb-3">
                    <label for="collection_date">{{ trans('translations.collections.promissories.tarih') }}&nbsp;<span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="collection_date" id="collection_date" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-12 col-xl-9 mb-3">
                    <label for="notes">{{ trans('translations.collections.promissories.aciklama') }}</label>
                    <textarea class="form-control" name="notes" id="notes" rows="1"></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="title">
                        <a href="javascript:;" class="text-danger" data-js="promissory-modal">{{ trans('translations.collections.promissories.senet_olustur') }}</a>
                    </div>
                </div>
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table" id="promissory-table">
                            <thead>
                                <tr>
                                    <th>{{ trans('translations.collections.promissories.seri_no') }}</th>
                                    <th>{{ trans('translations.collections.promissories.vade') }}</th>
                                    <th>{{ trans('translations.collections.promissories.clio_tipi') }}</th>
                                    <th>{{ trans('translations.collections.promissories.borclu_adi') }}</th>
                                    <th>{{ trans('translations.collections.promissories.tutar') }}</th>
                                    <th>{{ trans('translations.collections.promissories.doviz_turu') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="col-12" id="save-button" style="display: none;">
                    <button type="button" class="btn btn-md mt-3 theme-bg-color text-white" data-js="save-button">{{ trans('translations.collections.promissories.kaydet') }}</button>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ mix('js/frontend/modules/collections/promissories-create.js') }}"></script>
@endpush


