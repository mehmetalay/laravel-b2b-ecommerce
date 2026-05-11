@extends('admin.layouts.app')

@section('title', 'Kategoriler')

@section('content')
    @php
        $vueCategoryTableProps = [
            'parentId' => request()->route('category')?->id,
            'createUrl' => route('admin.catalog.categories.create'),
        ];
    @endphp

    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Stok'],
            ['url' => route('admin.catalog.categories.index'), 'label' => 'Kategoriler'],
        ]">
        <li class="nav-item">
            @if (request()->route('category'))
                <a href="{{ route('admin.catalog.categories.index') }}" class="btn btn-info dash-btn mr-2">
                    <i class="las la-list"></i> Listeye Dön
                </a>
            @endif
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div data-vue="categories-index" data-props='@json($vueCategoryTableProps)'></div>
            </div>
        </div>
    </div>
@endsection
