@extends('admin.layouts.app')

@section('css')
    <link href="/admin/assets/css/apps/ecommerce.css?v=1.2.2" rel="stylesheet" type="text/css"/>
@endsection

@section('title', 'Sipariş Detayı')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Siparişler'],
            ['url' => route('admin.orders.edit', $order->id), 'label' => 'Detay']
        ]">
        <li class="nav-item">
            <a href="javascript:;" class="btn btn-primary dash-btn mr-2" data-download-url="{{ route('excel.export.order', [$order->id]) }}" data-file-name="siparis-detayi-{{ $order->id }}" data-download-file>
                <i class="las la-file-excel"></i> Excel'e Aktar
            </a>
            <button type="submit" class="btn btn-success dash-btn mr-2" form="order-form" data-ajax-submit>
                <i class="las la-save"></i> Kaydet
            </button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-info dash-btn">
                <i class="las la-list"></i> Listeye Dön
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing switch-outer-container">
            <div class="col-12 col-xl-9 layout-spacing">
                <div class="widget-content searchable-container grid">
                    {{-- Sipariş ürünleri --}}
                    <div class="card-box order-detail-table">
                        <h5 class="header-title mb-3">Sipariş Detayı #{{ $order->id }}</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-centered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center"><i class="las la-image"></i></th>
                                        <th>Ürün</th>
                                        <th class="text-center">Kodu</th>
                                        <th class="text-center">Fiyat</th>
                                        <th class="text-center">Miktar</th>
                                        <th class="text-center">İndirim (%)</th>
                                        <th class="text-center">Kampanya</th>
                                        <th class="text-center">Ara Toplam</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->orderProducts()->with('product')->get()->sortBy('product.code') as $item)
                                        <tr>
                                            <th scope="row" class="text-center">
                                                <img src="{{ $item->product->image_small_url_1 }}" width="50">
                                            </th>
                                            <th class="align-middle">
                                                <a href="{{ route('product.detail', [$item->product_slug]) }}" class="text-info" target="_blank">{{ $item->product_name }} <i class="las la-external-link-alt font-20"></i></a>
                                                @if ($item->explanation)
                                                    <div><small>Açıklama: {{ $item->explanation }}</small></div>
                                                @endif
                                            </th>
                                            <td class="text-center">{{ $item->product_code }}</td>
                                            <td class="text-center">{{ $item->formatted_price }}</td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-center">{{ number_format($item->discount, 2) }}</td>
                                            <td class="text-center">
                                                @if ($item->is_campaign_gift)
                                                    <span class="badge badge-primary">🎁 Hediye</span>
                                                @elseif ($item->campaign_discount > 0)
                                                    <span class="badge badge-success">
                                                        -{{ number_format($item->campaign_discount, 2) }} ₺
                                                        @if ($item->campaign_discount_percent)
                                                            ({{ number_format($item->campaign_discount_percent, 2) }}%)
                                                        @endif
                                                    </span>
                                                @else
                                                    -
                                                @endif

                                                @if ($item->campaign_note)
                                                    <div>
                                                        <small class="text-muted">
                                                            {!! $item->campaign_note !!}
                                                        </small>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $item->formatted_total_price }}</td>
                                        </tr>
                                    @endforeach
                                    
                                    <tr>
                                        <th scope="row" colspan="7" class="text-right">Ürünler</th>
                                        <td class="font-weight-bold">{{ $order->formatted_total_product_price }}</td>
                                    </tr>

                                    <tr>
                                        <th scope="row" colspan="7" class="text-right">Toplam Ürün</th>
                                        <td class="font-weight-bold">{{ $order->total_quantity }} Adet</td>
                                    </tr>

                                    <tr>
                                        <th scope="row" colspan="7" class="text-right">Toplam Ürün Çeşidi</th>
                                        <td class="font-weight-bold">{{ $order->unique_product_count }} Çeşit</td>
                                    </tr>

                                    @if ($order->campaign_discount_total > 0)
                                        <tr>
                                            <th colspan="7" class="text-right text-success">
                                                Kampanya İndirimi
                                            </th>
                                            <td class="font-weight-bold text-success">
                                                -{{ number_format($order->campaign_discount_total, 2) }} ₺
                                            </td>
                                        </tr>
                                    @endif

                                    @if ($order->has_free_shipping)
                                        <tr>
                                            <th colspan="7" class="text-right text-primary">
                                                Ücretsiz Kargo Kampanyası
                                            </th>
                                            <td class="font-weight-bold text-primary">
                                                ✔
                                            </td>
                                        </tr>
                                    @endif

                                    @if ($order->cart_discount_rate_1)
                                        <tr>
                                            <th scope="row" colspan="7" class="text-right">Sepet İndirimi (%{{ $order->cart_discount_rate_1 }})</th>
                                            <td class="font-weight-bold">{{ $order->formatted_cart_discount_1 }}</td>
                                        </tr>
                                    @endif

                                    <tr>
                                        <th scope="row" colspan="7" class="text-right">Toplam Tutar</th>
                                        <td class="font-weight-bold">{{ $order->total_amount }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- Sipariş Bilgileri --}}
                    <div class="card-box">
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="mb-4">
                                    <h6 class="mt-0">Sipariş No:</h6>
                                    <p>#{{ $order->id }}</p>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="mb-4">
                                    <h6 class="mt-0">Sipariş Tarihi:</h6>
                                    <p>{{ $order->formatted_created_at }}</p>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="mb-4">
                                    <h6 class="mt-0">Plasiyer:</h6>
                                    <p>{{ $order->salesman_name }}</p>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="mb-4">
                                    <h6 class="mt-0">Bayi:</h6>
                                    <p>{{ $order->dealer_name }}</p>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="mb-4">
                                    <h6 class="mt-0">Ödeme Türü:</h6>
                                    <p>{{ $order->payment_type_name }}</p>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="mb-4">
                                    <h6 class="mt-0">Döviz Kuru (USD):</h6>
                                    <p>{{ $order->formatted_usd_exchange_rate }}</p>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="mb-4">
                                    <h6 class="mt-0">Kampanya Durumu:</h6>
                                    <p>{{ $order->campaign_status }}</p>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="mb-4">
                                    <h6 class="mt-0">Ip Adresi:</h6>
                                    <p>{{ $order->ip_address }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-3 layout-spacing">
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-content widget-content-area p-0">
                        <div class="form-group">
                            <label for="order_status_id">Sipariş Durumu</label>
                            <select class="form-control" name="order_status_id" id="order_status_id" form="order-form">
                                @foreach ($orderStatuses as $orderStatus)
                                    <option value="{{ $orderStatus->id }}" {{ $orderStatus->id == $order->order_status_id ? 'selected' : '' }}>{{ $orderStatus->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-content widget-content-area p-0">
                        <h6 class="header-title mb-3">
                            Teslimat Bilgileri
                        </h6>
                        <p>
                            <strong>Teslimat Şekli:</strong>
                            {{ $order->delivery_summary['delivery_type'] }}
                        </p>

                        @foreach ($order->delivery_summary['items'] as $item)
                            <p class="mb-1">
                                <strong>{{ $item['label'] }}: </strong>
                                {!! $item['value'] !!}
                            </p>
                        @endforeach
                    </div>
                </div>
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-content widget-content-area p-0">
                        <div class="form-group">
                            <label>Açıklama</label>
                            <p class="text-muted">{{ $order->explanation ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-content widget-content-area p-0">
                        <div class="form-group">
                            <x-backend.textarea name="order_note" label="Sipariş Notu" rows="5" :value="$order->order_note" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form action="{{ route('admin.orders.update', [$order->id]) }}" method="post" id="order-form" enctype="multipart/form-data" data-ajax-form>
        @csrf
        @method('PATCH')
    </form>
@endsection
