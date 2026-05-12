@extends('backend.layouts.app')

@section('title', 'Pasif Ürünler')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Raporlar'],
            ['url' => route('admin.report.inactive-products'), 'label' => 'Pasif Ürünler']
        ]">
        <li class="nav-item">
            <a class="btn btn-success dash-btn" href="javascript:;" data-download-url="{{ route('excel.export.inactive-products') }}" data-file-name="pasif-urunler" data-download-file>
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
                        <h4>Pasif Ürünler</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Resim</th>
                                        <th>Ürün Adı</th>
                                        <th class="text-center">Kodu</th>
                                        <th class="text-center">Stok</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $item)
                                        <tr>
                                            <td><img src="{{ $item->image_small_url_1 }}" alt="" width="75"></td>
                                            <td>{{ $item->name }}</td>
                                            <td class="text-center">{{ $item->code }}</td>
                                            <td class="text-center">{{ $item->stock }}</td>
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
