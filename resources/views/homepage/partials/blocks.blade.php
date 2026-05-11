<section class="section-small-space">
    <div class="container-fluid-lg">
        @foreach ($blocks as $block)
            @php
                $products = $block->getRandomProducts(20);
            @endphp
            <div class="title d-flex justify-content-between align-items-center mt-5">
                <div>
                    <a href="{{ $block->slug ? route('product.block', [$block->slug]) : 'javascript:;' }}" class="text-dark">
                        <h2 class="mb-0">{{ $block->{'title_' . app()->getLocale()} }}</h2>
                    </a>
                    <p class="mt-2">{{ $block->{'subtitle_' . app()->getLocale()} }}</p>
                </div>
                @if($block->slug)
                    <a href="{{ route('product.block', [$block->slug]) }}" class="badge rounded-pill bg-secondary p-2 px-3 text-white text-decoration-none">
                        Tümünü Gör <i class="fa-solid fa-arrow-right-long ms-2"></i>
                    </a>
                @endif
            </div>

            <div class="product-box-slider g-2">
                @foreach($products as $product)
                    <div>
                        <div class="product-box-3 h-100">
                            <div class="product-header">
                                <div class="product-image">
                                    <a href="{{ route('product.detail', [$product->slug]) }}">
                                        <img src="{{ image_url(config('images.default.lazy_load'), 'product.small') }}" data-src="{{ $product->image_small_url_1 }}" class="img-fluid blur-up lazyload" alt="">
                                    </a>
                                </div>
                            </div>
                            <div class="product-footer">
                                <div class="product-detail">
                                    <a href="{{ route('product.detail', [$product->slug]) }}">
                                        <h5 class="name">{{ $product->product_name }}</h5>
                                    </a>
                                    <h6 class="unit">{{ $product->code }}</h6>
                                    <h6 class="stock">{!! $product->stock_show !!}</h6>
                                    <h5 class="price">
                                        <x-price-box :product="$product" />
                                    </h5>
                                    <div class="add-to-cart-box bg-white">
                                        <div class="cart_qty qty-box">
                                            <div class="input-group" data-selector="quantity-container" data-box-quantity="{{ $product->box_quantity ?? 1 }}" data-box-exact="{{ $product->box_quantity_must_be_exact ? 'true' : 'false' }}">
                                                <button type="button" class="qty-left-minus" data-selector="qty-minus">
                                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                                </button>
                                                <input class="form-control input-number qty-input" type="text" name="quantity" value="{{ $product->box_quantity_must_be_exact ? $product->box_quantity : 1 }}" data-selector="qty-value">
                                                <button type="button" class="qty-right-plus" data-selector="qty-plus">
                                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <a href="javascript:;" class="btn btn-md cart-button text-white w-100 bg-dark" {{ $product->stock >= 1 || (additional_setting('allow_over_order') == 1 && $product->stock <= 0) ? 'data-js=add-to-cart data-product-id=' . $product->id . ' data-view-type=grid' : '' }}>{{ $product->stock >= 1 || (additional_setting('allow_over_order') == 1 && $product->stock <= 0) ? trans('translations.product.sepete_ekle') : trans('translations.product.tukendi') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</section>