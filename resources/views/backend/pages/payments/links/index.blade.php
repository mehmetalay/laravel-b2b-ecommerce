@extends('backend.layouts.app')

@section('title', 'Ödeme Linkleri')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Ödeme Linkleri']
        ]">
        <li class="nav-item">
            <a href="{{ route('admin.payment-links.create') }}" class="btn btn-info dash-btn">
                <i class="las la-plus"></i> Yeni
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-content widget-content-area br-6">
                        <div class="row">
                            <div class="col-sm-12 col-md-4 filtered-list-search align-self-center">
                                <form class="form-group" method="GET" accept-charset="utf-8" id="search-form">
                                    <label for="is_paid">Ara</label>
                                    <div class="input-group">
                                        <x-backend.input id="name" type="text" :value="request()->get('name')" placeholder="Müşteri adı, telefon, e-posta adresi.." />
                                        <div class="input-group-append">
                                            <button class="btn btn-soft-info" type="submit" id="filter-form">Ara</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-sm-12 col-md-2">
                                <div class="form-group">
                                    <label for="is_paid">Ödeme Durumu</label>
                                    <select class="form-control ml-lg-auto" name="is_paid" id="is_paid" onchange="javascript:this.form.submit();" form="search-form">
                                        <option value="" selected hidden>Seç</option>
                                        <option value="1" {{ request()->has('is_paid') && request()->get('is_paid') === '1' ? 'selected' : '' }}>Ödeme Yapıldı</option>
                                        <option value="0" {{ request()->has('is_paid') && request()->get('is_paid') === '0' ? 'selected' : '' }}>Ödeme Bekleniyor</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-2 align-self-center">
                                @if (!empty($_SERVER['QUERY_STRING']))
                                    <a href="javascript:;" class="btn btn-danger mr-1" onclick="removeFiltersFromURL()">Filtreleri Temizle</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="statbox widget box box-shadow">
                    <div class="widget-header">
                        <h4>Ödeme Linkleri</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-left">ID</th>
                                        <th class="text-left">Müşteri Bilgisi</th>
                                        <th class="text-center">Ödeme Tutarı</th>
                                        <th class="text-center">İşlem Tipi</th>
                                        <th class="text-center">Tutar Değiştirilemez</th>
                                        <th class="text-center">Ödeme Durumu</th>
                                        <th class="text-center">Ödeme Yapılan Tarihi</th>
                                        <th class="text-center">Oluşturan</th>
                                        <th class="text-center">Durum</th>
                                        <th class="no-content" style="width: 175px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $item)
                                        <tr id="parent-{{ $item->id }}">
                                            <td class="text-left">{{ $item->id }}</td>
                                            <td class="text-left">
                                                <div>{{ $item->user->name . ' ' . $item->user->code }}</div>
                                                <div><small>{!! '<strong>Mail Adresi:</strong> ' . ($item->email ?? '-') . (!$item->is_paid ? ($item->email ? ($item->email_sent ? ' <a href="' . route('mail.payment-link', ['payment_link' => $item->id, 'message' => 1]) . '" class="badge badge-info font-10">Mail Gönder</a>' : '') : '') : '') !!}</small></div>
                                                <div><small>{!! '<strong>Tel:</strong> ' . ($item->phone ?? '-') . (!$item->is_paid ? ($item->phone ? ($item->sms_sent ? ' <a href="" class="badge badge-info font-10">SMS Gönder</a>' : '') : '') : '') !!}</small></div>
                                            </td>
                                            <td class="text-center">{{ number_format($item->amount, 2) . ' TL' }}</td>
                                            <td class="text-center">
                                                <x-backend.status-badge type="transaction_type" :value="$item->transaction_type" />
                                                {!! $item->transaction_type == 3 ? '<div><small><strong>Banka:</strong> ' . $item->manualBankIntegration->full_name . '</small></div>' : '' !!}
                                                {!! $item->transaction_type == 3 ? '<div><small><strong>Taksit Sayısı:</strong> ' . $item->manual_installment . '</small></div>' : '' !!}
                                            </td>
                                            <td class="text-center">
                                                <x-backend.status-badge type="yes_no" :value="$item->amount_locked" />
                                            </td>
                                            <td class="text-center">
                                                <x-backend.status-badge type="payment_status" :value="$item->is_paid" />
                                                {!! $item->is_paid ? '<div><small>' . $item->bankIntegration->full_name . '</small></div>' : '' !!}
                                                {!! $item->is_paid ? '<div><small>' . $item->oid . '</small></div>' : '' !!}
                                            </td>
                                            <td class="text-center">{{ $item->payment_date ? format_date_time($item->payment_date) : '-' }}</td>
                                            <td class="text-center">
                                                <div>
                                                    <small>
                                                        <x-backend.status-badge type="role" :value="$item->admin_id" />
                                                    </small>
                                                </div>
                                                {!! '<div>
                                                        <small>' . ($item->admin_id ? $item->admin->name . ' ' . $item->admin->surname : $item->plasiyer->name) . '</small>
                                                    </div>
                                                    <div>
                                                        <small>' . format_date_time($item->created_at) . '</small>
                                                    </div>'
                                                !!}
                                            </td>
                                            <td class="text-center">
                                                <x-backend.status-badge type="active_passive" :value="$item->status" />
                                            </td>
                                            @if ($item->is_paid)
                                                <td class="text-center">
                                                    <a href="{{ route('pdf.payment-receipt.payment-link', [$item->id]) }}" title="PDF Görüntüle" class="btn btn-danger font-15 p-2" target="_blank"><i class="las la-file-pdf"></i></a>
                                                </td>
                                            @else
                                                <td class="text-center">
                                                    <a href="{{ route('payments.payment-link', [$item->token]) }}" title="Ödeme Yap" class="btn btn-secondary font-15 p-2" target="_blank"><i class="las la-external-link-alt"></i></a>
                                                    <a href="javascript:;" title="Link Kopyala" class="btn btn-soft-primary font-15 p-2" data-js="clipboard-payment-link" data-link="{{ route('payments.payment-link', [$item->token]) }}"><i class="las la-copy"></i></a>
                                                    <a href="{{ route('admin.payment-links.edit', [$item->id]) }}" title="Düzenle" class="btn btn-info font-15 p-2"><i class="las la-edit"></i></a>
                                                    <a href="javascript:;" class="btn btn-danger font-15 p-2" data-selector="row-delete" data-url="{{ route('admin.payment-links.destroy', [$item->id]) }}" title="Sil"><i class="las la-trash"></i></a>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr class="text-center">
                                            <td colspan="11">Veri yok.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <x-backend.pagination :paginator="$items" />
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.11/clipboard.min.js"></script>
    <script>
        $(document).ready(function() {
            var clipboard = new ClipboardJS('[data-js=clipboard-payment-link]', {
                text: function(trigger) {
                    return $(trigger).data('link');
                }
            });
            clipboard.on('success', function(e) {
                notify('success', 'Link kopyalandı')
            });
        });
    </script>
@endsection
