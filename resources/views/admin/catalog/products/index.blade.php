@extends('admin.layouts.app')

@section('title', 'Ürünler')

@section('content')
    @php
        $vueProductTableProps = [
            'title' => 'Ürünler',
            'endpoint' => route('admin.products.table-data'),
            'columns' => [
                ['key' => 'id', 'label' => 'ID'],
                ['key' => 'image_url', 'label' => 'Resim', 'type' => 'image'],
                ['key' => 'name', 'label' => 'Ürün Adı'],
                ['key' => 'category', 'label' => 'Kategori'],
                ['key' => 'brand', 'label' => 'Marka'],
                ['key' => 'price', 'label' => 'Fiyat'],
                ['key' => 'stock', 'label' => 'Stok'],
                ['key' => 'status', 'label' => 'Durum'],
                ['key' => 'edit_url', 'label' => 'İşlemler', 'type' => 'link'],
            ],
            'filters' => [
                'categories' => $categories->map(fn ($category) => [
                    'value' => $category->id,
                    'label' => $category->name,
                ])->values(),
                'brands' => $brands->map(fn ($brand) => [
                    'value' => $brand->id,
                    'label' => $brand->name,
                ])->values(),
                'statusOptions' => [
                    ['value' => '', 'label' => 'Tüm Durumlar'],
                    ['value' => '1', 'label' => 'Aktif'],
                    ['value' => '0', 'label' => 'Pasif'],
                ],
                'stockStatusOptions' => [
                    ['value' => '', 'label' => 'Tüm Stoklar'],
                    ['value' => 'in_stock', 'label' => 'Stokta'],
                    ['value' => 'out_of_stock', 'label' => 'Stokta Değil'],
                ],
            ],
            'createUrl' => route('admin.catalog.products.create'),
        ];
    @endphp

    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Stok'],
            ['url' => route('admin.catalog.products.index'), 'label' => 'Ürünler'],
        ]">
        <li class="nav-item"></li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div data-vue="products-index" data-props='@json($vueProductTableProps)'></div>
            </div>
        </div>
    </div>
@endsection

