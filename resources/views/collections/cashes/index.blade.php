@extends('frontend.layouts.app')

@section('content')
    <section class="section-b-space">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="title">
                        <h2>{{ trans('translations.collections.cashes.nakit_tahsilatlari') }}</h2>
                    </div>
                    <div
                        data-js="cashes-index-config"
                        data-processing-text="{{ trans('translations.collections.cashes.isleminiz_yapiliyor_lutfen_bekleyin') }}"
                        data-request-error="{{ trans('translations.istek_sirasinda_bir_hata_olustu_lutfen_daha_sonra_tekrar_deneyiniz') }}"
                        data-empty-row-text="{{ trans('translations.collections.cashes.veri_yok') }}"
                        hidden
                    ></div>
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-4 col-xl-3 mb-3">
                            <a href="javascript:;" data-js="add-edit" data-url="{{ route('collections.cashes.create') }}" data-title="Yeni" class="btn btn-sm theme-bg-color text-white w-50">{{ trans('translations.collections.cashes.yeni') }}</a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ trans('translations.collections.cashes.cari') }}</th>
                                    <th class="text-center">{{ trans('translations.collections.cashes.sira_no') }}</th>
                                    <th class="text-center">{{ trans('translations.collections.cashes.tutar') }}</th>
                                    <th class="text-center">{{ trans('translations.collections.cashes.tarih') }}</th>
                                    <th class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                                @forelse ($cashes as $item)
                                    <tr id="parent-{{ $item->id }}">
                                        <td>{{ $item->user->name }}</td>
                                        <td class="text-center">{{ $item->sequence_number }}</td>
                                        <td class="text-center">{{ number_format($item->amount, 2) . ' ' . $item->currency_type }}</td>
                                        <td class="text-center">{{ format_date_time($item->collection_date) }}</td>
                                        <td>
                                            <a href="javascript:;" data-js="add-edit" data-url="{{ route('collections.cashes.edit', [$item->id]) }}" data-title="{{ trans('translations.collections.cashes.duzenle') }}"><i class="fa-solid fa-edit"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">{{ trans('translations.collections.cashes.veri_yok') }}.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $cashes->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ mix('js/frontend/modules/collections/cashes-index.js') }}"></script>
@endpush


