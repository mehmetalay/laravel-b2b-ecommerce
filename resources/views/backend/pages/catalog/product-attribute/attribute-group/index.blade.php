@extends('backend.layouts.app')

@section('title', 'Özellik Grupları')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Stok'],
            ['url' => 'javascript:;', 'label' => 'Ürün Özellikleri'],
            ['url' => route('admin.catalog.product-attributes.attribute-groups.index'), 'label' => 'Özellik Grupları'],
        ]" />

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div data-vue="attribute-groups-index" data-server-datatable-component="attribute-groups"></div>
            </div>
        </div>
    </div>
@endsection
