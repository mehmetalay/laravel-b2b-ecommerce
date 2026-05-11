@extends('admin.layouts.app')

@section('title', 'Paket Bölünemez Ürünler')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Raporlar'],
            ['url' => route('admin.report.non-splittable-packages'), 'label' => 'Paket Bölünemez Ürünler']
        ]">
        <li class="nav-item">
            <!-- <a class="btn btn-success dash-btn" href="javascript:;" data-download-url="" data-file-name="paket-bolunemez-urunler" data-download-file>
                <i class="las la-file-excel"></i> Excel’e Aktar
            </a> -->
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div class="statbox widget box box-shadow">
                    <div class="card-box">
                        <form class="row" method="GET" accept-charset="utf-8">
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group">
                                    <x-label for="name" text="Ara" />
                                    <x-backend.input id="name" type="text" :value="request()->get('name')" placeholder="Ürün adı, ürün kodu..."/>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-6 col-xl-2">
                                <div class="form-group">
                                    <x-label for="status" text="Ürün Durumu" />
                                    <select class="selectpicker w-100 ml-lg-auto" title="Seç" name="status" id="status">
                                        <option value="active" {{ request()->get('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                                        <option value="inactive" {{ request()->get('status') === 'inactive' ? 'selected' : '' }}>Pasif</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-6 col-xl-2">
                                <div class="form-group">
                                    <x-label for="stock_quantity" text="Stok Miktarı" />
                                    <select class="selectpicker w-100 ml-lg-auto" title="Seç" name="stock_quantity" id="stock_quantity">
                                        <option value="zero_or_less" {{ request()->get('stock_quantity') === 'zero_or_less' ? 'selected' : '' }}>
                                            0 ve altı
                                        </option>
                                        <option value="positive" {{ request()->get('stock_quantity') === 'positive' ? 'selected' : '' }}>
                                            1 ve üzeri
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-6 col-xl-2">
                                <div class="form-group">
                                    <x-label for="package_quantity" text="Paket Adedi" />
                                    <select class="selectpicker w-100 ml-lg-auto" title="Seç" name="package_quantity" id="package_quantity">
                                        <option value="zero_or_less" {{ request()->get('package_quantity') === 'zero_or_less' ? 'selected' : '' }}>
                                            0 ve altı
                                        </option>
                                        <option value="positive" {{ request()->get('package_quantity') === 'positive' ? 'selected' : '' }}>
                                            1 ve üzeri
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 col-lg-6 col-xl-4">
                                <label class="col-form-label d-none d-md-block">&nbsp;</label>
                                <div class="d-flex align-items-center">
                                    <button class="btn btn-primary mr-2" type="submit">Filtrele</button>
                                    @if (request()->query())
                                        <x-backend.filter-clear-button :route="route('admin.report.non-splittable-packages')"/>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="statbox widget box box-shadow">
                    <div class="widget-header">
                        <h4>Paket Bölünemez Ürünler</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Ürün Adı</th>
                                        <th>Ürün Kodu</th>
                                        <th class="text-center">Stok Miktarı</th>
                                        <th class="text-center">Paket Adedi</th>
                                        <th class="text-center">Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->code }}</td>
                                            <td class="text-center">{{ $item->stock }}</td>
                                            <td class="text-center">{{ $item->box_quantity }}</td>
                                            <td class="text-center">
                                                <x-backend.status-badge type="active_passive" :value="$item->status" />
                                            </td>
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
