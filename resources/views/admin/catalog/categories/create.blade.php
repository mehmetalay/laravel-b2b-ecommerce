@extends('admin.layouts.app')

@section('title', 'Yeni')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Stok'],
            ['url' => route('admin.catalog.categories.index'), 'label' => 'Kategoriler'],
            ['url' => route('admin.catalog.categories.create'), 'label' => 'Yeni'],
        ]">
        <li class="nav-item">
            <button type="submit" class="btn btn-success dash-btn mr-2" form="category-form" data-ajax-submit>
                <i class="las la-save"></i> Kaydet
            </button>
            <a href="{{ route('admin.catalog.categories.index') }}" class="btn btn-info dash-btn">
                <i class="las la-list"></i> Listeye Dön
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row justify-content-center layout-top-spacing switch-outer-container">
            <div class="col-12 col-xl-6 layout-spacing">
                <form action="{{ route('admin.catalog.categories.store') }}" method="POST" id="category-form" data-ajax-form>
                    @csrf
                    <div class="statbox widget box box-shadow mb-4">
                        <div class="widget-header">
                            <h4>Yeni</h4>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="form-group">
                                <x-backend.input id="name" label="Kategori Adı" type="text" :required="true" autofocus/>
                            </div>
                            <div class="form-group">
                                <label for="parent_id">Üst Kategori (Opsiyonel)</label>
                                <select name="parent_id" class="form-control">
                                    <option value="">Ana Kategori</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ isset($category) && $category->parent_id == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="stock_display_limit" label="Stok Gösterim Limiti" type="text"/>
                                        <small class="text-muted">Boş bıraktığınızda çalışmayacaktır.</small>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <x-backend.input id="sort_order" label="Sıra" type="text"/>
                                    </div>
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
                                            <input type="checkbox" name="status" checked form="category-form">
                                            <span></span>
                                        </label>
                                    </span>
                                </div>
                                <label class="col-9 col-form-label" id="status-label-text">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-header">
                        <h4>Resim <small class="text-muted">(Önerilen Ebat: {{ config('images.sizes.category.recommended_resolution') }})</small></h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="form-group">
                            <div class="image-upload">
                                <div class="image-edit">
                                    <input type="file" id="category_image_upload" data-preview="#category_image_preview" name="image" accept=".png, .jpg, .jpeg" form="category-form">
                                    <label for="category_image_upload">
                                        <i class="las la-pen"></i>
                                    </label>
                                </div>
                                <div class="image-preview">
                                    <div id="category_image_preview" style="background-size: contain;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
