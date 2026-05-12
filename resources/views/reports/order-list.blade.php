@extends('frontend.layouts.app')

@section('content')
    <section class="section-b-space">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="title d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h2>Sipariş Raporu</h2>
                        <a href="{{ route('reports.customer-statement') }}" class="btn btn-outline-secondary btn-sm">Cari Hareket</a>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-content">
                            Cari: <b class="text-title">{{ $currentAccount->code . ' ' . $currentAccount->name }}</b>
                        </h6>
                    </div>

                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Başlangıç</label>
                            <input type="date" name="startDate" value="{{ $startDate }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bitiş</label>
                            <input type="date" name="endDate" value="{{ $endDate }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sipariş Durumu</label>
                            <input type="number" name="status" value="{{ $status }}" class="form-control" placeholder="order_status_id">
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button class="btn theme-bg-color text-white">Filtrele</button>
                            <a href="{{ route('reports.order-list') }}" class="btn btn-danger text-white">Temizle</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tarih</th>
                                    <th>Durum</th>
                                    <th>Para Birimi</th>
                                    <th class="text-end">Toplam</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr>
                                        <td>#{{ $order->id }}</td>
                                        <td>{{ optional($order->created_at)->format('d.m.Y H:i') }}</td>
                                        <td>{{ $order->status }}</td>
                                        <td>{{ $order->currency ?? '-' }}</td>
                                        <td class="text-end">{{ number_format((float) $order->total_price, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">Kayıt bulunamadı.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection
