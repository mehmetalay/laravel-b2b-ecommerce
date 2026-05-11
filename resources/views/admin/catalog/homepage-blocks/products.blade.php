@extends('admin.layouts.app')

@section('title', 'Ürünler | Anasayfa Blokları')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Stok'],
            ['url' => route('admin.catalog.homepage-blocks.index'), 'label' => 'Anasayfa Blokları'],
            ['url' => route('admin.catalog.homepage-blocks.products', $homepageBlock->id), 'label' => $homepageBlock->title_tr],
            ['url' => 'javascript:;', 'label' => 'Ürünler'],
        ]">
        <li class="nav-item">
            <button type="submit" class="btn btn-success dash-btn mr-2" form="homepage-block-products-form" data-ajax-submit>
                <i class="las la-save"></i> Kaydet
            </button>
            <a href="{{ route('admin.catalog.homepage-blocks.index') }}" class="btn btn-info dash-btn">
                <i class="las la-list"></i> Listeye Dön
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-header">
                        <h4>Anasayfa Bloğu -> {{ $homepageBlock->title_tr }} -> Ürünler</h4>
                    </div>
                    <form id="homepage-block-products-form" method="POST" action="{{ route('admin.catalog.homepage-blocks.addProducts', $homepageBlock->id) }}" data-ajax-form>
                        @csrf
                        <div class="widget-content widget-content-area">
                            <div class="layout-spacing mb-3">
                                <button type="button" id="add-product" class="btn btn-primary btn-sm open-product-popup" data-action="open-product-popup" data-target="homepage-block-products-target" data-input-name="product_ids[]" data-form-id="homepage-block-products-form">Ürün Ekle</button>
                                <button type="button" id="clear-all-products" data-js="clear-all-products" class="btn btn-danger btn-sm" {{ count($items) === 0 ? 'style=display:none' : '' }}>Ürünleri Temizle</button>
                            </div>

                            <div id="homepage-block-products-target" class="table-responsive" style="max-height:600px; overflow-y:auto;">
                                <table class="table table-hover table-sm" id="products-table" data-js="products-table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Ürün</th>
                                            <th class="text-center" style="width: 50px;">İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($items as $item)
                                            <tr data-id="{{ $item->id }}">
                                                <td>
                                                    {{ $item->product_name }}
                                                    <div><small class="text-muted">{{ $item->code }}</small></div>
                                                    <input type="hidden" name="product_ids[]" value="{{ $item->id }}" form="homepage-block-products-form">
                                                </td>
                                                <td class="text-center">
                                                    <a href="javascript:;" class="btn btn-danger btn-sm delete-row" data-action="delete-row" data-js="delete-row">
                                                        <i class="las la-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
