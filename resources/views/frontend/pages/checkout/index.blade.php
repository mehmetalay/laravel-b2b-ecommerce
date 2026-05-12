@extends('frontend.layouts.app')

@section('content')
    <section class="section-b-space">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="title">
                        <h2>{{ trans('translations.payment.index.odeme_raporu') }}</h2>
                    </div>

                    {{-- FİLTRELER --}}
                    <form class="row g-3 align-items-end mb-4" method="GET" action="{{ route('payments.index') }}" onsubmit="removeEmptyInputs(this)">
                        @if (auth('web')->check() && auth('web')->user()->role === 'salesman')
                            <div class="col-sm-12 col-md-2">
                                <label for="name" class="form-label">{{ trans('translations.payment.index.bayi_adi') }}</label>
                                <input type="text" class="form-control" name="name" id="name" value="{{ request()->get('name') }}">
                            </div>
                        @endif

                        <div class="col-sm-12 col-md-3">
                            <label for="date_from" class="form-label">{{ trans('translations.payment.index.baslangic_ve_bitis_tarihi') }}</label>
                            <div class="input-group">
                                <input type="date" class="form-control" name="date_from" id="date_from" value="{{ request()->get('date_from', $startDate) }}">
                                <span class="input-group-text">-</span>
                                <input type="date" class="form-control" name="date_to" id="date_to" value="{{ request()->get('date_to', $endDate) }}">
                            </div>
                        </div>

                        <div class="col-sm-12 col-md-3">
                            <label class="form-label d-none d-md-block">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn theme-bg-color text-white gap-2">
                                    <i class="fa fa-search"></i> Ara
                                </button>

                                <a href="#" class="btn btn-success text-white gap-2" data-download-url="{{ route('excel.export.payment', array_merge(request()->query(), ['source' => 'b2b'])) }}" data-file-name="odeme-raporu" data-download-file>
                                    <i class="fa-solid fa-file"></i>
                                    {{ trans('translations.payment.index.excel_indir') }}
                                </a>

                                @if (request()->query())
                                    <a href="{{ route('payments.index') }}" class="btn btn-danger text-white gap-2">
                                        <i class="fa-solid fa-trash"></i> Filtreleri Temizle
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ trans('translations.payment.index.plasiyer') }}</th>
                                    <th>{{ trans('translations.payment.index.bayi') }}</th>
                                    <th class="text-center">{{ trans('translations.payment.index.odeme_bilgisi') }}</th>
                                    <th class="text-center">{{ trans('translations.payment.index.kart_bilgisi') }}</th>
                                    <th>{{ trans('translations.payment.index.aciklama') }}</th>
                                    <th class="text-center">{{ trans('translations.payment.index.3d_odeme') }}</th>
                                    <th class="text-center">{{ trans('translations.payment.index.durum') }}</th>
                                    <th class="text-center">{{ trans('translations.payment.index.tarih') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($payments as $item)
                                    <tr>
                                        <td>{{ isset($item->plasiyer) ? $item->plasiyer->name : '-' }}</td>
                                        <td>{{ $item->user->name }}</td>
                                        <td class="text-center">{{ number_format($item->amount_paid, 2) }} TL <small>({{ $item->amount_paid_usd }} USD)</small>
                                            <div>
                                                <small class="text-muted">
                                                    {{ trans('translations.payment.index.sanal_pos_adi') . ': ' . $item->bank_integration_name }}<br>
                                                    {{ trans('translations.payment.index.taksit_sayisi') . ': ' . $item->installment }}<br>
                                                    {{ trans('translations.payment.index.komisyon') . ': (%' . $item->commission_rate . ') ' . number_format($item->commission_amount, 2) }} TL<br>
                                                    {{ trans('translations.payment.index.dolar_kuru') . ': ' . $item->usd_rate_info }}
                                                </small>
                                            </div>
                                        </td>
                                        <td class="text-center">{{ $item->card_name }}
                                            <div>
                                                <small class="text-muted">{{ $item->card_number }}</small>
                                            </div>
                                        </td>
                                        <td>{{ $item->explanation }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $item->option_3d_payment ? 'alert-success' : 'alert-danger' }}">{{ $item->option_3d_payment ? trans('translations.payment.index.evet') : trans('translations.payment.index.hayir') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $item->status === 'SUCCESS' ? 'alert-success' : 'alert-danger' }}">{{ $item->status === 'SUCCESS' ? trans('translations.payment.index.basarili') : trans('translations.payment.index.basarisiz') }}</span>
                                            @if ($item->status === 'FAILED')
                                                <a href="javascript:;" data-bs-toggle="popover" title="{{ trans('translations.payment.index.hata_mesaji') }}" data-bs-content="{{ $item->failure_reason }}"><span class="badge alert-dark">?</span></a>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->formatted_completed_at }}</td>
                                        <td>
                                            @if ($item->status === 'SUCCESS' && $item->user->receipt_enabled)
                                                <a href="{{ route('pdf.payment-receipt.payment', [$item->id]) }}" target="_blank" title="Ödeme Dekontu">
                                                    <span class="badge alert-danger"><i class="fa-solid fa-file-pdf"></i></span>
                                                </a>
                                                @if (auth('web')->check() && auth('web')->user()->role === 'salesman')
                                                    <a href="javascript:;"
                                                        data-modal-trigger="send-b2b-mail"
                                                        data-mail-type="payment"
                                                        data-ref-id="{{ $item->id }}"
                                                        data-dealer-email="{{ $item->dealer_mail ?? '' }}">
                                                        <span class="badge alert-warning"><i class="fa-solid fa-envelope"></i></span>
                                                    </a>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9">{{ trans('translations.payment.index.veri_yok') }}.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $payments->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script>
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl)
        })
    </script>
    <script src="{{ mix('js/frontend/modules/mail/send-modal.js') }}"></script>
@endsection
