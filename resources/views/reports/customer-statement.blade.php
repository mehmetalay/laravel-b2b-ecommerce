@extends('frontend.layouts.app')

@section('content')
    <section class="section-b-space">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="title d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h2>Cari Hareket Listesi ({{ $currentAccount->currency }})</h2>
                        <div class="d-flex gap-2">
                            <a href="{{ route('reports.order-list') }}" class="btn btn-outline-secondary btn-sm">Sipariş Listesi</a>
                            <a href="{{ route('reports.payment-list') }}" class="btn btn-outline-secondary btn-sm">Ödeme Listesi</a>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-content">
                            Cari: <b class="text-title">{{ $currentAccount->code . ' ' . $currentAccount->name }}</b>
                        </h6>
                    </div>

                    <form class="row" method="GET">
                        <div class="col-sm-12 col-md-4">
                            <label for="startDate" class="form-label">Başlangıç / Bitiş Tarihi</label>
                            <div class="input-group mb-3">
                                <input type="date" class="form-control" name="startDate" id="startDate" value="{{ request()->get('startDate') }}">
                                <span class="input-group-text">/</span>
                                <input type="date" class="form-control" name="endDate" id="endDate" value="{{ request()->get('endDate') }}">
                                <button class="btn theme-bg-color text-white" type="submit">
                                    <i data-feather="search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-8">
                            <label class="form-label d-none d-md-block">&nbsp;</label>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('pdf.customer-statement', request()->query()) }}" class="btn btn-dark btn-md text-white">
                                    <i class="fa-solid fa-file-pdf"></i> PDF İndir
                                </a>
                                <a href="javascript:;"
                                    data-modal-trigger="send-b2b-mail"
                                    data-mail-type="statement"
                                    data-ref-id="{{ $currentAccount->id }}"
                                    data-dealer-email="{{ $currentAccount->email ?? '' }}"
                                    class="btn theme-bg-color btn-md text-white">
                                    <i class="fa-solid fa-envelope"></i> Mail Gönder
                                </a>
                                @if (request()->has('startDate') || request()->has('endDate'))
                                    <a href="{{ route('reports.customer-statement') }}" class="btn btn-danger btn-md text-white">
                                        <i class="fa-solid fa-trash"></i> Filtreleri Temizle
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tarih</th>
                                    <th>İşlem Tipi</th>
                                    <th>Açıklama</th>
                                    <th>Vade</th>
                                    <th class="text-center">Borç ({{ number_format($debtTotal, 2) . ' ' . $currentAccount->currency }})</th>
                                    <th class="text-center">Alacak ({{ number_format($receivableTotal, 2) . ' ' . $currentAccount->currency }})</th>
                                    <th class="text-center">Bakiye ({{ number_format($balance, 2) . ' ' . $currentAccount->currency }})</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $carryoverBalance = 0;
                                @endphp
                                @forelse ($items as $item)
                                    @php
                                        $debt = (float) ($item['BORC'] ?? 0);
                                        $receivable = (float) ($item['ALACAK'] ?? 0);

                                        $carryoverBalance += $debt;
                                        $carryoverBalance -= $receivable;
                                    @endphp
                                    <tr>
                                        <td>{{ date('d.m.Y', strtotime($item['FISTARIHI'])) }}</td>
                                        <td>{{ $item['ISLEMTIPI'] }}</td>
                                        <td>{{ $item['ACIKLAMA'] ?? '-' }}</td>
                                        <td>{{ date('d.m.Y', strtotime($item['VADE'])) }}</td>
                                        <td class="text-center">{{ number_format($debt, 2) . ' ' . $item['DOVKOD'] }}</td>
                                        <td class="text-center">{{ number_format($receivable, 2) . ' ' . $item['DOVKOD'] }}</td>
                                        <td class="text-center">{{ number_format($carryoverBalance, 2) . ' ' . $item['DOVKOD'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">Kayıt bulunamadı.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ mix('js/frontend/modules/mail/send-modal.js') }}"></script>
@endpush
