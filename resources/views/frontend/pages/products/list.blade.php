@extends('frontend.layouts.app')

@php
    if ($type == 'list') {
        $title = $category->name;
        $slug = $category->slug;
    } elseif ($type == 'brand') {
        $title = $brand->name;
        $slug = $brand->slug;
    } elseif ($type == 'all') {
        $title = 'Tüm Ürünler';
        $slug = null;
    } elseif ($type == 'block') {
        $title = $block->{'title_' . app()->getLocale()};
        $slug = 'blok/' . $block->slug;
    } elseif ($type == 'search') {
        $title = $q;
        $slug = 'ara?q=' . $q;
    } else {
        $title = '';
        $slug = '';
    }
@endphp

@section('title', $title)

@section('content')
    <section class="breadscrumb-section pt-0">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="breadscrumb-contain">
                        <h2>{{ $title }}</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="/">
                                        <i class="fa-solid fa-house"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="section-b-space shop-section">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="show-button">
                        <div class="top-filter-menu-2">
                            <div class="sidebar-filter-menu me-3" data-bs-toggle="modal" data-bs-target=".product-filter">
                                <a href="javascript:;"><i class="fa-solid fa-filter"></i> {{ trans('translations.list.filtrele') }}</a>
                            </div>
                            <div class="d-flex align-items-center gap-2"></div>
                            <div class="ms-auto d-flex align-items-center">
                                <div class="category-dropdown">
                                    <h5 class="text-content">{{ trans('translations.list.sirala') }}:</h5>
                                    <div class="dropdown">
                                        @php
                                            $sorting_name = [
                                                'yeniden-eskiye' => trans('translations.list.yeniden_eskiye'),
                                                'eskiden-yeniye' => trans('translations.list.eskiden_yeniye'),
                                                'dusuk-fiyat' => trans('translations.list.dusuk_fiyat'),
                                                'yuksek-fiyat' => trans('translations.list.yuksek_fiyat'),
                                                'a-z-sirala' => trans('translations.list.urun_koduna_gore_a_z'),
                                                'z-a-sirala' => trans('translations.list.urun_koduna_gore_z_a'),
                                                'dusuk-stok' => trans('translations.list.dusuk_stok'),
                                                'yuksek-stok' => trans('translations.list.yuksek_stok'),
                                            ];
                                        @endphp
                                        <button class="dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown">
                                            <span>{{ request()->has('sirala') ? $sorting_name[$_GET['sirala']] : trans('translations.list.varsayilan_siralama') }}</span> <i class="fa-solid fa-angle-down"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                            @if (session()->has('admin_login'))
                                                <li>
                                                    <a class="dropdown-item {{ request()->get('sirala') === 'yeniden-eskiye' ? 'active' : '' }}" id="pop" href="javascript:;" onclick="setFilter('yeniden-eskiye')">{{ $sorting_name['yeniden-eskiye'] }}</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item {{ request()->get('sirala') === 'eskiden-yeniye' ? 'active' : '' }}" id="low" href="javascript:;" onclick="setFilter('eskiden-yeniye')">{{ $sorting_name['eskiden-yeniye'] }}</a>
                                                </li>
                                            @endif
                                            <li>
                                                <a class="dropdown-item {{ request()->get('sirala') === 'dusuk-fiyat' ? 'active' : '' }}" id="high" href="javascript:;" onclick="setFilter('dusuk-fiyat')">{{ $sorting_name['dusuk-fiyat'] }}</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item {{ request()->get('sirala') === 'yuksek-fiyat' ? 'active' : '' }}" id="rating" href="javascript:;" onclick="setFilter('yuksek-fiyat')">{{ $sorting_name['yuksek-fiyat'] }}</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item {{ request()->get('sirala') === 'a-z-sirala' ? 'active' : '' }}" id="aToz" href="javascript:;" onclick="setFilter('a-z-sirala')">{{ $sorting_name['a-z-sirala'] }}</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item {{ request()->get('sirala') === 'z-a-sirala' ? 'active' : '' }}" id="zToa" href="javascript:;" onclick="setFilter('z-a-sirala')">{{ $sorting_name['z-a-sirala'] }}</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="view-type-toggle ms-3">
                                    <button class="view-btn {{ $viewType == 'grid' ? 'active' : '' }}" onclick="setViewType('grid')">
                                        <i class="fa-solid fa-border-all"></i> Katalog
                                    </button>
                                    <button class="view-btn {{ $viewType == 'list' ? 'active' : '' }}" onclick="setViewType('list')">
                                        <i class="fa-solid fa-list"></i> Liste
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if ($viewType == 'list')
                        @include('frontend.pages.products.partials._list-view')
                    @else
                        @include('frontend.pages.products.partials._grid-view')
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ mix('js/frontend/modules/products/index.js') }}"></script>
@endpush

@section('js')
    <script src="{{ mix('js/frontend/modules/cart/add-to-cart.js') }}"></script>
@endsection
