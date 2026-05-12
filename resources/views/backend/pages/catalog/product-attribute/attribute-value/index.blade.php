@extends('backend.layouts.app')

@section('title', 'Özellik Değerleri')

@section('content')
    @php
        $vueAttributeValuesProps = [
            'attributeId' => $attribute->id,
        ];
    @endphp

    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Stok'],
            ['url' => 'javascript:;', 'label' => 'Ürün Özellikleri'],
            ['url' => route('admin.catalog.product-attributes.attribute-groups.index'), 'label' => 'Özellik Grupları'],
            ['url' => route('admin.catalog.product-attributes.attribute-groups.attributes.index', ['attributeGroup' => $attribute->attributeGroup->id]), 'label' => 'Özellikler'],
            ['url' => route('admin.catalog.product-attributes.attributes.attribute-values.index', ['attribute' => $attribute->id]), 'label' => 'Özellik Değerleri'],
        ]">
        <li class="nav-item">
            <a href="{{ route('admin.catalog.product-attributes.attribute-groups.attributes.index', ['attributeGroup' => $attribute->attributeGroup->id]) }}" class="btn btn-info dash-btn">
                <i class="las la-list"></i> Listeye Dön
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div data-vue="attribute-values-index" data-server-datatable-component="attribute-values" data-props='@json($vueAttributeValuesProps)'></div>
            </div>
        </div>
    </div>
@endsection
