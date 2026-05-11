<div class="{{ count($products) ? 'row g-sm-4 g-3 row-cols-xxl-6 row-cols-xl-5 row-cols-lg-4 row-cols-md-3 row-cols-2 product-list-section' : 'text-center' }}">
    @forelse ($products as $product)
        <div data-code="{{ $product->encoded_code }}">
            <div class="product-box-3 h-100">
                <div class="product-header">
                    <div class="product-header-top">
                        @if ($product->activeCampaigns()->count())
                            <div class="label-campaign">
                                <i class="fa-solid fa-gift"></i> Kampanya
                            </div>
                        @endif
                    </div>
                    <div class="product-image">
                        <a href="{{ $product->detail_url }}">
                            <img src="{{ $product->image_lazy_load }}" data-src="{{ $product->image_small_url_1 }}" class="img-fluid blur-up lazyload" alt="">
                        </a>
                    </div>
                </div>
                <div class="product-footer">
                    <div class="product-detail">
                        <a href="{{ $product->detail_url }}">
                            <h5 class="name">{{ $product->product_name }}</h5>
                        </a>
                        <h6 class="unit">{{ $product->code }}</h6>
                        <h6 class="stock">{!! $product->stock_show !!}</h6>
                        <div class="product-footer">
                            <x-price-box :product="$product" />
                        </div>
                        <div class="add-to-cart-box bg-white">
                            <div class="cart_qty qty-box">
                                <div class="input-group" data-selector="quantity-container" data-box-quantity="{{ $product->box_quantity_value }}" data-box-exact="{{ $product->box_quantity_exact }}">
                                    <button type="button" class="qty-left-minus" data-selector="qty-minus">
                                        <i class="fa fa-minus" aria-hidden="true"></i>
                                    </button>
                                    <input class="form-control input-number qty-input" type="text" name="quantity" value="{{ $product->quantity_value }}" data-selector="qty-value">
                                    <button type="button" class="qty-right-plus" data-selector="qty-plus">
                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                            <a href="javascript:;" class="btn btn-md cart-button text-white w-100 bg-dark" {{ $product->can_add_to_cart ? 'data-js=add-to-cart data-product-id=' . $product->id . ' data-view-type=grid' : '' }}>{{ $product->can_add_to_cart ? trans('translations.product.sepete_ekle') : trans('translations.product.tukendi') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <h4>{{ trans('translations.product.urun_yok') }}.</h4>
    @endforelse
</div>
{{ $products->appends(request()->except('page'))->links('pagination::bootstrap-5') }}