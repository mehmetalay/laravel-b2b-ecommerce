@extends('admin.layouts.app')

@section('title', 'Düzenle')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Stok'],
            ['url' => route('admin.catalog.brands.index'), 'label' => 'Markalar'],
            ['url' => route('admin.catalog.brands.edit', $brand->id), 'label' => 'Düzenle'],
        ]">
        <li class="nav-item">
            <button type="submit" class="btn btn-success dash-btn mr-2" form="brand-form" data-ajax-submit>
                <i class="las la-save"></i> Kaydet
            </button>
            <a href="{{ route('admin.catalog.brands.index') }}" class="btn btn-info dash-btn">
                <i class="las la-list"></i> Listeye Dön
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row justify-content-center layout-top-spacing switch-outer-container">
            <div class="col-12 col-xl-6 layout-spacing">
                <form action="{{ route('admin.catalog.brands.update', [$brand->id]) }}" method="POST" data-ajax-form id="brand-form">
                    @csrf
                    @method('PATCH')
                    <div class="statbox widget box box-shadow mb-4">
                        <div class="widget-header">
                            <h4>Düzenle</h4>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="form-group">
                                <x-backend.input id="name" label="Marka Adı" type="text" :value="$brand->name" :required="true" autofocus/>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <x-backend.input id="sort_order" label="Sıra" type="number" :value="$brand->sort_order"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-12 col-xl-3 layout-spacing">
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-header">
                        <h4>Durumu</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="w-100">
                            <div class="form-group row">
                                <div class="col-3">
                                    <span class="switch align-items-start">
                                        <label>
                                            <input type="checkbox" name="status" form="brand-form" {{ $brand->status ? 'checked' : '' }}>
                                            <span></span>
                                        </label>
                                    </span>
                                </div>
                                <label class="col-9 col-form-label" id="status-label-text"> {{ $brand->status ? 'Aktif' : 'Pasif' }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-header">
                        <h4>Resim <small class="text-muted">(Önerilen Ebat: {{ config('images.sizes.brand.recommended_resolution') }})</small></h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="form-group">
                            <div class="image-upload">
                                <div class="image-edit">
                                    <input type="file" id="brand_image_upload" data-preview="#brand_image_preview" name="image" accept=".png, .jpg, .jpeg" form="brand-form">
                                    <label for="brand_image_upload">
                                        <i class="las la-pen"></i>
                                    </label>
                                </div>
                                <div class="image-preview">
                                    <div id="brand_image_preview" style="background-image: url({{ image_url($brand->image, 'brand') }}); background-size: contain;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-header">
                        <h4>İzin Verilen Ödeme Tipleri</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        @php
                            $allowedMethods = explode(',', $brand->allowed_payment_methods ?? 'cash,credit,term');
                        @endphp
                        <div class="form-group row">
                            <div class="col-3">
                                <span class="switch align-items-start">
                                    <label>
                                        <input type="checkbox" id="payment_cash" name="allowed_payment_methods[]" value="cash" {{ in_array('cash', $allowedMethods) ? 'checked' : '' }} form="brand-form">
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" for="payment_cash">Nakit</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-3">
                                <span class="switch align-items-start">
                                    <label>
                                        <input type="checkbox" id="payment_credit" name="allowed_payment_methods[]" value="credit" {{ in_array('credit', $allowedMethods) ? 'checked' : '' }} form="brand-form">
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" for="payment_credit">Kredi Kartı</label>
                        </div>
                        <div class="form-group row">
                            <div class="col-3">
                                <span class="switch align-items-start">
                                    <label>
                                        <input type="checkbox" id="payment_term" name="allowed_payment_methods[]" value="term" {{ in_array('term', $allowedMethods) ? 'checked' : '' }} form="brand-form">
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" for="payment_term">Vadeli</label>
                        </div>
                    </div>
                </div>
        </div>
    </div>
@endsection

