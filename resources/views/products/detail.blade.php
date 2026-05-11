@extends('layouts.app')

@section('title', $product->full_name)

@section('content')
    <section class="product-section section-b-space">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-xxl-9 col-lg-12">
                    <div class="row g-4">
                        <div class="col-md-6 col-xl-5 mt-0">
                            <div class="product-left-box">
                                <div class="row g-sm-4 g-2">
                                    <div class="col-12">
                                        <div class="product-main no-arrow">
                                            <div>
                                                <div class="slider-image">
                                                    <a href="javascript:;" data-src="{{ $product->image_large_url_1 }}" data-fancybox="gallery" data-caption="{{ $product->full_name }}">
                                                        <img src="{{ $product->image_large_url_1 }}" id="img-1" class="img-fluid image_zoom_cls-0 blur-up lazyload" alt="{{ $product->full_name }}">
                                                    </a>
                                                </div>
                                            </div>
                                            @if (!empty($product->image_2))
                                                <div>
                                                    <div class="slider-image">
                                                        <a href="javascript:;" data-src="{{ $product->image_large_url_2 }}" data-fancybox="gallery" data-caption="{{ $product->full_name }}">
                                                            <img src="{{ $product->image_large_url_2 }}" id="img-2" class="img-fluid image_zoom_cls-0 blur-up lazyload" alt="{{ $product->full_name }}">
                                                        </a>
                                                    </div>
                                                </div>
                                            @endif
                                            @if (!empty($product->image_3))
                                                <div>
                                                    <div class="slider-image">
                                                        <a href="javascript:;" data-src="{{ $product->image_large_url_3 }}" data-fancybox="gallery" data-caption="{{ $product->full_name }}">
                                                            <img src="{{ $product->image_large_url_3 }}" id="img-3" class="img-fluid image_zoom_cls-0 blur-up lazyload" alt="{{ $product->full_name }}">
                                                        </a>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="left-slider-image left-slider no-arrow slick-top">
                                            <div>
                                                <div class="slider-image">
                                                    <a href="javascript:;">
                                                        <img src="{{ $product->image_small_url_1 }}" id="img-1" class="img-fluid image_zoom_cls-0 blur-up lazyload" alt="{{ $product->full_name }}">
                                                    </a>
                                                </div>
                                            </div>
                                            @if (!empty($product->image_2))
                                                <div>
                                                    <div class="slider-image">
                                                        <a href="javascript:;">
                                                            <img src="{{ $product->image_small_url_2 }}" id="img-2" class="img-fluid image_zoom_cls-0 blur-up lazyload" alt="{{ $product->full_name }}">
                                                        </a>
                                                    </div>
                                                </div>
                                            @endif
                                            @if (!empty($product->image_3))
                                                <div>
                                                    <div class="slider-image">
                                                        <a href="javascript:;">
                                                            <img src="{{ $product->image_small_url_3 }}" id="img-3" class="img-fluid image_zoom_cls-0 blur-up lazyload" alt="{{ $product->full_name }}">
                                                        </a>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-7">
                            <div class="right-box-contain p-sticky">
                                <h2 class="name">{{ $product->product_name }}</h2>
                                <div class="pickup-box">
                                    <div>
                                        <div class="pickup-detail">
                                            <h4 class="text-content">Ürün Kodu: {{ $product->code }}</h4>
                                            <h4 class="text-content">Kategori: <a href="{{ $product->category_slug }}">{{ $product->category_name }}</a></h4>
                                            @if ($product->brand)
                                                <h4 class="text-content">Marka: <a href="{{ $product->brand_slug }}">{{ $product->brand_name }}</a></h4>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if($campaigns->count())
                                    <div class="campaign-summary-box" style="background: linear-gradient(135deg, #fff3e0, #ffe0b2); border: 1px solid #ffb74d; border-radius: 8px; padding: 12px 16px; margin: 12px 0;">
                                        <div style="display: flex; align-items: center; margin-bottom: 6px;">
                                            <i class="fa-solid fa-gift" style="color: #e65100; margin-right: 8px; font-size: 16px;"></i>
                                            <strong style="color: #e65100; font-size: 14px;">Kampanya Avantajı</strong>
                                        </div>
                                        @foreach($campaigns as $campaign)
                                            @php
                                                $summary = $campaign->getDescriptionSummary();
                                            @endphp
                                            @if($summary)
                                                <div style="display: flex; align-items: baseline; padding: 4px 0; {{ !$loop->last ? 'border-bottom: 1px dashed #ffcc80;' : '' }}">
                                                    <span style="color: #e65100; margin-right: 6px; font-weight: bold;">•</span>
                                                    <span style="color: #bf360c; font-size: 13px; font-weight: 500;">{{ $summary }}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                        <div style="margin-top: 8px;">
                                            <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#productCampaignModal" style="color: #e65100; font-size: 12px; text-decoration: underline;">
                                                Detayları Gör →
                                            </a>
                                        </div>
                                    </div>
                                @endif

                                <x-price-box :product="$product" viewType="detail" />

                                <div class="pickup-box">
                                    <div class="product-info">
                                        <ul class="product-info-list product-info-list-4">
                                            @canSeeStock
                                                <li>
                                                    <i class="fa-solid fa-cubes"></i>
                                                    <span>Stok</span>
                                                    <b>{{ $product->stock_value }}</b>
                                                </li>
                                            @endcanSeeStock
                                            <li><i class="fa-solid fa-percent"></i><span>KDV</span><b>%{{ $product->vat_rate }}</b></li>
                                            <li><i class="fa-solid fa-box"></i><span>Birim</span><b>{{ $product->unit_name_1 }}</b></li>
                                            <li><i class="fa-solid fa-bag-shopping"></i><span>Minimum Sipariş</span><b>{{ $product->box_quantity }}</b></li>
                                            @if ($product->unit_name_2)
                                                <li><i class="fa-solid fa-boxes-stacked"></i><span>{{ $product->unit_name_2_title }} Miktarı</span><b>{{ $product->unit_quantity_2 }}</b></li>
                                            @endif
                                            @if ($product->unit_name_3)
                                                <li><i class="fa-solid fa-boxes-stacked"></i><span>{{ $product->unit_name_3_title }} Miktarı</span><b>{{ $product->unit_quantity_3 }}</b></li>
                                            @endif
                                            @if ($product->unit_name_4)
                                                <li><i class="fa-solid fa-boxes-stacked"></i><span>{{ $product->unit_name_4_title }} Miktarı</span><b>{{ $product->unit_quantity_4 }}</b></li>
                                            @endif
                                            @if ($product->barcode)
                                                <li><i class="fa-solid fa-barcode"></i><span>{{ trans('translations.product.barkod') }}</span><b>{{ $product->barcode }}</b></li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                                @if ($product->can_add_to_cart)
                                    <div class="note-box product-packege">
                                        <div class="cart_qty qty-box">
                                            <div class="input-group" data-selector="quantity-container" data-box-quantity="{{ $product->box_quantity_value }}" data-box-exact="{{ $product->box_quantity_exact }}">
                                                <button type="button" class="qty-left-minus bg-white" data-selector="qty-minus">
                                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                                </button>
                                                <input class="form-control input-number qty-input" type="text" name="quantity" value="{{ $product->quantity_value }}" data-selector="qty-value">
                                                <button type="button" class="qty-right-plus bg-white" data-selector="qty-plus">
                                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <a href="javascript:;" class="btn btn-md bg-dark cart-button text-white w-100" data-js="add-to-cart" data-product-id="{{ $product->id }}" data-view-type="detail">{{ trans('translations.product.sepete_ekle') }}</a>
                                    </div>
                                @else
                                    <div class="note-box product-packege">
                                        <a class="btn btn-md bg-dark cart-button text-white w-100">{{ trans('translations.product.stok_tukendi') }}</a>
                                    </div>
                                @endif
                                <div class="buy-box">
                                    <a href="javascript:;">
                                        {{ trans('translations.product.son_guncelleme_tarihi') }}
                                        {{ format_date_time($product->erp_updated_at) }}
                                    </a>
                                </div>
                                @php
                                    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
                                    $isProductPage = preg_match('/\/urun\/[a-zA-Z0-9\-]+/', $referer);

                                    if ($isProductPage) {
                                        session(['last_visited_product' => htmlspecialchars($referer) . '#' . $product->code]);
                                    }

                                    $lastVisited = session('last_visited_product', '');
                                @endphp
                                @if ($isProductPage)
                                    <div class="buy-box">
                                        <a href="{{ $lastVisited }}" class="btn btn-sm bg-secondary text-white py-1">
                                            <span><i class="fa-solid fa-arrow-left"></i> {{ trans('translations.product.alisverise_devam_et') }}</span>
                                        </a>
                                    </div>
                                @endif
                                @if (count($product->attributeValues))
                                    <div class="pickup-box">
                                        <div class="product-info">
                                            <ul class="product-info-list">
                                                @foreach ($product->attributeValues->groupBy('attribute_id') as $attributeId => $values)
                                                    <li>{{ app()->getLocale() == 'tr' ? $values->first()->attribute->name : $values->first()->attribute->name_en }}:
                                                        <span>{{ app()->getLocale() == 'tr' ? $values->pluck('name')->implode(', ') : $values->pluck('name_en')->implode(', ') }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                                @if (false && auth('web')->check() && auth('web')->user()->role === 'salesman')
                                    <div class="buy-box">
                                        <a href="{{ $product->update_url }}" class="btn btn-sm bg-danger text-white" onclick="showLoader()">
                                            <span><i class="fa-solid fa-sync"></i> {{ trans('translations.product.urun_guncelle') }}</span>
                                        </a>
                                        <a href="javascript:;">{{ trans('translations.product.son_guncelleme_tarihi') }}: {{ $product->updated_at_formatted }}</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @if (count($product->attributeValues) || count($product->files))
                            <div class="col-12">
                                <div class="product-section-box">
                                    <ul class="nav nav-tabs custom-nav" id="myTab" role="tablist">
                                        @if (count($product->attributeValues))
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link {{ count($product->attributeValues) ? 'active' : '' }}" id="product-features-tab" data-bs-toggle="tab" data-bs-target="#product-features" type="button" role="tab" aria-controls="product-features" aria-selected="{{ count($product->attributeValues) ? 'true' : 'false' }}">
                                                    {{ trans('translations.product.urun_ozellikleri') }}
                                                </button>
                                            </li>
                                        @endif
                                        @if (count($product->files))
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link {{ !count($product->attributeValues) && count($product->files) ? 'active' : '' }}" id="product-files-tab" data-bs-toggle="tab" data-bs-target="#product-files" type="button" role="tab" aria-controls="product-files" aria-selected="{{ !count($product->attributeValues) && count($product->files) ? 'true' : 'false' }}">
                                                    {{ trans('translations.product.urunun_belgeleri') }}
                                                </button>
                                            </li>
                                        @endif
                                    </ul>
                                    <div class="tab-content custom-tab" id="myTabContent">
                                        @if (count($product->attributeValues))
                                            <div class="tab-pane fade {{ count($product->attributeValues) ? 'show active' : '' }}" id="product-features" role="tabpanel" aria-labelledby="product-features-tab">
                                                <div class="product-description table-responsive">
                                                    <table class="table info-table">
                                                        <tbody>
                                                            @foreach ($product->attributeValues->groupBy('attribute_id') as $attributeId => $values)
                                                                <tr>
                                                                    <td>{{ app()->getLocale() == 'tr' ? $values->first()->attribute->name : $values->first()->attribute->name_en }}</td>
                                                                    <td>{{ app()->getLocale() == 'tr' ? $values->pluck('name')->implode(', ') : $values->pluck('name_en')->implode(', ') }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endif
                                        @if (count($product->files))
                                            <div class="tab-pane fade {{ !count($product->attributeValues) && count($product->files) ? 'show active' : '' }}" id="product-files" role="tabpanel" aria-labelledby="product-files-tab">
                                                <div class="list-group file-list mt-3">
                                                    @foreach($product->files as $file)
                                                        <a href="{{ $file->file_url }}" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center flex-column flex-md-row">
                                                            <div class="file-info d-flex align-items-center mb-2 mb-md-0">
                                                                <span class="file-icon me-3">
                                                                    <i class="fa-solid {{ str_ends_with($file->value, '.pdf') ? 'fa-file-pdf' : 'fa-file' }}"></i>
                                                                </span>
                                                                <span class="file-name text-truncate" style="max-width: 250px;">{{ $file->name }}</span>
                                                            </div>
                                                            <div class="file-actions d-flex align-items-center">
                                                                <span class="file-download me-2">
                                                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                                                </span>
                                                            </div>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-xxl-3 d-none d-xxl-block">
                    @if (($product->brand && $product->brand_image_url) || count($relatedProducts))
                        <div class="right-sidebar-box">
                            @if ($product->brand && $product->brand_image_url)
                                <div class="vendor-box">
                                    <div class="verndor-contain">
                                        @if ($product->brand_image_url)
                                            <div class="vendor-image">
                                                <a href="{{ $product->brand_slug }}">
                                                    <img src="{{ $product->brand_image_url }}" class="blur-up lazyload" alt="{{ $product->brand_name }}">
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if (count($relatedProducts))
                                <div class="pt-25">
                                    <div class="category-menu">
                                        <h3>{{ trans('translations.product.bu_modelin_renkleri') }}</h3>
                                        <ul class="product-list product-right-sidebar border-0 p-0">
                                            @foreach ($relatedProducts as $relatedProduct)
                                                <li>
                                                    <div class="offer-product">
                                                        <a href="{{ $relatedProduct->detail_url }}" class="offer-image">
                                                            <img data-src="{{ $relatedProduct->image_small_url_1 }}" src="{{ $relatedProduct->image_lazy_load }}" class="img-fluid blur-up lazyload" alt="{{ $relatedProduct->product_name }}">
                                                        </a>
                                                        <div class="offer-detail">
                                                            <div>
                                                                <a href="{{ $relatedProduct->detail_url }}">
                                                                    <h6 class="name">{{ $relatedProduct->product_name }}</h6>
                                                                </a>
                                                                <span>{{ $relatedProduct->code }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
    @if (count($relatedProducts))
        <section class="product-list-section section-b-space">
            <div class="container-fluid-lg">
                <div class="title">
                    <h2>{{ trans('translations.product.bu_modelin_renkleri') }}</h2>
                    <span class="title-leaf"></span>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="slider-5_3 product-wrapper">
                            @foreach ($relatedProducts as $relatedProduct)
                                <div>
                                    <div class="product-box-3">
                                        <div class="product-header">
                                            <div class="product-image">
                                                <a href="{{ $relatedProduct->detail_url }}">
                                                    <img data-src="{{ $relatedProduct->image_small_url_1 }}" src="{{ $relatedProduct->image_lazy_load }}" class="img-fluid blur-up lazyload" alt="{{ $relatedProduct->product_name }}">
                                                </a>
                                            </div>
                                        </div>
                                        <div class="product-footer">
                                            <div class="product-detail">
                                                <a href="{{ $relatedProduct->detail_url }}">
                                                    <h5 class="name">{{ $relatedProduct->product_name }}</h5>
                                                </a>
                                                <h6 class="unit">{{ $relatedProduct->code }}</h6>
                                                <h6 class="stock">{!! $relatedProduct->stock_show !!}</h6>
                                                <h5 class="price">
                                                    <x-price-box :product="$relatedProduct" />
                                                </h5>
                                                <div class="add-to-cart-box bg-white">
                                                    <div class="cart_qty qty-box">
                                                        <div class="input-group" data-selector="quantity-container" data-box-quantity="{{ $relatedProduct->box_quantity_value }}" data-box-exact="{{ $relatedProduct->box_quantity_exact }}">
                                                            <button type="button" class="qty-left-minus" data-selector="qty-minus">
                                                                <i class="fa fa-minus" aria-hidden="true"></i>
                                                            </button>
                                                            <input class="form-control input-number qty-input" type="text" name="quantity" value="{{ $relatedProduct->quantity_value }}" data-selector="qty-value">
                                                            <button type="button" class="qty-right-plus" data-selector="qty-plus">
                                                                <i class="fa fa-plus" aria-hidden="true"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <a href="javascript:;" class="btn btn-md bg-dark cart-button text-white w-100" {{ $relatedProduct->can_add_to_cart ? 'data-js=add-to-cart data-product-id=' . $relatedProduct->id . ' data-view-type=grid' : '' }}>{{ $relatedProduct->can_add_to_cart ? trans('translations.product.sepete_ekle') : trans('translations.product.tukendi') }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
@endsection

@section('js')
    <script src="{{ mix('js/frontend/modules/cart/add-to-cart.js') }}"></script>
@endsection
