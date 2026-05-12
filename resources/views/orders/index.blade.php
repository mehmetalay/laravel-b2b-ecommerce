@extends('frontend.layouts.app')

@section('css')
    <style>
        .modal-fullscreen {
            width: 90vw;
            margin: 1.75rem auto;
            height: 90%;
        }
    </style>
@endsection

@section('content')
    <section
        class="section-b-space"
        data-js="orders-index-page"
        data-approve-confirm="{!! trans('translations.siparis.durumu_onaylandi_olarak_degistireceksiniz_onayliyor_musunuz') !!}"
        data-approved-badge="{{ trans('translations.siparis.onaylandi') }}"
        data-generic-error="Bir hata oluştu">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="title">
                        <h2>{{ trans('translations.siparis.siparisler') }}</h2>
                    </div>

                    {{-- FİLTRELER --}}
                    @if (auth('web')->check() && auth('web')->user()->role === 'salesman')
                        <form class="row g-3 align-items-end mb-4" method="GET">

                            <div class="col-sm-12 col-md-2">
                                <label for="customerName" class="form-label">{{ trans('translations.siparis.bayi_adi') }}</label>
                                <input type="text" class="form-control" name="customerName" id="customerName" value="{{ request()->get('customerName') }}">
                            </div>

                            <div class="col-sm-12 col-md-3">
                                <label for="startDate" class="form-label">{{ trans('translations.siparis.baslangic_ve_bitis_tarihi') }}</label>
                                <div class="input-group">
                                    <input type="date" class="form-control" name="startDate" id="startDate" value="{{ request()->get('startDate', $startDate) }}">
                                    <span class="input-group-text">-</span>
                                    <input type="date" class="form-control" name="endDate" id="endDate" value="{{ request()->get('endDate', $endDate) }}">
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-2">
                                <label for="status" class="form-label">{{ trans('translations.siparis.siparis_durumu') }}</label>
                                <select class="form-select form-control" name="status" id="status">
                                    <option value="" {{ !request()->get('status') ? 'selected' : '' }} hidden>{{ trans('translations.siparis.hepsi') }}</option>
                                    <option value="1" {{ request()->get('status') == 1 ? 'selected' : '' }}>{{ trans('translations.siparis.onay_bekleniyor') }}</option>
                                    <option value="2" {{ request()->get('status') == 2 ? 'selected' : '' }}>{{ trans('translations.siparis.onaylandi') }}</option>
                                    <option value="3" {{ request()->get('status') == 3 ? 'selected' : '' }}>{{ trans('translations.siparis.hazirlaniyor') }}</option>
                                    <option value="4" {{ request()->get('status') == 4 ? 'selected' : '' }}>{{ trans('translations.siparis.sevk_edildi') }}</option>
                                </select>
                            </div>

                            <div class="col-sm-12 col-md-3">
                                <label class="form-label d-none d-md-block">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn theme-bg-color text-white gap-2">
                                        <i class="fa fa-search"></i> Ara
                                    </button>
                                    @if (request()->query())
                                        <a href="{{ route('orders.index') }}" class="btn btn-danger text-white gap-2">
                                            <i class="fa-solid fa-trash"></i> Filtreleri Temizle
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    @endif

                    {{-- TOPLAMLAR --}}
                    <div class="row justify-content-center mb-4">
                        <div class="col-md-4">
                            <div class="card border-success text-center">
                                <div class="card-body">
                                    <div class="text-muted small mb-1">Toplam (TL)</div>
                                    <div class="fs-4 fw-bold text-success">
                                        {{ number_format($currencyTotals['TL'] ?? 0, 2, ',', '.') }} ₺
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-primary text-center">
                                <div class="card-body">
                                    <div class="text-muted small mb-1">Toplam (USD)</div>
                                    <div class="fs-4 fw-bold text-primary">
                                        $ {{ number_format($currencyTotals['USD'] ?? 0, 2, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- LİSTE --}}
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ trans('translations.siparis.plasiyer') }}</th>
                                    <th>{{ trans('translations.siparis.bayi') }}</th>
                                    <th class="text-center">{{ trans('translations.siparis.toplam_siparis_tutari') }}</th>
                                    <th class="text-center">{{ trans('translations.siparis.siparis_tarihi') }}</th>
                                    <th class="text-center">{{ trans('translations.siparis.siparis_durumu') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $item)
                                    <tr id="order-{{ $item->id }}">
                                        <td>{{ $item->salesman_name }}</td>
                                        <td>
                                            {{ $item->dealer_name }}
                                            @if ($item->subDealer)
                                                <div>
                                                    <small>{{ "Alt Bayi: {$item->sub_dealer_name}" }}</small>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->total_amount }}</td>
                                        <td class="text-center">{{ $item->formatted_created_at }}</td>
                                        <td class="text-center">
                                            @if ($item->creator_type === 'subdealer' && $item->status === 'pending')
                                                <span class="badge alert-secondary">{{ trans('translations.siparis.onay_bekleniyor') }}</span>
                                            @else
                                                <span class="badge {{ $item->orderStatus->front_color_name }}">{{ $item->orderStatus->name }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if (auth('web')->check() && $item->creator_type === 'subdealer' && $item->status === 'pending')
                                                <a href="javascript:;" title="Sipariş Onayla" data-selector="approve-order" data-js="approve-order" data-id="{{ $item->id }}">
                                                    <span class="badge alert-success"><i class="fa-solid fa-check"></i> {{ trans('translations.siparis.onayla') }}</span>
                                                </a>
                                            @endif
                                            <a href="{{ route('excel.export.order', [$item->id]) }}" title="Excel'e Aktar">
                                                <span class="badge alert-success"><i class="fa-solid fa-file-excel"></i></span>
                                            </a>
                                            <a href="javascript:;" data-selector="order-show" data-js="order-show" data-url="{{ route('orders.show', [$item->id]) }}" title="{{ trans('translations.siparis.siparis_detayi_goruntule') }}">
                                                <span class="badge alert-primary"><i class="fa-solid fa-eye"></i></span>
                                            </a>
                                            @if (auth('web')->check() && auth('web')->user()->role === 'salesman')
                                                <a href="javascript:;"
                                                    data-modal-trigger="send-b2b-mail"
                                                    data-mail-type="order"
                                                    data-ref-id="{{ $item->id }}"
                                                    data-dealer-email="{{ $item->dealer_mail ?? '' }}">
                                                    <span class="badge alert-warning"><i class="fa-solid fa-envelope"></i></span>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">{{ trans('translations.siparis.siparis_yok') }}.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $orders->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ mix('js/frontend/modules/order/orders-index.js') }}"></script>
    <script src="{{ mix('js/frontend/modules/mail/send-modal.js') }}"></script>
@endpush


