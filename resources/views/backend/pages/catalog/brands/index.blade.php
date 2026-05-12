@extends('backend.layouts.app')

@section('title', 'Markalar')

@section('content')
    @php
        $vueBrandTableProps = [
            'createUrl' => route('admin.catalog.brands.create'),
        ];
    @endphp

    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Stok'],
            ['url' => route('admin.catalog.brands.index'), 'label' => 'Markalar'],
        ]">
        <li class="nav-item"></li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div data-vue="brands-index" data-server-datatable-component="brands" data-props='@json($vueBrandTableProps)'></div>
            </div>
        </div>
    </div>
@endsection

