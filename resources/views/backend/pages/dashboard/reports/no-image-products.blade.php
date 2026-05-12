@extends('backend.layouts.app')

@section('title', 'Resim Olmayan Ürünler')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Raporlar'],
            ['url' => route('admin.report.no-image-products'), 'label' => 'Resim Olmayan Ürünler']
        ]">
        <li class="nav-item">
            <a class="btn btn-success dash-btn" href="javascript:;" data-download-url="{{ route('excel.export.no-image-products') }}" data-file-name="resim-olmayan-urunler" data-download-file>
                <i class="las la-file-excel"></i> Excel’e Aktar
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
                                <form class="form-group" method="GET" accept-charset="utf-8">
                                    <x-label for="name" text="Ara" />
                                    <div class="input-group">
                                        <x-backend.input id="name" type="text" :value="request()->get('name')" placeholder="Ürün adı, kodu.." />
                                        <div class="input-group-append">
                                            <button class="btn btn-soft-info" type="submit" id="filter-form">Ara</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-sm-12 col-md-2 filtered-list-search align-self-center">
                                <form class="form-group" method="GET" accept-charset="utf-8">
                                    <x-label for="status" text="Durum" />
                                    <div class="input-group">
                                        <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                                            <option value="" {{ request()->get('status') == '' ? 'selected' : '' }}>Tümü</option>
                                            <option value="active" {{ request()->get('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                            <option value="inactive" {{ request()->get('status') == 'inactive' ? 'selected' : '' }}>Pasif</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                            <div class="col-sm-12 col-md-4 align-self-center">
                                @if (!empty($_SERVER['QUERY_STRING']))
                                    <a href="javascript:;" class="btn btn-danger mr-1" onclick="removeFiltersFromURL()">Filtreleri Temizle</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="statbox widget box box-shadow">
                    <div class="widget-header">
                        <h4>Resim Olmayan Ürünler</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Ürün Adı</th>
                                        <th>Kodu</th>
                                        <th>Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->code }}</td>
                                            <td>
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
