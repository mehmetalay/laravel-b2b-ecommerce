@extends('admin.layouts.app')

@section('title', 'Özellikler')

@section('content')
    @php
        $vueAttributesProps = [
            'attributeGroupId' => $attributeGroup->id,
        ];
    @endphp

    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Stok'],
            ['url' => 'javascript:;', 'label' => 'Ürün Özellikleri'],
            ['url' => route('admin.catalog.product-attributes.attribute-groups.index'), 'label' => 'Özellik Grupları'],
            ['url' => route('admin.catalog.product-attributes.attribute-groups.attributes.index', ['attributeGroup' => $attributeGroup->id]), 'label' => 'Özellikler'],
        ]">
        <li class="nav-item">
            <a href="{{ route('admin.catalog.product-attributes.attribute-groups.index') }}" class="btn btn-info dash-btn">
                <i class="las la-list"></i> Listeye Dön
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div data-vue="attributes-index" data-server-datatable-component="attributes" data-props='@json($vueAttributesProps)'></div>
            </div>
        </div>
    </div>
@endsection
