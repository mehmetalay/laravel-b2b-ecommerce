@extends('admin.layouts.app')

@section('title', 'Düzenle')

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => 'javascript:;', 'label' => 'Stok'],
            ['url' => route('admin.catalog.products.index'), 'label' => 'Ürünler'],
            ['url' => route('admin.catalog.products.edit', $product->id), 'label' => 'Düzenle'],
        ]">
        <li class="nav-item">
            <button type="submit" class="btn btn-success dash-btn mr-2 data-form-button" form="product-form" data-ajax-submit>
                <i class="las la-save"></i> Kaydet
            </button>
            <a href="{{ route('admin.catalog.products.index') }}" class="btn btn-info dash-btn">
                <i class="las la-list"></i> Listeye Dön
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing switch-outer-container">
            <div class="col-12 col-xl-9 layout-spacing">
                <form action="{{ route('admin.catalog.products.update', [$product->id]) }}" method="POST" id="product-form" data-ajax-form>
                    @csrf
                    @method('PATCH')
                    <div class="statbox widget box box-shadow mb-4">
                        <div class="widget-header">
                            <h4>Temel Bilgiler</h4>
                        </div>
                        <div class="widget-content widget-content-area">
                            
                            <ul class="nav nav-tabs mb-3 mt-3" id="iconTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="icon-tr-tab" data-toggle="tab" href="#icon-tr"
                                        role="tab" aria-controls="icon-tr" aria-selected="true">TR</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="icon-en-tab" data-toggle="tab" href="#icon-en" role="tab"
                                        aria-controls="icon-en" aria-selected="false">EN</a>
                                </li>
                            </ul>
                            <div class="tab-content" id="iconTabContent-1">
                                <div class="tab-pane fade show active" id="icon-tr" role="tabpanel"
                                    aria-labelledby="icon-tr-tab">
                                    <div class="form-group">
                                        <x-backend.input id="name" label="Ürün Adı" type="text" :value="$product->name"
                                            :required="true" autofocus />
                                    </div>
                                    <div class="form-group">
                                        <x-backend.textarea name="description_tr" label="Açıklama" :value="$product->description_tr"
                                            data-editor="tinymce-6.7.0" />
                                    </div>
                                    <div class="form-group">
                                        <x-backend.textarea name="short_description_tr" label="Kısa Açıklama" rows="2"
                                            :value="$product->short_description_tr" />
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="icon-en" role="tabpanel" aria-labelledby="icon-en-tab">
                                    <div class="form-group">
                                        <x-backend.input id="name_en" label="Ürün Adı" type="text" :value="$product->name_en"
                                            :required="true" autofocus />
                                    </div>
                                    <div class="form-group">
                                        <x-backend.textarea name="description_en" label="Açıklama" :value="$product->description_en"
                                            data-editor="tinymce-6.7.0" />
                                    </div>
                                    <div class="form-group">
                                        <x-backend.textarea name="short_description_en" label="Kısa Açıklama"
                                            rows="2" :value="$product->short_description_en" />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="category_id">Kategori <span class="text-danger">*</span></label>
                                        <select class="form-control basic" id="category_id" name="category_id">
                                            <option value="">Kategori Seçin</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}</option>
                                                @foreach ($category->children as $child)
                                                    <option value="{{ $child->id }}"
                                                        {{ $product->category_id == $child->id ? 'selected' : '' }}>--
                                                        {{ $child->name }}</option>
                                                    @foreach ($child->children as $subchild)
                                                        <option value="{{ $subchild->id }}"
                                                            {{ $product->category_id == $subchild->id ? 'selected' : '' }}>
                                                            ---- {{ $subchild->name }}</option>
                                                    @endforeach
                                                @endforeach
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="brand_id">Marka <span class="text-danger">*</span></label>
                                        <select class="form-control basic" id="brand_id" name="brand_id">
                                            <option value="">Marka Seçin</option>
                                            @foreach ($brands as $brand)
                                                <option value="{{ $brand->id }}"
                                                    {{ $product->brand_id == $brand->id ? 'selected' : '' }}>
                                                    {{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-4">
                                    <x-backend.input id="code" label="Ürün Kodu" type="text" :value="$product->code"
                                        :required="true" />
                                </div>
                                <div class="col-sm-12 col-md-4">
                                    <x-backend.input id="code_group" label="Grup Kodu" type="text" :value="$product->code_group"
                                        :required="true" />
                                </div>
                                <div class="col-sm-12 col-md-4">
                                    <x-backend.input id="barcode" label="Barkod" type="text" :value="$product->barcode" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="statbox widget box box-shadow mb-4">
                        <div class="widget-header">
                            <h4>Fiyatlandırma ve Para Birimi</h4>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="row">
                                <div class="col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <x-label for="price_1" text="1. Fiyat" />
                                        <span class="text-danger">*</span>
                                        <div class="input-group">
                                            <x-backend.input id="price_1" type="text" :value="number_format($product->price_1, 2)"
                                                :required="true" class="form-control" data-format="price" />
                                            <div class="input-group-append">
                                                <select class="form-control" name="price_1_currency"
                                                    id="price_1_currency">
                                                    <option value="TL"
                                                        {{ $product->price_1_currency == 'TL' ? 'selected' : '' }}>TL
                                                    </option>
                                                    <option value="USD"
                                                        {{ $product->price_1_currency == 'USD' ? 'selected' : '' }}>USD
                                                    </option>
                                                    <option value="EUR"
                                                        {{ $product->price_1_currency == 'EUR' ? 'selected' : '' }}>EUR
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <x-label for="price_2" text="2. Fiyat" />
                                        <div class="input-group">
                                            <x-backend.input id="price_2" type="text" :value="number_format($product->price_2, 2)"
                                                class="form-control" data-format="price" />
                                            <div class="input-group-append">
                                                <select class="form-control" name="price_2_currency"
                                                    id="price_2_currency">
                                                    <option value="TL"
                                                        {{ $product->price_2_currency == 'TL' ? 'selected' : '' }}>TL
                                                    </option>
                                                    <option value="USD"
                                                        {{ $product->price_2_currency == 'USD' ? 'selected' : '' }}>USD
                                                    </option>
                                                    <option value="EUR"
                                                        {{ $product->price_2_currency == 'EUR' ? 'selected' : '' }}>EUR
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <x-label for="price_3" text="3. Fiyat" />
                                        <div class="input-group">
                                            <x-backend.input id="price_3" type="text" :value="number_format($product->price_3, 2)"
                                                class="form-control" data-format="price" />
                                            <div class="input-group-append">
                                                <select class="form-control" name="price_3_currency"
                                                    id="price_3_currency">
                                                    <option value="TL"
                                                        {{ $product->price_3_currency == 'TL' ? 'selected' : '' }}>TL
                                                    </option>
                                                    <option value="USD"
                                                        {{ $product->price_3_currency == 'USD' ? 'selected' : '' }}>USD
                                                    </option>
                                                    <option value="EUR"
                                                        {{ $product->price_3_currency == 'EUR' ? 'selected' : '' }}>EUR
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <x-label for="price_4" text="4. Fiyat" />
                                        <div class="input-group">
                                            <x-backend.input id="price_4" type="text" :value="number_format($product->price_4, 2)"
                                                class="form-control" data-format="price" />
                                            <div class="input-group-append">
                                                <select class="form-control" name="price_4_currency"
                                                    id="price_4_currency">
                                                    <option value="TL"
                                                        {{ $product->price_4_currency == 'TL' ? 'selected' : '' }}>TL
                                                    </option>
                                                    <option value="USD"
                                                        {{ $product->price_4_currency == 'USD' ? 'selected' : '' }}>USD
                                                    </option>
                                                    <option value="EUR"
                                                        {{ $product->price_4_currency == 'EUR' ? 'selected' : '' }}>EUR
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <x-label for="price_5" text="5. Fiyat" />
                                        <div class="input-group">
                                            <x-backend.input id="price_5" type="text" :value="number_format($product->price_5, 2)"
                                                class="form-control" data-format="price" />
                                            <div class="input-group-append">
                                                <select class="form-control" name="price_5_currency"
                                                    id="price_5_currency">
                                                    <option value="TL"
                                                        {{ $product->price_5_currency == 'TL' ? 'selected' : '' }}>TL
                                                    </option>
                                                    <option value="USD"
                                                        {{ $product->price_5_currency == 'USD' ? 'selected' : '' }}>USD
                                                    </option>
                                                    <option value="EUR"
                                                        {{ $product->price_5_currency == 'EUR' ? 'selected' : '' }}>EUR
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-3">
                                    <x-backend.input id="vat_rate" label="KDV Oranı (%)" type="number"
                                        :value="$product->vat_rate" />
                                </div>
                                <div class="col-sm-12 col-md-3">
                                    <label class="font-12 mb-3">KDV Dahil Mi?</label>
                                    <div class="text-left">
                                        <span class="switch">
                                            <label>
                                                <input type="checkbox" name="is_vat_included"
                                                    {{ $product->is_vat_included ? 'checked' : '' }}>
                                                <span></span>
                                            </label>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="statbox widget box box-shadow mb-4">
                        <div class="widget-header">
                            <h4>Özellikler</h4>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Özellik Grubu Seç</label>
                                    <select class="form-control attribute-group-select" name="attribute_group_id"
                                        id="attribute_group_id"
                                        data-url="{{ route('admin.catalog.products.ajax.attributes') }}">
                                        <option value="">Seç</option>
                                        @php
                                            $selectedGroup = $product->attributeValues->first()?->attribute
                                                ?->attributeGroup?->id;
                                        @endphp
                                        @foreach ($attributeGroups as $group)
                                            <option value="{{ $group->id }}"
                                                {{ $selectedGroup == $group->id ? 'selected' : '' }}>{{ $group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="attribute-list" id="attribute-list-container">
                                        @php
                                            $attributeGroup = $product->attributeValues->first()?->attribute
                                                ?->attributeGroup;
                                            $selectedAttributeValueIds = $product->attributeValues
                                                ->pluck('id')
                                                ->map(fn ($id) => (int) $id)
                                                ->all();
                                            $attributes = $attributeGroup
                                                ? $attributeGroup->attributes()->with('attributeValues')->orderBy('name')->get()
                                                : collect();
                                        @endphp
                                        @include('admin.catalog.products.partials.attributes', [
                                            'attributes' => $attributes,
                                            'productId' => (int) $product->id,
                                            'selectedAttributeValueIds' => $selectedAttributeValueIds,
                                        ])
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="statbox widget box box-shadow mb-4">
                        <div class="widget-header">
                            <h4>Ürün Görselleri</h4>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="row">
                                @for ($i = 1; $i <= 3; $i++)
                                    <div class="col-md-3">
                                        <label class="font-12">{{ $i }}. Resim</label>
                                        <div class="form-group">
                                            <div class="image-upload">
                                                <div class="image-edit">
                                                    <input type="file" id="product_image_{{ $i }}_upload"
                                                        data-preview="#product_image_{{ $i }}_preview"
                                                        name="image_{{ $i }}" accept=".png, .jpg, .jpeg">
                                                    <label for="product_image_{{ $i }}_upload"><i
                                                            class="las la-pen"></i></label>
                                                </div>
                                                @if (
                                                    ($i == 1 && $product->image_1 && $product->image_1 != 'urun-gorseli-hazirlaniyor.jpg') ||
                                                        ($i == 2 && $product->image_2 && $product->image_2 != null) ||
                                                        ($i == 3 && $product->image_3 && $product->image_3 != null))
                                                    <div class="image-delete">
                                                        <a href="javascript:;" class="btn btn-danger p-1"
                                                            style="display: inline-block;width: 34px;height: 34px;margin-bottom: 0;border-radius: 100%;border: 1px solid transparent;box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.12);cursor: pointer;font-weight: normal;transition: all .2s ease-in-out;"
                                                            data-selector="image-delete"
                                                            data-url="{{ route('admin.catalog.products.delete-image', ['product' => $product->id, 'imageField' => 'image_' . $i]) }}"
                                                            data-product-id="{{ $product->id }}"
                                                            data-image-index="{{ $i }}" title="Resmi Sil">
                                                            <i class="las la-trash"></i>
                                                        </a>
                                                    </div>
                                                @endif
                                                <div class="image-preview">
                                                    <div id="product_image_{{ $i }}_preview"
                                                        style="background-image: url('{{ $product->{'image_small_url_' . $i} }}'); background-size: contain; background-repeat: no-repeat; background-position: center;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>

                    <div class="statbox widget box box-shadow mb-4">
                        <div class="widget-header">
                            <h4>Ürün Dosyaları</h4>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div x-data="productFiles({{ $product->files->toJson() }})" class="space-y-4">

                                <template x-for="(file, index) in files" :key="index">
                                    <div class="border p-3 rounded-lg mb-3">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <input type="hidden" :name="`files[${index}][id]`" x-model="file.id">
                                                <input type="text" class="form-control mb-2"
                                                       placeholder="Dosya Adı"
                                                       x-model="file.name"
                                                       :name="`files[${index}][name]`">
                                            </div>

                                            <div class="col-md-3">
                                                <select class="form-control mb-2"
                                                        x-model="file.type"
                                                        :name="`files[${index}][type]`">
                                                    <option value="file">Dosya</option>
                                                    <option value="link">Link</option>
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <template x-if="file.type === 'file'">
                                                    <div>
                                                        <input type="file"
                                                               class="form-control mb-2"
                                                               :name="`files[${index}][value]`"
                                                               accept="*/*">
                                                        <input type="hidden"
                                                            :name="`files[${index}][old_value]`"
                                                            x-model="file.value">
                                                        <template x-if="file.value">
                                                            <a :href="`/product-files/${file.value}`" target="_blank" class="text-sm text-info">
                                                                Mevcut dosyayı görüntüle
                                                            </a>
                                                        </template>
                                                    </div>
                                                </template>

                                                <template x-if="file.type === 'link'">
                                                    <input type="text"
                                                           class="form-control mb-2"
                                                           placeholder="Link adresi"
                                                           x-model="file.value"
                                                           :name="`files[${index}][value]`">
                                                </template>
                                            </div>

                                            <div class="col-md-1 text-end">
                                                <button type="button" class="btn btn-danger btn-sm mt-1"
                                                        @click="removeFile(index)">
                                                    Sil
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <button type="button" class="btn btn-primary" @click="addFile()">+ Yeni Dosya Ekle</button>
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
                        <div class="form-group row">
                            <div class="col-3">
                                <span class="switch align-items-start">
                                    <label>
                                        <input type="checkbox" name="status" form="product-form" {{ $product->status ? 'checked' : '' }}>
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" id="status-label-text"> {{ $product->status ? 'Aktif' : 'Pasif' }}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        function productFiles(existingFiles = []) {
            return {
                files: existingFiles.length ? existingFiles : [],

                addFile() {
                    this.files.push({ id: null, name: '', type: 'file', value: '' });
                },

                removeFile(index) {
                    this.files.splice(index, 1);
                },
            }
        }

        $(document).ready(function() {
            $('.basic').select2();
            $('.attribute-group-select').select2({
                placeholder: "Seç"
            });
            $('.attribute-value-select').select2({
                placeholder: "Seç",
                multiple: true,
                closeOnSelect: false
            });

            $('.attribute-group-select').change(function() {
                var groupId = $(this).val();
                var productId = '{{ $product->id }}';
                var $attributeList = $('#attribute-list-container');
                var endpoint = $(this).data('url');

                if (groupId && endpoint) {
                    $.post(endpoint, {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        attribute_group_id: groupId,
                        product_id: productId
                    }, function(response) {
                        $attributeList.html(response);

                        setTimeout(function() {
                            $('.attribute-value-select').select2({
                                placeholder: "Seç",
                                multiple: true,
                                closeOnSelect: false
                            });
                        }, 50);
                    });
                } else {
                    $attributeList.html('');
                }
            });

            // Resim önizleme için
            $('input[type="file"][data-preview]').change(function() {
                var input = this;
                var previewSelector = $(this).data('preview');

                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $(previewSelector).css('background-image', 'url(' + e.target.result + ')');
                    }

                    reader.readAsDataURL(input.files[0]);

                    var imageUploadDiv = $(input).closest('.image-upload');
                    if (imageUploadDiv.find('.image-delete').length === 0) {
                        var name = $(input).attr('name');
                        var imageIndex = name.replace('image_', '');
                        var productId = '{{ $product->id ?? '' }}';
                        var deleteUrl = '';
                        var deleteHtml =
                            '<div class="image-delete">' +
                            '<a href="javascript:;" class="btn btn-danger p-1" style="display: inline-block;width: 34px;height: 34px;margin-bottom: 0;border-radius: 100%;border: 1px solid transparent;box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.12);cursor: pointer;font-weight: normal;transition: all .2s ease-in-out;" data-selector="image-delete" data-url="' +
                            deleteUrl + '" data-product-id="' + productId +
                            '" data-image-index="' + imageIndex +
                            '" title="Resmi Sil"><i class="las la-trash"></i></a>' +
                            '</div>';
                        imageUploadDiv.find('.image-edit').after(deleteHtml);
                    }
                }
            });

            document.addEventListener('click', async (e) => {
                const btn = e.target.closest('[data-selector="image-delete"]');
                if (!btn) return;

                const url = btn.dataset.url;
                const imageIndex = btn.dataset.imageIndex;
                const isSaved = url && url.length > 0;

                const confirmed = await customConfirm("Bu resmi silmek istediğinize emin misiniz?");
                if (!confirmed) {
                    return;
                }

                setLoading(btn, true);

                const defaultImage =
                    '{{ asset('assets/images/products/small/urun-gorseli-hazirlaniyor.jpg') }}';

                if (isSaved) {
                    axiosRequest.delete(url, {}, {
                        onSuccess: (data) => {
                            notify('success', data.message);

                            const preview = document.getElementById('product_image_' +
                                imageIndex + '_preview');
                            if (preview) {
                                if (imageIndex == 1) {
                                    preview.style.backgroundImage = 'url(' + defaultImage +
                                        '?v=' + Date.now() + ')';
                                } else {
                                    preview.style.backgroundImage = 'url()';
                                }
                            }

                            const input = document.getElementById('product_image_' +
                                imageIndex + '_upload');
                            if (input) input.value = '';

                            btn.closest('.image-delete').remove();
                        },
                        onComplete: () => setLoading(btn, false)
                    });
                } else {
                    const preview = document.getElementById('product_image_' + imageIndex + '_preview');
                    if (preview) {
                        if (imageIndex == 1) {
                            preview.style.backgroundImage = 'url(' + defaultImage + '?v=' + Date.now() +
                                ')';
                        } else {
                            preview.style.backgroundImage = 'url()';
                        }
                    }
                    const input = document.getElementById('product_image_' + imageIndex + '_upload');
                    if (input) input.value = '';

                    btn.closest('.image-delete').remove();
                    setLoading(btn, false);
                }
            });
        });
    </script>
@endsection
