@extends('backend.layouts.app')

@section('title', 'Ödeme Türleri')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Ayarlar'],
            ['url' => 'javascript:;', 'label' => 'Tanımlar'],
            ['url' => route('admin.settings.definitions.payment-types.index'), 'label' => 'Ödeme Türleri'],
        ]">
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-lg-12">
                <div class="statbox widget box box-shadow">
                    <div class="card-box">
                        <div class="row">
                            <div class="col-sm-12 col-md-4 filtered-list-search align-self-center">
                                <form class="form-group" method="GET" accept-charset="utf-8">
                                    <div class="input-group">
                                        <x-backend.input id="name" type="text" :value="request()->get('name')" placeholder="Adı.." />
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
                        <h4>Ödeme Türleri</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-left" style="width: 75px">ID</th>
                                        <th>Adı</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $item)
                                        <tr id="parent-{{ $item->id }}">
                                            <td class="text-left">{{ $item->id }}</td>
                                            <td>{{ $item->name }}</td>
                                        </tr>
                                    @empty
                                        <tr class="text-center">
                                            <td colspan="3">Veri yok.</td>
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
