@extends('backend.layouts.app')

@section('title', 'Anasayfa')

@section('css')
    <link href="/admin/assets/css/dashboard/dashboard_1.css?v=1.2" rel="stylesheet" type="text/css">
    <style>
        .order-table tbody tr, .critical-stock-table tbody tr {
            cursor: pointer;
        }
    </style>
@endsection

@section('js')
    <script src="/admin/assets/js/dashboard/dashboard_1.js?v=1.2"></script>
    <script>
        $('tbody tr').click(function () {
            var href = $(this).data('href');
            if (href) {
                window.location.href = href;
            }
        });
    </script>
@endsection

@section('content')
    <x-backend.breadcrumb></x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-4 col-sm-6 col-12 layout-spacing">
                <a class="widget quick-category">
                    <div class="quick-category-head">
                        <span class="quick-category-icon qc-primary rounded-circle">
                            <i class="las la-money-bill"></i>
                        </span>
                    </div>
                    <div class="quick-category-content">
                        <h3>{{ number_format($total_sales_amount_tl, 2) }} TL</h3>
                        <p class="text-primary">Toplam Satış Tutarı</p>
                    </div>
                </a>
            </div>
            <div class="col-xl-4 col-sm-6 col-12 layout-spacing">
                <a class="widget quick-category">
                    <div class="quick-category-head">
                        <span class="quick-category-icon qc-primary rounded-circle">
                            <i class="las la-money-bill"></i>
                        </span>
                    </div>
                    <div class="quick-category-content">
                        <h3>{{ number_format($total_sales_amount_usd, 2) }} USD</h3>
                        <p class="text-primary">Toplam Satış Tutarı</p>
                    </div>
                </a>
            </div>
            <div class="col-xl-4 col-sm-6 col-12 layout-spacing">
                <a class="widget quick-category">
                    <div class="quick-category-head">
                        <span class="quick-category-icon qc-warning rounded-circle">
                            <i class="las la-shopping-cart"></i>
                        </span>
                    </div>
                    <div class="quick-category-content">
                        <h3>{{ $total_number_of_orders }}</h3>
                        <p class="text-warning">Toplam Sipariş Sayısı</p>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-sm-6 col-12 layout-spacing">
                <a class="widget quick-category">
                    <div class="quick-category-head">
                        <span class="quick-category-icon qc-warning rounded-circle">
                            <i class="las la-shopping-cart"></i>
                        </span>
                    </div>
                    <div class="quick-category-content">
                        <h3>{{ $numberOfOrdersAwaitingApproval }}</h3>
                        <p class="text-warning">Onay Bekleyen Sipariş Sayısı</p>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-sm-6 col-12 layout-spacing">
                <a class="widget quick-category">
                    <div class="quick-category-head">
                        <span class="quick-category-icon qc-success rounded-circle">
                            <i class="las la-dolly"></i>
                        </span>
                    </div>
                    <div class="quick-category-content">
                        <h3>{{ $total_number_of_sold_products }}</h3>
                        <p class="text-success">Toplam Satılan Ürün Sayısı</p>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-sm-6 col-12 layout-spacing">
                <a class="widget quick-category">
                    <div class="quick-category-head">
                        <span class="quick-category-icon qc-secondary rounded-circle">
                            <i class="las la-user-friends"></i>
                        </span>
                    </div>
                    <div class="quick-category-content">
                        <h3>{{ $total_number_of_plasiyer_orders }}</h3>
                        <p class="text-secondary">Toplam Plasiyer Sipariş Sayısı</p>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-sm-6 col-12 layout-spacing">
                <a class="widget quick-category">
                    <div class="quick-category-head">
                        <span class="quick-category-icon qc-success-teal rounded-circle">
                            <i class="las la-user-friends"></i>
                        </span>
                    </div>
                    <div class="quick-category-content">
                        <h3>{{ $total_number_of_user_orders }}</h3>
                        <p class="text-success-teal">Toplam Bayi Sipariş Sayısı</p>
                    </div>
                </a>
            </div>
        </div>
        <div class="row layout-top-spacing">
            <div class="col-12 layout-spacing">
                <div class="widget dashboard-table">
                    <div class="widget-heading">
                        <h5>Son Siparişler (Son 10)</h5>
                    </div>
                    <div class="widget-content">
                        <div class="table-responsive order-table">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th class="text-center">Kullanıcı Türü</th>
                                        <th>Plasiyer</th>
                                        <th>Bayi</th>
                                        <th class="text-center">Toplam Sipariş Tutarı</th>
                                        <th class="text-center">Sipariş Durumu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($orders as $item)
                                        <tr data-href="{{ route('admin.orders.show', [$item->id]) }}">
                                            <td class="order-info">
                                                #{{ $item->id }}
                                                <div class="order-date">
                                                    <small class="text-muted">Sipariş Tarihi:<br>{{ $item->formatted_created_at }}</small>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <x-backend.status-badge type="user_type" :value="$item->creator_type" />
                                            </td>
                                            <td>{{ $item->salesman_name }}</td>
                                            <td>{{ $item->dealer_name }}</td>
                                            <td class="text-center">{{ $item->total_amount }}</td>
                                            <td class="text-center">
                                                <x-backend.status-badge type="custom" :color="$item->orderStatus->back_color_name" :label="$item->orderStatus->name" />
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="text-center">
                                            <td colspan="6">Henüz sipariş verilmedi.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <p class="font-13 text-center mt-4 mb-1 text-muted">
                                Tam sipariş listesini görmek için <a class="text-primary" href="{{ route('admin.orders.index') }}">buraya tıklayın</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
